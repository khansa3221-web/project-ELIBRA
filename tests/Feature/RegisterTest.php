<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase, Concerns;

    public function test_nis_di_luar_daftar_ditolak(): void
    {
        $this->from('/register')->post('/register', $this->data(['nis' => '55555']))
            ->assertRedirect('/register')->assertSessionHasErrors('nis');
        $this->get('/register')->assertOk()->assertSee('tidak ada dalam daftar siswa');
        $this->assertSame(0, User::count());
        $this->assertGuest();
    }

    private function data(array $o = []): array
    {
        return array_merge([
            'name' => 'Siswa Baru', 'kelas' => 'X RPL 1', 'nis' => '24088',
            'email' => 'baru@elibra.test', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ], $o);
    }

    public function test_halaman_register_terbuka_dan_login_menautkannya(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('register'), false);
        $this->get('/register')->assertOk()->assertSee('Buat akun baru');
    }

    public function test_registrasi_berhasil_tersimpan_hash_dan_role_siswa(): void
    {
        // Setelah daftar langsung masuk ke dashboard siswa (bukan kembali ke login)
        $this->post('/register', $this->data(['role' => 'admin']))->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();

        $u = User::where('email', 'baru@elibra.test')->firstOrFail();
        $this->assertSame('siswa', $u->role);
        $this->assertNotSame('rahasia123', $u->password);
        $this->assertTrue(password_verify('rahasia123', $u->password));

        $this->post('/logout');
        $this->post('/login', ['login' => '24088', 'password' => 'rahasia123'])
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_setelah_daftar_dashboard_langsung_terbuka(): void
    {
        $this->post('/register', $this->data());
        $this->get('/dashboard')->assertOk()->assertSee('Registrasi berhasil');
    }

    public function test_duplikat_dan_konfirmasi_salah_ditolak(): void
    {
        $this->bikinUser('siswa', ['nis' => '24088', 'email' => 'baru@elibra.test']);

        $this->post('/register', $this->data())->assertSessionHasErrors(['nis', 'email']);
        $this->post('/register', $this->data(['nis' => '24089', 'email' => 'x@elibra.test', 'password_confirmation' => 'beda']))
            ->assertSessionHasErrors('password');
        $this->assertSame(1, User::count());
    }

    public function test_akun_baru_bisa_login_lewat_email_nis_dan_membuka_dashboard(): void
    {
        $this->post('/register', $this->data());
        $this->post('/logout');

        $this->post('/login', ['login' => 'baru@elibra.test', 'password' => 'rahasia123'])
            ->assertRedirect(route('student.dashboard'));
        $this->get('/dashboard')->assertOk();

        $this->post('/logout');
        $this->post('/login', ['login' => '24088', 'password' => 'salah-banget'])->assertSessionHasErrors('login');
        $this->post('/login', ['login' => '24088', 'password' => 'rahasia123'])->assertRedirect(route('student.dashboard'));
    }

    public function test_dashboard_siswa_menerima_semua_kunci_statistik(): void
    {
        $u = $this->bikinUser('siswa');

        $this->actingAs($u)->get('/dashboard')->assertOk()->assertViewHas('stats', function ($s) {
            foreach (['total', 'active', 'returned', 'pending', 'late', 'due_soon', 'fine'] as $k) {
                if (! array_key_exists($k, $s) || $s[$k] !== 0) {
                    return false;
                }
            }

            return true;
        });

        $this->actingAs($u)->get('/dashboard/riwayat')->assertOk();
        $this->actingAs($u)->get('/dashboard/notifikasi')->assertOk();
    }
}
