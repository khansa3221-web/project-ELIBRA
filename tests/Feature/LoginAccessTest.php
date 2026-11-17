<?php

namespace Tests\Feature;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAccessTest extends TestCase
{
    use RefreshDatabase, Concerns;

    public function test_login_siswa_dan_admin_diarahkan_sesuai_role(): void
    {
        $siswa = $this->bikinUser('siswa');
        $admin = $this->bikinUser('admin');

        $this->post('/login', ['login' => $siswa->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('student.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['login' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_bisa_memakai_nis(): void
    {
        $siswa = $this->bikinUser('siswa', ['nis' => '99887']);

        $this->post('/login', ['login' => '99887', 'password' => 'rahasia123'])
            ->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($siswa);
    }

    public function test_password_tersimpan_dalam_bentuk_hash(): void
    {
        $siswa = $this->bikinUser('siswa');

        $this->assertNotSame('rahasia123', $siswa->password);
        $this->assertTrue(password_verify('rahasia123', $siswa->password));
    }

    public function test_login_dibatasi_setelah_lima_kali_gagal(): void
    {
        $siswa = $this->bikinUser('siswa');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login' => $siswa->email, 'password' => 'salah'])->assertSessionHasErrors('login');
        }

        // percobaan ke-6 diblokir walau password benar
        $this->post('/login', ['login' => $siswa->email, 'password' => 'rahasia123'])->assertSessionHasErrors('login');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('login'));
        $this->assertGuest();
    }

    public function test_login_dengan_nama_kembar_ditolak(): void
    {
        $this->bikinUser('siswa', ['name' => 'Budi']);
        $this->bikinUser('siswa', ['name' => 'Budi']);

        $this->post('/login', ['login' => 'budi', 'password' => 'rahasia123'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_hak_akses_antar_role(): void
    {
        $siswa = $this->bikinUser('siswa');
        $admin = $this->bikinUser('admin');

        $this->actingAs($siswa)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($siswa)->get('/admin/peminjaman')->assertForbidden();
        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
    }

    public function test_siswa_hanya_melihat_peminjaman_miliknya(): void
    {
        $a = $this->bikinUser('siswa');
        $b = $this->bikinUser('siswa');
        $bukuA = $this->bikinBuku('Judul Milik A');
        $bukuB = $this->bikinBuku('Judul Milik B');
        foreach ([[$a, $bukuA], [$b, $bukuB]] as [$u, $bk]) {
            Loan::create(['user_id' => $u->id, 'book_id' => $bk->id, 'pickup_date' => today(), 'duration_days' => 7,
                'due_date' => today()->addDays(7), 'status' => 'pending']);
        }

        $this->actingAs($a)->get('/dashboard/peminjaman')
            ->assertOk()->assertSee('Judul Milik A')->assertDontSee('Judul Milik B');
        $this->actingAs($a)->get('/dashboard/riwayat')->assertOk()->assertDontSee('Judul Milik B');
    }

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/login')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
