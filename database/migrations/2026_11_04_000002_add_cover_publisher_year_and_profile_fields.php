<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Hanya MENAMBAH kolom. Tidak ada data yang dihapus atau diubah (selain mengisi username kosong).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'publisher')) {
                $table->string('publisher', 150)->nullable();
            }
            if (! Schema::hasColumn('books', 'year')) {
                $table->unsignedSmallInteger('year')->nullable();
            }
            if (! Schema::hasColumn('books', 'cover_path')) {
                $table->string('cover_path', 255)->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 40)->nullable();
            }
            if (! Schema::hasColumn('users', 'photo')) {
                $table->string('photo', 255)->nullable();
            }
        });

        // Isi username akun lama: NIS (siswa) atau bagian depan email (admin); dijamin unik.
        $dipakai = [];
        foreach (DB::table('users')->orderBy('id')->get(['id', 'nis', 'email', 'username']) as $u) {
            if ($u->username) {
                $dipakai[strtolower($u->username)] = true;
                continue;
            }
            $dasar = Str::slug($u->nis ?: Str::before((string) $u->email, '@'), '') ?: 'user'.$u->id;
            $nama = $dasar;
            for ($i = 2; isset($dipakai[strtolower($nama)]); $i++) {
                $nama = $dasar.$i;
            }
            $dipakai[strtolower($nama)] = true;
            DB::table('users')->where('id', $u->id)->update(['username' => $nama]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'photo']);
        });
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['publisher', 'year', 'cover_path']);
        });
    }
};
