<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH indeks unik NIS jika belum ada (database lama tidak memilikinya). Data tidak diubah.
// Bila ada NIS ganda di data lama, indeks dilewati (aplikasi tetap menolak duplikat baru lewat validasi).
return new class extends Migration
{
    public function up(): void
    {
        try {
            if (! Schema::hasIndex('users', 'users_nis_unique')) {
                Schema::table('users', fn (Blueprint $t) => $t->unique('nis', 'users_nis_unique'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', fn (Blueprint $t) => $t->dropUnique('users_nis_unique'));
        } catch (\Throwable $e) {
        }
    }
};
