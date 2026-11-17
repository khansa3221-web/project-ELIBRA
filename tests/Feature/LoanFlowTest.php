<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\UserNotification;
use App\Services\LoanService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use RefreshDatabase, Concerns;

    private function service(): LoanService
    {
        return app(LoanService::class);
    }

    private function ajukan($siswa, Book $buku): Loan
    {
        return $this->service()->request($siswa, $buku, ['pickup_date' => today()->toDateString(), 'duration_days' => 7]);
    }

    public function test_alur_ajukan_setujui_kembalikan_dan_stok(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Alur Utama', 3);

        $loan = $this->ajukan($siswa, $buku);
        $this->assertSame('pending', $loan->status);
        $this->assertSame(3, $buku->fresh()->available_stock);       // stok belum berkurang saat pengajuan

        $this->service()->approve($loan);
        $this->assertSame('active', $loan->fresh()->status);
        $this->assertSame(2, $buku->fresh()->available_stock);

        $this->service()->returnBook($loan);
        $this->assertSame('returned', $loan->fresh()->status);
        $this->assertSame(0, $loan->fresh()->fine);
        $this->assertSame(3, $buku->fresh()->available_stock);

        $tipe = UserNotification::where('user_id', $siswa->id)->pluck('type')->all();
        foreach (['submitted', 'approved', 'returned'] as $t) {
            $this->assertContains($t, $tipe);
        }
    }

    public function test_persetujuan_tidak_bisa_diproses_dua_kali(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Dua Kali', 3);
        $loan = $this->ajukan($siswa, $buku);

        $this->service()->approve($loan);

        $this->expectException(DomainException::class);
        try {
            $this->service()->approve($loan);
        } finally {
            $this->assertSame(2, $buku->fresh()->available_stock);  // stok tidak berkurang lagi
        }
    }

    public function test_pengajuan_yang_sudah_disetujui_tidak_bisa_ditolak(): void
    {
        $siswa = $this->bikinUser('siswa');
        $loan = $this->ajukan($siswa, $this->bikinBuku('Tolak Setelah Setuju'));
        $this->service()->approve($loan);

        $this->expectException(DomainException::class);
        $this->service()->reject($loan, 'terlambat');
    }

    public function test_pengajuan_ganda_untuk_buku_sama_ditolak(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Ganda', 3);
        $this->ajukan($siswa, $buku);

        $this->expectException(DomainException::class);
        $this->ajukan($siswa, $buku);
    }

    public function test_stok_habis_tidak_bisa_diajukan(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Habis', 2, 0);

        $this->expectException(DomainException::class);
        $this->ajukan($siswa, $buku);
    }

    public function test_denda_dihitung_per_hari_terlambat(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Telat', 3, 2);
        $loan = Loan::create(['user_id' => $siswa->id, 'book_id' => $buku->id, 'pickup_date' => today()->subDays(10),
            'duration_days' => 7, 'due_date' => today()->subDays(3), 'status' => 'active']);

        $this->service()->returnBook($loan);

        $this->assertSame(3 * Loan::FINE_PER_DAY, $loan->fresh()->fine);
        $this->assertSame(3, $buku->fresh()->available_stock);
    }

    public function test_stok_tidak_melebihi_total_saat_pengembalian(): void
    {
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Batas', 3, 3);   // data tidak konsisten sengaja
        $loan = Loan::create(['user_id' => $siswa->id, 'book_id' => $buku->id, 'pickup_date' => today(),
            'duration_days' => 7, 'due_date' => today()->addDays(7), 'status' => 'active']);

        $this->service()->returnBook($loan);
        $this->assertSame(3, $buku->fresh()->available_stock);
    }

    public function test_penandaan_terlambat_dan_pengingat_tidak_ganda(): void
    {
        $siswa = $this->bikinUser('siswa');
        $telat = Loan::create(['user_id' => $siswa->id, 'book_id' => $this->bikinBuku('Lewat Tempo')->id, 'pickup_date' => today()->subDays(9),
            'duration_days' => 7, 'due_date' => today()->subDay(), 'status' => 'active']);
        $besok = Loan::create(['user_id' => $siswa->id, 'book_id' => $this->bikinBuku('Besok Tempo')->id, 'pickup_date' => today()->subDays(6),
            'duration_days' => 7, 'due_date' => today()->addDay(), 'status' => 'active']);

        $this->service()->remind();
        $this->service()->remind();   // dijalankan dua kali

        $this->assertSame('late', $telat->fresh()->status);
        $this->assertSame(1, UserNotification::where('loan_id', $telat->id)->where('type', 'overdue')->count());
        $this->assertSame(1, UserNotification::where('loan_id', $besok->id)->where('type', 'due_soon')->count());
    }

    public function test_alur_http_admin_setujui_lalu_kembalikan(): void
    {
        $admin = $this->bikinUser('admin');
        $siswa = $this->bikinUser('siswa');
        $buku = $this->bikinBuku('Lewat HTTP', 2);

        $this->actingAs($siswa)->post(route('books.borrow', $buku), ['pickup_date' => today()->toDateString(), 'duration_days' => 7])
            ->assertRedirect(route('student.loans'));
        $loan = Loan::firstOrFail();

        $this->actingAs($admin)->post(route('admin.approve', $loan))->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.approve', $loan))->assertSessionHas('error');   // kedua kali ditolak
        $this->assertSame(1, $buku->fresh()->available_stock);

        $this->actingAs($admin)->post(route('admin.loans.return', $loan))->assertSessionHas('success');
        $this->assertSame(2, $buku->fresh()->available_stock);
        $this->actingAs($admin)->get(route('admin.loans'))->assertOk();
    }

    public function test_siswa_tidak_bisa_menyetujui_atau_mengembalikan(): void
    {
        $siswa = $this->bikinUser('siswa');
        $loan = $this->ajukan($siswa, $this->bikinBuku('Terlarang'));

        $this->actingAs($siswa)->post(route('admin.approve', $loan))->assertForbidden();
        $this->actingAs($siswa)->post(route('admin.loans.return', $loan))->assertForbidden();
    }

    public function test_popularitas_dihitung_dari_peminjaman_nyata(): void
    {
        $siswa = $this->bikinUser('siswa');
        $populer = $this->bikinBuku('Populer');
        $sepi = $this->bikinBuku('Sepi');
        Loan::create(['user_id' => $siswa->id, 'book_id' => $populer->id, 'pickup_date' => today(), 'duration_days' => 7, 'due_date' => today()->addDays(7), 'status' => 'returned']);
        Loan::create(['user_id' => $siswa->id, 'book_id' => $sepi->id, 'pickup_date' => today(), 'duration_days' => 7, 'due_date' => today()->addDays(7), 'status' => 'rejected']);

        $urut = Book::withBorrowCount()->orderByDesc('loans_count')->get();
        $this->assertSame('Populer', $urut->first()->title);
        $this->assertSame(0, (int) $urut->firstWhere('title', 'Sepi')->loans_count);   // ditolak tidak dihitung
    }
}
