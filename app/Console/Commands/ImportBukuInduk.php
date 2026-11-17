<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use App\Support\XlsxReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// php artisan elibra:import-induk "C:\path\BUKU_INDUK_KESELURUHAN.xlsx" --count=50 --ganti-tanpa-cover --cari-judul
class ImportBukuInduk extends Command
{
    protected $signature = 'elibra:import-induk
        {file : Path file BUKU INDUK (.xlsx atau .csv)}
        {--count=50 : Jumlah buku pengganti yang bercover (20-100)}
        {--sheet=1 : Nomor sheet Excel}
        {--seed= : Angka acak tetap agar hasil bisa diulang}
        {--hapus-contoh : Hapus buku contoh lama yang TIDAK punya riwayat peminjaman}
        {--ganti-tanpa-cover : Hapus buku yang BELUM punya cover (dan tanpa riwayat pinjam), lalu gantikan dengan buku lain yang covernya ditemukan}
        {--boleh-tanpa-cover : Jika cover tidak cukup ditemukan, isi kekurangan dengan buku tanpa cover}
        {--cari-judul : Untuk baris tanpa ISBN valid, cari cover lewat judul+pengarang (Open Library, harus cocok persis)}
        {--paksa : Tetap ganti walau jumlah buku bercover yang ditemukan kurang dari --count}
        {--tanpa-cover : Lewati pencarian cover}';

    protected $description = 'Impor 20-30 buku acak dari Excel BUKU INDUK + cover asli (dicocokkan lewat ISBN)';

    private const ALIASES = [
        'title'     => ['judul', 'judulbuku', 'namabuku', 'title'],
        'author'    => ['pengarang', 'penulis', 'author'],
        'publisher' => ['penerbit', 'publisher'],
        'year'      => ['tahun', 'tahunterbit', 'thterbit', 'year'],
        'isbn'      => ['isbn', 'noisbn'],
        'category'  => ['kategori', 'category', 'klasifikasi', 'subjek', 'jenis'],
        'stock'     => ['jumlah', 'eksemplar', 'stok', 'jmleksemplar'],
    ];

    public function handle(): int
    {
        $count = (int) $this->option('count');
        if ($count < 20 || $count > 100) {
            $this->error('--count harus antara 20 dan 100.');
            return self::FAILURE;
        }
        $file = (string) $this->argument('file');
        if (! is_file($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return self::FAILURE;
        }

        try {
            $rows = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xlsx'
                ? XlsxReader::rows($file, max(1, (int) $this->option('sheet')))
                : $this->csvRows($file);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        [$cols, $headerAt] = $this->detectColumns($rows);
        if (! isset($cols['title'])) {
            $this->error('Kolom judul tidak ditemukan. Baris judul kolom yang terbaca:');
            $this->line(implode(' | ', $rows[0] ?? []));
            return self::FAILURE;
        }
        $this->info('Kolom terdeteksi: '.collect($cols)->map(fn ($i, $k) => "{$k}=#{$i}")->implode(', '));

        // Kandidat: judul terisi + ISBN valid (ISBN dipakai untuk mencocokkan cover yang benar)
        $candidates = [];
        $seen = [];
        foreach (array_slice($rows, $headerAt + 1) as $r) {
            $get = fn (string $k) => isset($cols[$k]) ? trim((string) ($r[$cols[$k]] ?? '')) : '';
            $title = $get('title');
            $isbn  = $this->normalizeIsbn($get('isbn'));
            if ($title === '' || ($isbn === null && ! $this->option('cari-judul'))) {
                continue;
            }
            $key = $isbn ?? 'T:'.mb_strtolower($title);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $candidates[] = [
                'title' => mb_substr($title, 0, 255), 'author' => mb_substr($get('author') ?: 'Tidak diketahui', 0, 255),
                'publisher' => mb_substr($get('publisher'), 0, 150) ?: null,
                'year' => preg_match('/(19|20)\d{2}/', $get('year'), $y) ? (int) $y[0] : null,
                'isbn' => $isbn, 'category' => mb_substr($get('category') ?: 'Umum', 0, 100),
                'stock' => ctype_digit($get('stock')) && (int) $get('stock') > 0 ? min(100, (int) $get('stock')) : 1,
            ];
        }
        $this->info(count($candidates).' baris punya judul + ISBN valid.');
        if (count($candidates) < $count) {
            $this->warn('Kandidat kurang dari jumlah yang diminta; jumlah menyesuaikan.');
            $count = count($candidates);
        }

        if ($this->option('seed') !== null && $this->option('seed') !== '') {
            mt_srand((int) $this->option('seed'));
        }
        // Urutan: buku yang paling mungkin punya cover di Open Library/Google Books lebih dulu
        // (fiksi/novel/populer, penerbit umum, terbitan baru), lalu diacak di dalam skor yang sama.
        foreach ($candidates as &$cd) {
            $cd['_s'] = $this->coverScore($cd) + mt_rand(0, 300) / 100;
        }
        unset($cd);
        usort($candidates, fn ($a, $b) => $b['_s'] <=> $a['_s']);

        // Buku yang sudah ada di database tidak perlu dicari covernya lagi
        $existing = Book::pluck('isbn')->filter()->flip()->all();
        $titles = Book::pluck('title')->map(fn ($t) => mb_strtolower(trim($t)))->flip()->all();
        $candidates = array_values(array_filter($candidates, fn ($c) => $c['isbn'] !== null
            ? ! isset($existing[$c['isbn']])
            : ! isset($titles[mb_strtolower(trim($c['title']))])));

        // Hanya buku yang cover aslinya ditemukan (ISBN sama persis) yang dipilih.
        // Buku tanpa cover baru dipakai jika --boleh-tanpa-cover.
        $picked = [];
        $noCover = [];
        $attempts = 0;
        $pickedKeys = [];

        if ($this->option('tanpa-cover')) {
            foreach (array_slice($candidates, 0, $count) as $c) {
                $picked[] = $c + ['cover' => null, 'source' => '-'];
            }
        } else {
            $withIsbn = array_values(array_filter($candidates, fn ($c) => $c['isbn'] !== null));
            $this->info('Mencari cover (20 buku sekaligus, jauh lebih cepat)...');

            // Tahap 1: Open Library lewat ISBN, 20 permintaan paralel per putaran
            foreach (array_chunk($withIsbn, 20) as $chunk) {
                if (count($picked) >= $count || $attempts >= 800) {
                    break;
                }
                $attempts += count($chunk);
                $res = Http::pool(function ($pool) use ($chunk) {
                    foreach ($chunk as $i => $c) {
                        $pool->as((string) $i)->timeout(8)->get("https://covers.openlibrary.org/b/isbn/{$c['isbn']}-L.jpg", ['default' => 'false']);
                    }
                });
                foreach ($chunk as $i => $c) {
                    $r = $res[(string) $i] ?? null;
                    if ($r instanceof \Illuminate\Http\Client\Response && $r->successful() && $this->validImage($r->body()) && count($picked) < $count) {
                        $picked[] = $c + ['cover' => $r->body(), 'source' => 'Open Library'];
                        $pickedKeys[$c['isbn']] = true;
                        $this->line("  cover OK  {$c['isbn']}  {$c['title']} (Open Library)  [".count($picked)."/{$count}]");
                    }
                }
            }

            // Tahap 2: Google Books untuk yang belum ketemu
            if (count($picked) < $count) {
                $rest = array_values(array_filter($withIsbn, fn ($c) => ! isset($pickedKeys[$c['isbn']])));
                foreach (array_chunk(array_slice($rest, 0, 300), 15) as $chunk) {
                    if (count($picked) >= $count) {
                        break;
                    }
                    $res = Http::pool(function ($pool) use ($chunk) {
                        foreach ($chunk as $i => $c) {
                            $pool->as((string) $i)->timeout(8)->get('https://www.googleapis.com/books/v1/volumes', ['q' => 'isbn:'.$c['isbn'], 'maxResults' => 1]);
                        }
                    });
                    foreach ($chunk as $i => $c) {
                        $r = $res[(string) $i] ?? null;
                        if (! ($r instanceof \Illuminate\Http\Client\Response) || count($picked) >= $count) {
                            continue;
                        }
                        $item = $r->json('items.0.volumeInfo');
                        $ids = collect($item['industryIdentifiers'] ?? [])->pluck('identifier')->map(fn ($v) => $this->normalizeIsbn((string) $v))->all();
                        $url = $item['imageLinks']['thumbnail'] ?? null;
                        if ($url && in_array($c['isbn'], $ids, true)) {
                            $img = Http::timeout(8)->get(str_replace(['http://', '&edge=curl'], ['https://', ''], $url).'&zoom=2');
                            if ($img->successful() && $this->validImage($img->body())) {
                                $picked[] = $c + ['cover' => $img->body(), 'source' => 'Google Books'];
                                $pickedKeys[$c['isbn']] = true;
                                $this->line("  cover OK  {$c['isbn']}  {$c['title']} (Google Books)  [".count($picked)."/{$count}]");
                            }
                        }
                    }
                }
            }

            // Tahap 3: pencarian judul+pengarang (lebih lambat, dibatasi 150 buku)
            if (count($picked) < $count && $this->option('cari-judul')) {
                $rest = array_values(array_filter($candidates, fn ($c) => $c['isbn'] === null || ! isset($pickedKeys[$c['isbn']])));
                $this->info('Mencari lewat judul + pengarang (Open Library, lalu Google Books)...');
                foreach (array_slice($rest, 0, 400) as $n => $c) {
                    if (count($picked) >= $count) {
                        break;
                    }
                    [$bin, $src] = $this->findCoverByTitle($c['title'], $c['author']);
                    if ($bin === null) {
                        usleep(250000);
                        [$bin, $src] = $this->findCoverGoogleTitle($c['title'], $c['author']);
                    }
                    if ($n > 0 && $n % 50 === 0) {
                        $this->line("  ... {$n} judul diperiksa, ".count($picked)." cover ditemukan");
                    }
                    if ($bin !== null) {
                        $picked[] = $c + ['cover' => $bin, 'source' => $src];
                        $this->line('  cover OK  '.($c['isbn'] ?? '-')."  {$c['title']} ({$src})  [".count($picked)."/{$count}]");
                    }
                }
            }
        }

        if (count($picked) < $count && $this->option('boleh-tanpa-cover')) {
            foreach ($noCover as $c) {
                if (count($picked) >= $count) {
                    break;
                }
                $picked[] = $c;
            }
        } elseif (count($picked) < $count && ! $this->option('tanpa-cover')) {
            $this->warn(count($picked)." buku dengan cover ditemukan dari {$attempts} percobaan (diminta {$count}). Cek koneksi internet, atau tambahkan --boleh-tanpa-cover.");
        }

        if ($this->option('ganti-tanpa-cover')) {
            if (count($picked) === 0 || (count($picked) < $count && ! $this->option('paksa'))) {
                $this->error('Hanya '.count($picked)." buku pengganti bercover ditemukan (diminta {$count}). Buku lama TIDAK dihapus. Coba tambahkan --cari-judul, atau --paksa untuk tetap mengganti.");
                return self::FAILURE;
            }
            $removed = 0;
            Book::whereDoesntHave('loans')->get()->each(function (Book $b) use (&$removed) {
                // cover_url juga mengenali foto di cover-map.json, jadi buku yang punya foto tidak ikut terhapus
                if (! $b->cover_url) {
                    $b->delete();
                    $removed++;
                }
            });
            Category::doesntHave('books')->delete();
            $this->warn("{$removed} buku tanpa cover (tanpa riwayat peminjaman) diganti.");
        }

        if ($this->option('hapus-contoh')) {
            $removed = 0;
            Book::whereDoesntHave('loans')->get()->each(function (Book $b) use (&$removed) {
                $b->delete();
                $removed++;
            });
            Category::doesntHave('books')->delete();
            $this->warn("{$removed} buku contoh tanpa riwayat peminjaman dihapus.");
        }

        $dir = public_path('uploads/covers');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $rowsOut = [];
        foreach ($picked as $c) {
            if ($c['isbn'] !== null && Book::where('isbn', $c['isbn'])->exists()) {
                $rowsOut[] = [$c['title'], $c['isbn'], 'dilewati (ISBN sudah ada)'];
                continue;
            }
            $path = null;
            if ($c['cover'] !== null) {
                $name = Str::random(32).'.jpg';
                file_put_contents($dir.'/'.$name, $c['cover']);
                $path = 'uploads/covers/'.$name;
            }
            $catName = $c['category'];
            $cat = Category::firstOrCreate(['slug' => Str::slug($catName) ?: 'umum'], ['name' => $catName]);
            $base = Str::slug($c['title']) ?: 'buku';
            $slug = $base;
            for ($i = 2; Book::where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }
            Book::create([
                'category_id' => $cat->id, 'title' => $c['title'], 'slug' => $slug, 'author' => $c['author'],
                'publisher' => $c['publisher'], 'year' => $c['year'], 'isbn' => $c['isbn'],
                'language' => 'Indonesia', 'total_stock' => $c['stock'], 'available_stock' => $c['stock'],
                'cover_gradient' => 'mc'.(($cat->id % 6) + 1), 'cover_path' => $path,
            ]);
            $rowsOut[] = [$c['title'], $c['isbn'] ?? '-', $path ? 'cover dari '.$c['source'] : 'Cover Belum Tersedia'];
        }

        $this->table(['Judul', 'ISBN', 'Hasil'], $rowsOut);
        $this->info(count($rowsOut).' buku diproses. Buku tanpa cover akan tampil "Cover Belum Tersedia"; admin bisa mengunggahnya di Data Buku > Ubah.');

        return self::SUCCESS;
    }

    // Cover hanya diterima bila server sumber mengembalikannya untuk ISBN yang sama persis.
    private function findCover(string $isbn): array
    {
        try {
            $r = Http::timeout(15)->get("https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg", ['default' => 'false']);
            if ($r->successful() && $this->validImage($r->body())) {
                return [$r->body(), 'Open Library'];
            }
        } catch (\Throwable $e) {
        }

        try {
            $r = Http::timeout(15)->get('https://www.googleapis.com/books/v1/volumes', ['q' => 'isbn:'.$isbn, 'maxResults' => 1]);
            $item = $r->json('items.0.volumeInfo');
            $ids = collect($item['industryIdentifiers'] ?? [])->pluck('identifier')->map(fn ($v) => $this->normalizeIsbn((string) $v))->all();
            $url = $item['imageLinks']['thumbnail'] ?? null;
            if ($url && in_array($isbn, $ids, true)) {
                $url = str_replace(['http://', '&edge=curl'], ['https://', ''], $url).'&zoom=2';
                $img = Http::timeout(15)->get($url);
                if ($img->successful() && $this->validImage($img->body())) {
                    return [$img->body(), 'Google Books'];
                }
            }
        } catch (\Throwable $e) {
        }

        return [null, null];
    }

    // Cari cover lewat judul+pengarang. Diterima hanya bila judul sama persis (setelah dirapikan)
    // dan salah satu kata nama pengarang cocok, supaya tidak memasang cover buku lain.
    private function findCoverByTitle(string $title, string $author): array
    {
        $norm = fn (string $t) => trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($t)));
        try {
            $r = Http::timeout(15)->get('https://openlibrary.org/search.json', [
                'title' => $title, 'author' => $author, 'limit' => 5, 'fields' => 'title,author_name,cover_i',
            ]);
            foreach ($r->json('docs') ?? [] as $d) {
                if (empty($d['cover_i']) || $norm((string) ($d['title'] ?? '')) !== $norm($title)) {
                    continue;
                }
                $names = $norm(implode(' ', $d['author_name'] ?? []));
                $ok = false;
                foreach (explode(' ', $norm($author)) as $w) {
                    if (strlen($w) >= 4 && str_contains($names, $w)) {
                        $ok = true;
                        break;
                    }
                }
                if (! $ok) {
                    continue;
                }
                $img = Http::timeout(15)->get("https://covers.openlibrary.org/b/id/{$d['cover_i']}-L.jpg", ['default' => 'false']);
                if ($img->successful() && $this->validImage($img->body())) {
                    return [$img->body(), 'Open Library (judul)'];
                }
            }
        } catch (\Throwable $e) {
        }

        return [null, null];
    }

    // Google Books lewat judul+pengarang; judul harus sama persis dan salah satu kata pengarang cocok.
    private function findCoverGoogleTitle(string $title, string $author): array
    {
        $norm = fn (string $t) => trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($t)));
        $first = trim(explode(',', $author)[0]);
        try {
            $r = Http::timeout(10)->get('https://www.googleapis.com/books/v1/volumes', [
                'q' => 'intitle:"'.$title.'"'.($first !== '' ? ' inauthor:"'.$first.'"' : ''), 'maxResults' => 5,
            ]);
            foreach ($r->json('items') ?? [] as $it) {
                $v = $it['volumeInfo'] ?? [];
                $url = $v['imageLinks']['thumbnail'] ?? null;
                if (! $url || $norm((string) ($v['title'] ?? '')) !== $norm($title)) {
                    continue;
                }
                $names = $norm(implode(' ', $v['authors'] ?? []));
                $ok = false;
                foreach (explode(' ', $norm($author)) as $w) {
                    if (strlen($w) >= 4 && str_contains($names, $w)) {
                        $ok = true;
                        break;
                    }
                }
                if (! $ok) {
                    continue;
                }
                $img = Http::timeout(10)->get(str_replace(['http://', '&edge=curl'], ['https://', ''], $url).'&zoom=2');
                if ($img->successful() && $this->validImage($img->body())) {
                    return [$img->body(), 'Google Books (judul)'];
                }
            }
        } catch (\Throwable $e) {
        }

        return [null, null];
    }

    // Perkiraan kemungkinan sebuah buku punya cover online (makin besar makin mungkin).
    private function coverScore(array $c): float
    {
        $score = 0.0;
        $cat = mb_strtolower($c['category']);
        $pub = mb_strtolower((string) $c['publisher']);
        $title = mb_strtolower($c['title']);

        if (preg_match('/novel|fiksi|kesusastraan|sastra|cerpen|biografi|motivasi|psikologi|agama|islam/', $cat)) {
            $score += 4;
        }
        if (preg_match('/paket|kelas\s*(x|xi|xii)\b|fase|modul|lks|ujian|tes |soal|teknik|instalasi|kurikulum/', $title.' '.$cat)) {
            $score -= 4;
        }
        if (preg_match('/gramedia|mizan|bentang|gagas|republika|diva|noura|elex|bukune|grasindo|deepublish|penerbit buku kompas|sabak|basa basi|laksana|erlangga|andi/', $pub)) {
            $score += 2;
        }
        if ($c['year'] && $c['year'] >= 2015) {
            $score += 1;
        }
        if ($c['isbn'] !== null && str_starts_with($c['isbn'], '978')) {
            $score += 0.5;
        }

        return $score;
    }

    private function validImage(string $bin): bool
    {
        if (strlen($bin) < 6000) {
            return false;   // placeholder / gambar kosong
        }
        $info = @getimagesizefromstring($bin);

        return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
            && $info[0] >= 150 && $info[1] >= 200;
    }

    private function normalizeIsbn(string $raw): ?string
    {
        $s = strtoupper(preg_replace('/[^0-9X]/i', '', $raw));
        if (strlen($s) === 13 && ctype_digit($s)) {
            $sum = 0;
            foreach (str_split(substr($s, 0, 12)) as $i => $d) {
                $sum += (int) $d * ($i % 2 ? 3 : 1);
            }
            return ((10 - $sum % 10) % 10) === (int) $s[12] ? $s : null;
        }
        if (strlen($s) === 10) {
            $sum = 0;
            foreach (str_split($s) as $i => $ch) {
                $sum += ($ch === 'X' ? 10 : (int) $ch) * (10 - $i);
            }
            return $sum % 11 === 0 ? $s : null;
        }

        return null;
    }

    private function detectColumns(array $rows): array
    {
        foreach (array_slice($rows, 0, 15, true) as $at => $row) {
            $cols = [];
            foreach ($row as $i => $name) {
                $key = preg_replace('/[^a-z]/', '', strtolower((string) $name));
                foreach (self::ALIASES as $field => $names) {
                    if (! isset($cols[$field]) && in_array($key, $names, true)) {
                        $cols[$field] = $i;
                    }
                }
            }
            if (isset($cols['title'])) {
                return [$cols, $at];
            }
        }

        return [[], 0];
    }

    private function csvRows(string $file): array
    {
        $h = fopen($file, 'r');
        $first = (string) fgets($h);
        rewind($h);
        $d = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $rows = [];
        while (($r = fgetcsv($h, 0, $d, '"', '\\')) !== false) {
            $rows[] = array_map(fn ($v) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $v)), $r);
        }
        fclose($h);

        return $rows;
    }
}
