<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Support\ReceiptPdf;
use App\Support\SimplePdf;
use App\Support\Tambah30Buku;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase, Concerns;

    private function pinjam($siswa, $buku, string $status = 'active'): Loan
    {
        return Loan::create([
            'user_id' => $siswa->id, 'book_id' => $buku->id, 'pickup_date' => '2026-11-05',
            'duration_days' => 7, 'due_date' => '2026-11-12', 'status' => $status,
        ]);
    }

    public function test_nomor_transaksi_berformat_tetap(): void
    {
        $loan = $this->pinjam($this->bikinUser(), $this->bikinBuku());

        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{5}$/', $loan->transaction_number);
        $this->assertStringEndsWith(str_pad((string) $loan->id, 5, '0', STR_PAD_LEFT), $loan->transaction_number);
    }

    public function test_siswa_melihat_bukti_dengan_data_lengkap_dan_tombol(): void
    {
        $siswa = $this->bikinUser('siswa', ['name' => 'Budi Santoso', 'nis' => '24088', 'class' => 'XI RPL 1']);
        $loan = $this->pinjam($siswa, $this->bikinBuku('Laskar Pelangi'));

        $this->actingAs($siswa)->get(route('loans.receipt', $loan))
            ->assertOk()
            ->assertSee($loan->transaction_number)
            ->assertSee('Budi Santoso')
            ->assertSee('Laskar Pelangi')
            ->assertSee('05 November 2026')
            ->assertSee('12 November 2026')
            ->assertSee('Dipinjam')
            ->assertSee('Cetak')
            ->assertSee('Download PDF')
            ->assertSee(route('loans.receipt.pdf', $loan), false);
    }

    public function test_unduh_pdf_menghasilkan_file_pdf_valid(): void
    {
        $siswa = $this->bikinUser('siswa', ['name' => 'Budi Santoso']);
        $loan = $this->pinjam($siswa, $this->bikinBuku('Laskar Pelangi'));

        $res = $this->actingAs($siswa)->get(route('loans.receipt.pdf', $loan));
        $res->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));

        $pdf = $res->getContent();
        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringEndsWith('%%EOF', $pdf);
        $this->assertStringContainsString($loan->transaction_number, $pdf);
        $this->assertStringContainsString('Budi Santoso', $pdf);
        $this->assertStringContainsString('Laskar Pelangi', $pdf);
        // offset xref harus menunjuk tepat ke "1 0 obj"
        preg_match('/startxref\n(\d+)/', $pdf, $m);
        $this->assertStringStartsWith('xref', substr($pdf, (int) $m[1], 4));
        preg_match('/0000000000 65535 f \n(\d{10})/', $pdf, $o);
        $this->assertStringStartsWith('1 0 obj', substr($pdf, (int) $o[1], 7));
    }

    public function test_siswa_lain_dan_tamu_tidak_bisa_membuka_bukti(): void
    {
        $pemilik = $this->bikinUser();
        $lain = $this->bikinUser();
        $loan = $this->pinjam($pemilik, $this->bikinBuku());

        $this->actingAs($lain)->get(route('loans.receipt', $loan))->assertNotFound();
        $this->actingAs($lain)->get(route('loans.receipt.pdf', $loan))->assertNotFound();
        auth()->logout();
        $this->get(route('loans.receipt', $loan))->assertRedirect(route('login'));
    }

    public function test_pengajuan_belum_disetujui_atau_ditolak_tidak_punya_bukti(): void
    {
        $siswa = $this->bikinUser();
        $pending = $this->pinjam($siswa, $this->bikinBuku('A'), 'pending');
        $ditolak = $this->pinjam($siswa, $this->bikinBuku('B'), 'rejected');

        $this->actingAs($siswa)->get(route('loans.receipt', $pending))->assertForbidden();
        $this->actingAs($siswa)->get(route('loans.receipt.pdf', $ditolak))->assertForbidden();
    }

    public function test_admin_boleh_melihat_bukti_dan_status_dikembalikan_tampil(): void
    {
        $admin = $this->bikinUser('admin');
        $loan = $this->pinjam($this->bikinUser(), $this->bikinBuku(), 'returned');
        $loan->update(['returned_at' => '2026-11-10 09:00:00']);

        $this->actingAs($admin)->get(route('loans.receipt', $loan))
            ->assertOk()->assertSee('Dikembalikan')->assertSee('10 November 2026');
    }

    public function test_halaman_peminjaman_saya_menampilkan_tautan_bukti_hanya_untuk_yang_disetujui(): void
    {
        $siswa = $this->bikinUser();
        $aktif = $this->pinjam($siswa, $this->bikinBuku('Buku Aktif'), 'active');
        $this->pinjam($siswa, $this->bikinBuku('Buku Menunggu'), 'pending');

        $this->actingAs($siswa)->get(route('student.loans'))
            ->assertOk()->assertSee(route('loans.receipt', $aktif), false);
    }

    public function test_pdf_writer_membungkus_teks_panjang_dan_menghitung_lebar(): void
    {
        $p = new SimplePdf();
        $this->assertGreaterThan(0, $p->width('E-LIBRA', 12, true));
        $lines = $p->wrap('Judul buku yang sangat panjang sekali sampai harus pindah baris di kolom sempit', 120, 11, true);
        $this->assertGreaterThan(1, count($lines));
        foreach ($lines as $l) {
            $this->assertLessThanOrEqual(130, $p->width($l, 11, true));
        }
    }

    public function test_30_buku_baru_lengkap_tampil_di_katalog_dan_bisa_dipinjam(): void
    {
        Tambah30Buku::jalankan(true);
        Tambah30Buku::jalankan(true);   // dijalankan dua kali tidak boleh menggandakan

        $daftar = json_decode(file_get_contents(database_path('seeders/daftar-30-buku.json')), true);
        $this->assertCount(29, $daftar);   // 29 buku bercover
        foreach ($daftar as $d) {
            $b = Book::where('title', $d['title'])->first();
            $this->assertNotNull($b, $d['title']);
            $this->assertNotEmpty($b->author);
            $this->assertNotEmpty($b->publisher);
            $this->assertNotEmpty($b->year);
            $this->assertNotNull($b->category_id);
            $this->assertGreaterThan(0, $b->total_stock);
            $this->assertSame($b->total_stock, $b->available_stock);
            $this->assertNotEmpty($b->description);
        }
        $this->assertSame(29, Book::whereIn('title', array_column($daftar, 'title'))->count());

        $this->get(route('catalog'))->assertOk()->assertSee($daftar[0]['title']);

        // bisa dipinjam, lalu admin menyetujui, lalu bukti tersedia
        $siswa = $this->bikinUser();
        $buku = Book::where('title', $daftar[3]['title'])->first();
        $this->actingAs($siswa)->post(route('books.borrow', $buku), [
            'pickup_date' => now()->addDay()->toDateString(), 'duration_days' => 7,
        ])->assertRedirect(route('student.loans'));
        $loan = Loan::firstOrFail();
        $this->actingAs($this->bikinUser('admin'))->post(route('admin.approve', $loan))->assertRedirect();
        $this->actingAs($siswa)->get(route('loans.receipt.pdf', $loan->fresh()))->assertOk();
    }
}
