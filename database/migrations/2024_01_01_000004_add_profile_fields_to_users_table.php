<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kolom role/nis/class sudah dibuat di migrasi create_users_table.
// Migrasi ini dibiarkan ada (supaya riwayat migrasi lama tetap cocok),
// tapi hanya menambah kolom yang belum ada, jadi aman di database kosong.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('siswa')->after('password'); // siswa | admin
            }
            if (! Schema::hasColumn('users', 'nis')) {
                $table->string('nis')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'class')) {
                $table->string('class')->nullable()->after('nis');
            }
        });
    }

    public function down(): void
    {
        // Sengaja kosong: kolom ini dimiliki oleh migrasi create_users_table.
    }
};