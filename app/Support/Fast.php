<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

// Cache ringan untuk data yang sering dibaca tapi jarang berubah (beranda, kategori, jumlah buku).
// Bila cache bermasalah (mis. tabel cache belum dimigrasi) halaman tetap jalan tanpa cache.
class Fast
{
    public const KEYS = ['elibra.landing', 'elibra.categories', 'elibra.book-count'];

    public static function remember(string $key, int $seconds, Closure $callback)
    {
        try {
            return Cache::remember($key, $seconds, $callback);
        } catch (\Throwable $e) {
            report($e);

            return $callback();
        }
    }

    // Dipanggil otomatis saat Buku/Peminjaman/Kategori/Anggota berubah, agar data tidak basi.
    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                // abaikan
            }
        }
    }
}
