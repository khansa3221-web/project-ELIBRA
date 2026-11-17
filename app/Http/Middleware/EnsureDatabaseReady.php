<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bila skema database belum sesuai kode (migrasi belum dijalankan), tampilkan penjelasan
 * yang jelas — bukan error SQL acak di halaman dashboard. Tidak menyembunyikan apa pun:
 * daftar tabel/kolom yang kurang ditampilkan apa adanya.
 */
class EnsureDatabaseReady
{
    /** tabel => kolom wajib ([] = cukup tabelnya ada) */
    private const REQUIRED = [
        'users'              => ['role', 'nis', 'class'],
        'books'              => ['qr_token', 'available_stock', 'total_stock'],
        'loans'              => ['reject_reason', 'fine', 'due_date', 'returned_at'],
        'user_notifications' => ['loan_id', 'message', 'read_at'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $missing = [];

        try {
            foreach (self::REQUIRED as $table => $columns) {
                if (! Schema::hasTable($table)) {
                    $missing[] = "tabel {$table}";
                    continue;
                }
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($table, $column)) {
                        $missing[] = "kolom {$table}.{$column}";
                    }
                }
            }
        } catch (\Throwable $e) {
            // Koneksi database sendiri bermasalah: biarkan Laravel menampilkan error aslinya.
            return $next($request);
        }

        if ($missing) {
            return response()->view('errors.migrate', ['missing' => $missing], 503);
        }

        return $next($request);
    }
}
