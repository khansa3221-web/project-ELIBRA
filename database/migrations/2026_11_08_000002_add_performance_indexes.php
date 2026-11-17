<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH indeks agar query katalog, dashboard, laporan, dan pencarian lebih cepat.
// Tidak ada data/kolom yang diubah atau dihapus. Indeks yang sudah ada dilewati.
return new class extends Migration
{
    public function up(): void
    {
        $this->tambah('loans', ['user_id', 'status'], 'loans_user_status_idx');      // dashboard/riwayat siswa
        $this->tambah('loans', ['book_id', 'status'], 'loans_book_status_idx');      // hitung buku terpopuler
        $this->tambah('loans', ['status', 'due_date'], 'loans_status_due_idx');      // pengingat & terlambat
        $this->tambah('loans', ['status', 'created_at'], 'loans_status_created_idx'); // laporan bulanan & daftar admin
        $this->tambah('books', ['category_id'], 'books_category_idx');               // filter kategori
        $this->tambah('books', ['title'], 'books_title_idx');                        // urut judul
        $this->tambah('books', ['available_stock'], 'books_available_idx');          // filter "tersedia saja"
        $this->tambah('users', ['role'], 'users_role_idx');                          // hitung anggota
    }

    public function down(): void
    {
        foreach ([
            'loans' => ['loans_user_status_idx', 'loans_book_status_idx', 'loans_status_due_idx', 'loans_status_created_idx'],
            'books' => ['books_category_idx', 'books_title_idx', 'books_available_idx'],
            'users' => ['users_role_idx'],
        ] as $tabel => $nama) {
            foreach ($nama as $n) {
                try {
                    Schema::table($tabel, fn (Blueprint $t) => $t->dropIndex($n));
                } catch (\Throwable $e) {
                }
            }
        }
    }

    private function tambah(string $tabel, array $kolom, string $nama): void
    {
        try {
            if (! Schema::hasTable($tabel)) {
                return;
            }
            foreach (Schema::getIndexes($tabel) as $idx) {
                if (($idx['name'] ?? '') === $nama || ($idx['columns'] ?? []) === $kolom) {
                    return; // sudah ada
                }
            }
            Schema::table($tabel, fn (Blueprint $t) => $t->index($kolom, $nama));
        } catch (\Throwable $e) {
            report($e); // jangan gagalkan migrasi hanya karena satu indeks
        }
    }
};
