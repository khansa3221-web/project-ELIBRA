<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

// php artisan elibra:pasang-cover
// Memasang foto cover yang sudah ada di public/uploads/covers ke buku yang judulnya SAMA PERSIS
// (daftar pasangan ada di database/seeders/cover-map.json). Aman dijalankan berulang.
class PasangCover extends Command
{
    protected $signature = 'elibra:pasang-cover {--timpa : Ganti juga cover yang sudah terisi}';

    protected $description = 'Pasang foto cover buku sesuai judul (cover-map.json)';

    public function handle(): int
    {
        $file = database_path('seeders/cover-map.json');
        $map = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        if (! is_array($map)) {
            $this->error('cover-map.json tidak ditemukan atau rusak.');
            return self::FAILURE;
        }

        $rows = [];
        foreach ($map as $judul => $path) {
            $book = Book::whereRaw('LOWER(title) = ?', [mb_strtolower($judul)])->first();
            if (! $book) {
                $rows[] = [$judul, 'buku tidak ada di database'];
            } elseif (! is_file(public_path($path))) {
                $rows[] = [$judul, "file {$path} tidak ada"];
            } elseif ($book->cover_path && ! $this->option('timpa')) {
                $rows[] = [$judul, 'dilewati (sudah punya cover; pakai --timpa)'];
            } else {
                $book->update(['cover_path' => $path]);
                $rows[] = [$judul, 'cover terpasang'];
            }
        }
        $this->table(['Judul', 'Hasil'], $rows);

        return self::SUCCESS;
    }
}
