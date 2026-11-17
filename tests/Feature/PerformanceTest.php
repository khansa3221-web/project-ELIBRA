<?php

namespace Tests\Feature;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menjaga optimasi performa agar hasilnya tetap sama dengan perilaku sebelumnya. */
class PerformanceTest extends TestCase
{
    use RefreshDatabase, Concerns;

    private function pinjam($user, $buku, string $status, $due, int $denda = 0): Loan
    {
        return Loan::create([
            'user_id' => $user->id, 'book_id' => $buku->id, 'pickup_date' => today(), 'duration_days' => 7,
            'due_date' => $due, 'status' => $status, 'fine' => $denda,
        ]);
    }

    public function test_statistik_siswa_satu_query_hasilnya_sama_dengan_aturan_lama(): void
    {
        $siswa = $this->bikinUser('siswa');
        $lain  = $this->bikinUser('siswa');
        $buku  = $this->bikinBuku('Statistik', 9);

        $this->pinjam($siswa, $buku, 'pending', today()->addDays(7));
        $this->pinjam($siswa, $buku, 'active', today()->addDays(1));    // segera jatuh tempo
        $this->pinjam($siswa, $buku, 'active', today()->subDays(2));    // lewat jatuh tempo
        $this->pinjam($siswa, $buku, 'returned', today()->subDays(9), 1000);
        $this->pinjam($lain, $buku, 'active', today()->addDays(1));     // milik siswa lain: tidak dihitung

        $this->assertSame(
            ['total' => 4, 'active' => 2, 'returned' => 1, 'pending' => 1, 'late' => 1, 'due_soon' => 1, 'fine' => 1000],
            Loan::statsFor($siswa->id)
        );
        $this->assertSame(
            ['total' => 0, 'active' => 0, 'returned' => 0, 'pending' => 0, 'late' => 0, 'due_soon' => 0, 'fine' => 0],
            Loan::statsFor(999999)
        );
    }

    public function test_beranda_yang_di_cache_diperbarui_saat_buku_bertambah(): void
    {
        $this->bikinBuku('Satu');
        $this->get('/beranda')->assertOk()->assertSee('<div class="v">1</div><div class="l">JUDUL BUKU</div>', false);

        $this->bikinBuku('Dua');   // event model membersihkan cache beranda
        $this->get('/beranda')->assertOk()->assertSee('<div class="v">2</div><div class="l">JUDUL BUKU</div>', false);
    }

    public function test_filter_kategori_katalog_tetap_benar(): void
    {
        $this->bikinBuku('Buku Dalam Kategori Uji');

        $this->get('/katalog?category=uji')->assertOk()->assertSee('Buku Dalam Kategori Uji');
        $this->get('/katalog?category=tidak-ada')->assertOk()->assertDontSee('Buku Dalam Kategori Uji');
    }

    public function test_aset_dimuat_dengan_versi_agar_aman_di_cache_browser(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~(build/elibra\.min\.css|css/dreamy\.css)\?v=\d+~', $html);
        $this->assertStringContainsString('media="print" onload="this.media=\'all\'"', $html);   // font tidak memblokir tampilan
    }
}
