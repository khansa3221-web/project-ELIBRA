<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DiagnoseAuth extends Command
{
    protected $signature = 'elibra:diagnose';
    protected $description = 'Periksa database & uji alur daftar -> login (data uji dibatalkan, akun asli tidak diubah)';

    public function handle(): int
    {
        $ok = true;
        $cek = function (string $nama, bool $lulus, string $saran = '') use (&$ok) {
            $this->line(($lulus ? '[OK]    ' : '[GAGAL] ').$nama);
            if (! $lulus) { $ok = false; if ($saran) { $this->line('        -> '.$saran); } }
        };

        $cek('APP_KEY terisi', (bool) config('app.key'), 'Jalankan: php artisan key:generate');

        try { DB::connection()->getPdo(); $cek('Koneksi database', true); }
        catch (\Throwable $e) { $cek('Koneksi database', false, $e->getMessage()); return self::FAILURE; }

        foreach (['users', 'sessions', 'cache', 'books', 'loans', 'user_notifications'] as $t) {
            $cek("Tabel {$t}", Schema::hasTable($t), 'Jalankan: php artisan migrate');
        }
        foreach (['role', 'nis', 'class', 'password'] as $c) {
            $cek("Kolom users.{$c}", Schema::hasColumn('users', $c), 'Jalankan: php artisan migrate');
        }

        // Uji alur nyata dalam transaksi yang dibatalkan
        DB::beginTransaction();
        try {
            $u = User::create([
                'name' => 'Uji Diagnosa', 'email' => 'diagnosa'.time().'@elibra.test',
                'password' => 'Rahasia12345', 'role' => 'siswa', 'nis' => 'DX'.time(), 'class' => 'X',
            ]);
            $cek('Akun baru tersimpan', (bool) User::find($u->id));
            $cek('Password tersimpan sebagai hash (bukan teks asli)', $u->password !== 'Rahasia12345' && str_starts_with($u->password, '$2y$'));
            $cek('Hash::check cocok dengan password yang diketik', Hash::check('Rahasia12345', $u->password), 'Kemungkinan password di-hash dua kali');
            $cek('Ditemukan lewat email', (bool) User::where('email', $u->email)->first());
            $cek('Ditemukan lewat NIS', (bool) User::where('nis', $u->nis)->first());
            $cek('Role = siswa', $u->role === 'siswa');
            $u->unreadNotificationCount();
            $u->loans()->count();
            $cek('Query dashboard siswa baru berjalan', true);
        } catch (\Throwable $e) {
            $cek('Alur daftar -> login', false, get_class($e).': '.$e->getMessage());
        } finally {
            DB::rollBack();
        }

        $this->newLine();
        $this->info($ok ? 'Semua pemeriksaan lulus.' : 'Ada pemeriksaan GAGAL. Kirim hasil ini ke pengembang.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
