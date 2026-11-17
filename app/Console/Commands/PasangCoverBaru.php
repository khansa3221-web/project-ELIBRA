<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// php artisan elibra:pasang-cover-baru
// Menambahkan 30 buku dari database/seeders/daftar-30-buku.json. Cover diambil dari folder
// cover-baru (01.jpg, 02.png, ...) atau, jika tidak ada, dicari otomatis lewat ISBN.
// Buku lama tanpa cover (dan tanpa riwayat pinjam) dihapus hanya jika SEMUA 30 buku punya cover.
class PasangCoverBaru extends Command
{
    protected $signature = 'elibra:pasang-cover-baru {--paksa : Hapus buku tanpa cover walau belum semua dari 30 buku punya cover}
        {--tambah-saja : Tambahkan semua 30 buku (yang covernya belum ada tetap ditambahkan tanpa cover), tanpa menghapus apa pun}';
    protected $description = 'Pasang 30 buku baru bercover dan ganti buku lama tanpa cover';

    public function handle(): int
    {
        $file = database_path('seeders/daftar-30-buku.json');
        $daftar = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        if (! is_array($daftar)) {
            $this->error('File daftar-30-buku.json tidak ditemukan.');
            return self::FAILURE;
        }

        $folder = base_path('cover-baru');
        if (! is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $dir = public_path('uploads/covers');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ok = 0;
        $kurang = [];
        foreach ($daftar as $d) {
            $no = sprintf('%02d', $d['no']);
            $book = Book::where('title', $d['title'])->first();

            if ($book && $book->cover_url) {
                $ok++;
                $this->line("  {$no}  {$d['title']}  (sudah ada, cover sudah terpasang)");
                continue;
            }

            [$bin, $sumber] = $this->ambilCover($folder, $no, $d['isbn']);
            $path = null;
            if ($bin === null) {
                $kurang[] = "{$no}  {$d['title']}";
                if (! $this->option('tambah-saja') || $book) {
                    $this->warn("  {$no}  {$d['title']}  (belum ada foto cover)");
                    continue;
                }
                $sumber = 'tanpa cover';
            } else {
                $info = @getimagesizefromstring($bin);
                $ext = match ($info['mime'] ?? '') { 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
                $path = 'uploads/covers/'.Str::random(32).'.'.$ext;
                file_put_contents(public_path($path), $bin);
            }

            if ($book) {
                $book->update(['cover_path' => $path]);
            } else {
                $cat = Category::firstOrCreate(['slug' => Str::slug($d['category']) ?: 'novel'], ['name' => $d['category']]);
                $base = Str::slug($d['title']) ?: 'buku';
                $slug = $base;
                for ($i = 2; Book::where('slug', $slug)->exists(); $i++) {
                    $slug = $base.'-'.$i;
                }
                Book::create([
                    'category_id' => $cat->id, 'title' => $d['title'], 'slug' => $slug, 'author' => $d['author'],
                    'publisher' => $d['publisher'], 'year' => $d['year'], 'isbn' => $d['isbn'],
                    'language' => 'Indonesia', 'description' => $d['description'] ?? null, 'total_stock' => $d['stock'], 'available_stock' => $d['stock'],
                    'cover_gradient' => 'mc'.(($cat->id % 6) + 1), 'cover_path' => $path,
                ]);
            }
            $ok++;
            $this->info("  {$no}  {$d['title']}  OK ({$sumber})");
        }

        $total = count($daftar);
        $this->line('');
        $this->info("{$ok} dari {$total} buku sudah punya cover.");

        if ($this->option('tambah-saja')) {
            $this->info('Selesai: semua buku dalam daftar sudah ditambahkan. Tidak ada buku yang dihapus.');
            if ($kurang) {
                $this->warn(count($kurang).' buku belum punya cover (tampil "Cover Belum Tersedia"). Cover bisa diunggah lewat Data Buku > Ubah.');
            }
            return self::SUCCESS;
        }

        if ($kurang && ! $this->option('paksa')) {
            $this->warn('Masih kurang foto untuk nomor berikut. Simpan fotonya di folder cover-baru (nama 01.jpg dst), lalu jalankan lagi:');
            foreach ($kurang as $k) {
                $this->line("   {$k}");
            }
            $this->warn('Buku lama tanpa cover BELUM dihapus (menunggu semua lengkap).');
            return self::SUCCESS;
        }

        $removed = 0;
        Book::whereDoesntHave('loans')->get()->each(function (Book $b) use (&$removed) {
            if (! $b->cover_url) {
                $b->delete();
                $removed++;
            }
        });
        Category::doesntHave('books')->delete();
        $this->info("{$removed} buku lama tanpa cover (tanpa riwayat peminjaman) dihapus.");

        return self::SUCCESS;
    }

    private function ambilCover(string $folder, string $no, ?string $isbn): array
    {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'] as $e) {
            foreach ([$no, ltrim($no, '0')] as $nama) {
                $f = "{$folder}/{$nama}.{$e}";
                if (is_file($f) && @getimagesize($f)) {
                    return [file_get_contents($f), 'foto kamu'];
                }
            }
        }
        if ($isbn) {
            try {
                $r = Http::timeout(10)->get("https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg", ['default' => 'false']);
                if ($r->successful() && strlen($r->body()) > 3000 && @getimagesizefromstring($r->body())) {
                    return [$r->body(), 'Open Library'];
                }
            } catch (\Throwable $e) {
            }
        }

        return [null, null];
    }
}
