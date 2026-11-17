<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileCoverLogoutTest extends TestCase
{
    use RefreshDatabase, Concerns;

    protected function tearDown(): void
    {
        foreach (['covers', 'avatars'] as $d) {
            foreach (glob(public_path("uploads/{$d}/*.{jpg,jpeg,png,webp}"), GLOB_BRACE) ?: [] as $f) {
                @unlink($f);
            }
        }
        parent::tearDown();
    }

    public function test_profil_hanya_bisa_dibuka_setelah_login(): void
    {
        $this->get('/profil')->assertRedirect('/login');
    }

    public function test_profil_siswa_menampilkan_data_akun_sendiri(): void
    {
        $a = $this->bikinUser('siswa', ['username' => 'andi']);
        $b = $this->bikinUser('siswa', ['username' => 'budi']);

        $this->actingAs($a)->get('/profil')->assertOk()
            ->assertSee($a->name)->assertSee($a->nis)->assertSee('andi')
            ->assertDontSee($b->name)->assertSee('Logout');
    }

    public function test_siswa_mengubah_profil_tanpa_bisa_mengubah_role_atau_nis(): void
    {
        $u = $this->bikinUser('siswa', ['username' => 'andi']);

        $this->actingAs($u)->put('/profil', [
            'name' => 'Andi Baru', 'username' => 'AndiBaru', 'email' => $u->email, 'kelas' => 'XII RPL 2',
            'role' => 'admin', 'nis' => '999',
        ])->assertRedirect('/profil');

        $u->refresh();
        $this->assertSame('Andi Baru', $u->name);
        $this->assertSame('andibaru', $u->username);
        $this->assertSame('XII RPL 2', $u->class);
        $this->assertSame('siswa', $u->role);
        $this->assertNotSame('999', $u->nis);
    }

    public function test_username_harus_unik(): void
    {
        $this->bikinUser('siswa', ['username' => 'dipakai']);
        $u = $this->bikinUser('siswa', ['username' => 'saya']);

        $this->actingAs($u)->put('/profil', ['name' => 'Nama Saya', 'username' => 'dipakai', 'email' => $u->email, 'kelas' => 'X'])
            ->assertSessionHasErrors('username');
    }

    public function test_foto_profil_valid_tersimpan_dan_file_bukan_gambar_ditolak(): void
    {
        $admin = $this->bikinUser('admin', ['username' => 'petugas']);
        $data = ['name' => 'Petugas Satu', 'username' => 'petugas', 'email' => $admin->email];

        $this->actingAs($admin)->put('/profil', $data + ['photo' => UploadedFile::fake()->create('x.pdf', 20, 'application/pdf')])
            ->assertSessionHasErrors('photo');

        $this->actingAs($admin)->put('/profil', $data + ['photo' => UploadedFile::fake()->image('p.jpg', 300, 300)])
            ->assertSessionHasNoErrors();
        $this->assertNotNull($admin->fresh()->photo);
        $this->assertFileExists(public_path($admin->fresh()->photo));
    }

    public function test_logout_tampil_di_halaman_siswa_dan_admin_dan_berfungsi(): void
    {
        $siswa = $this->bikinUser('siswa');
        $admin = $this->bikinUser('admin');

        foreach (['/dashboard', '/dashboard/peminjaman', '/dashboard/riwayat', '/dashboard/notifikasi', '/profil'] as $url) {
            $this->actingAs($siswa)->get($url)->assertOk()->assertSee(route('logout'), false);
        }
        foreach (['/admin/dashboard', '/admin/buku', '/admin/peminjaman', '/admin/laporan', '/admin/anggota', '/profil'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee(route('logout'), false);
        }

        $this->actingAs($siswa)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_cover_belum_tersedia_dan_qr_sudah_hilang(): void
    {
        $buku = $this->bikinBuku('Tanpa Cover');

        $this->get('/katalog')->assertOk()->assertSee('Cover Belum Tersedia')->assertDontSee('QR');
        $this->get('/katalog/'.$buku->slug)->assertOk()->assertSee('Cover Belum Tersedia')->assertDontSee('QR Code');
        $this->get('/q/abcdef1234')->assertNotFound();
    }

    public function test_admin_unggah_ganti_cover_dan_validasi_gambar(): void
    {
        $admin = $this->bikinUser('admin');
        $buku = $this->bikinBuku('Dengan Cover');
        $base = ['title' => 'Dengan Cover', 'author' => 'P', 'category' => 'Uji', 'total_stock' => 3];

        $this->actingAs($admin)->put("/admin/buku/{$buku->slug}", $base + ['cover' => UploadedFile::fake()->create('x.txt', 5, 'text/plain')])
            ->assertSessionHasErrors('cover');

        $this->actingAs($admin)->put("/admin/buku/{$buku->slug}", $base + ['cover' => UploadedFile::fake()->image('c1.jpg', 300, 400)])
            ->assertSessionHasNoErrors();
        $first = $buku->fresh()->cover_path;
        $this->assertFileExists(public_path($first));

        $this->actingAs($admin)->put("/admin/buku/{$buku->slug}", $base + ['cover' => UploadedFile::fake()->image('c2.png', 300, 400)]);
        $this->assertFileDoesNotExist(public_path($first));
        $this->get('/katalog')->assertSee($buku->fresh()->cover_path, false)->assertDontSee('Cover Belum Tersedia');
    }

    public function test_siswa_tidak_bisa_mengunggah_cover(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku();

        $this->actingAs($siswa)->put("/admin/buku/{$buku->slug}", ['title' => 'x'])->assertStatus(403);
    }
}
