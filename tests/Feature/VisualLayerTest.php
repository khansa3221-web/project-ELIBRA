<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menjaga lapisan visual/animasi: aset aman di balik Ngrok/proxy HTTPS & komponen baru tidak merusak halaman. */
class VisualLayerTest extends TestCase
{
    use RefreshDatabase, Concerns;

    public function test_aset_memakai_https_dan_host_publik_di_balik_proxy_ngrok(): void
    {
        $html = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host'  => 'contoh.ngrok-free.app',
        ])->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('https://contoh.ngrok-free.app/css/dreamy.css', $html);
        $this->assertStringNotContainsString('http://localhost', $html);
        $this->assertStringNotContainsString('http://contoh.ngrok-free.app/css', $html);
    }

    public function test_beranda_memuat_rotating_words_dan_highlight(): void
    {
        $this->bikinBuku('Contoh Beranda');
        $this->get('/beranda')->assertOk()
            ->assertSee('data-rotate=', false)
            ->assertSee('Discover Your Next Favorite Book')
            ->assertSee('class="hl"', false);
    }

    public function test_empty_state_berilustrasi_tampil_di_halaman_siswa_dan_admin(): void
    {
        $siswa = $this->bikinUser('siswa');
        $this->actingAs($siswa)->get('/dashboard/riwayat')->assertOk()->assertSee('data-empty="history"', false);
        $this->actingAs($siswa)->get('/dashboard/notifikasi')->assertOk()->assertSee('data-empty="bell"', false);
        $this->actingAs($siswa)->get('/dashboard/peminjaman')->assertOk()->assertSee('data-empty="loan"', false);

        $admin = $this->bikinUser('admin');
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('data-empty="inbox"', false)->assertSee('data-empty="chart"', false);
        $this->actingAs($admin)->get('/admin/laporan')->assertOk();
        $this->actingAs($admin)->get('/admin/anggota')->assertOk();
    }

    public function test_ornamen_panel_ada_di_halaman_panel(): void
    {
        $this->actingAs($this->bikinUser('siswa'))->get('/dashboard')->assertOk()->assertSee('class="panel-art"', false);
    }

    public function test_katalog_kosong_menampilkan_ilustrasi_pencarian(): void
    {
        $this->get('/katalog?q=tidak-ada-judul-ini')->assertOk()->assertSee('data-empty="search"', false);
    }

    public function test_buku_terkait_memakai_class_responsif_bukan_inline_style(): void
    {
        $a = $this->bikinBuku('Satu'); $b = $this->bikinBuku('Dua');
        $this->get(route('books.show', $a))->assertOk()->assertDontSee('repeat(4,1fr)', false);
    }
}
