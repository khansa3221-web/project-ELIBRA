<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Hanya MENAMBAH kolom qr_token (unik) dan mengisi buku lama. Data lain tidak disentuh.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('books', 'qr_token')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('qr_token', 16)->nullable()->after('slug');
            });
        }

        $dipakai = DB::table('books')->whereNotNull('qr_token')->pluck('qr_token')->flip()->all();
        foreach (DB::table('books')->whereNull('qr_token')->orderBy('id')->pluck('id') as $id) {
            do {
                $token = Str::lower(Str::random(10));
            } while (isset($dipakai[$token]));
            $dipakai[$token] = true;
            DB::table('books')->where('id', $id)->update(['qr_token' => $token]);
        }

        Schema::table('books', function (Blueprint $table) {
            $table->unique('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
