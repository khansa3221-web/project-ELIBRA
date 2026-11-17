<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Menambahkan buku dari database/seeders/daftar-30-buku.json (kini 22 buku bercover) satu kali saja,
// otomatis saat website pertama kali dibuka (tanpa perintah/bat).
// v3/v4 (v4: +7 buku pelajaran bercover): 8 buku yang sengaja tidak dipasang covernya (lihat DIHAPUS) dibuang dari daftar; bila sudah
// telanjur ada di database, dihapus HANYA jika belum punya cover dan belum pernah dipinjam.
// Penanda selesai: storage/app/tambah-30-buku-v4.done (hapus file itu bila ingin diulang).
class Tambah30Buku
{
    // Buku dari daftar lama yang tidak jadi dipakai (diganti buku lain bercover).
    public const DIHAPUS = [
        'Negeri Di Ujung Tanduk', 'Harga Sebuah Percaya', 'Kau, Aku, & Sepucuk Angpau Merah', 'Seopotong Hati Yang Baru',
        'Dalam Mihrab Cinta', 'Sang Singa, Sang Penyihir Dari Lampau', 'Pria Tak Tampak', 'Jo & Kas',
    ];

    public static function jalankan(bool $paksa = false): void
    {
        $flag = storage_path('app/tambah-30-buku-v4.done');
        if (! $paksa && (app()->runningInConsole() || app()->runningUnitTests() || is_file($flag))) {
            return;
        }

        try {
            if (! Schema::hasTable('books') || ! Schema::hasColumn('books', 'cover_path') || ! Schema::hasTable('categories')) {
                return; // database belum siap; coba lagi di kunjungan berikutnya
            }
            $file = database_path('seeders/daftar-30-buku.json');
            $daftar = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
            if (! is_array($daftar)) {
                return;
            }

            foreach ($daftar as $d) {
                $ada = Book::where('title', $d['title'])->first()
                    ?? ($d['isbn'] ? Book::where('isbn', $d['isbn'])->first() : null);
                if ($ada) {
                    if (! $ada->description && ! empty($d['description'])) {
                        $ada->update(['description' => $d['description']]);   // lengkapi sinopsis buku yang sudah ada
                    }
                    continue;
                }
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
                    'cover_gradient' => 'mc'.(($cat->id % 6) + 1), 'cover_path' => null,
                ]);
            }

            // buang buku tanpa cover yang tidak jadi dipakai (aman: yang punya riwayat pinjam tidak disentuh)
            foreach (Book::whereIn('title', self::DIHAPUS)->get() as $b) {
                if (! $b->cover_url && ! $b->loans()->exists()) {
                    $b->delete();
                }
            }

            if ($paksa) {
                return;
            }
            if (! is_dir(dirname($flag))) {
                mkdir(dirname($flag), 0777, true);
            }
            file_put_contents($flag, date('c'));
        } catch (\Throwable $e) {
            report($e); // jangan sampai website error karena ini
        }
    }
}
