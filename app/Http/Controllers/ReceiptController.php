<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Support\ReceiptPdf;

class ReceiptController extends Controller
{
    // Halaman bukti (siap cetak) — hanya pemilik peminjaman atau admin
    public function show(Loan $loan)
    {
        return view('loans.receipt', ['r' => $this->data($this->authorizeLoan($loan)), 'loan' => $loan]);
    }

    // Unduh PDF bukti peminjaman
    public function pdf(Loan $loan)
    {
        $d = $this->data($this->authorizeLoan($loan));

        return response(ReceiptPdf::render($d), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Bukti-Peminjaman-'.$d['trx'].'.pdf"',
            'Cache-Control'       => 'private, no-store',
        ]);
    }

    private function authorizeLoan(Loan $loan): Loan
    {
        $user = auth()->user();
        // Siswa lain tidak boleh melihat (404 agar keberadaan data tidak terbongkar)
        abort_unless($user && ($user->id === $loan->user_id || $user->role === 'admin'), 404);
        abort_unless($loan->has_receipt, 403, 'Bukti peminjaman tersedia setelah pengajuan disetujui petugas.');

        return $loan->load('user', 'book.category');
    }

    private function data(Loan $loan): array
    {
        $fmt = fn ($date) => $date ? $date->copy()->locale('id')->translatedFormat('d F Y') : '-';

        return [
            'trx'         => $loan->transaction_number,
            'status_key'  => $loan->status,
            'status'      => $loan->status_label,
            'nama'        => $loan->user->name,
            'nis'         => $loan->user->nis ?: '-',
            'kelas'       => $loan->user->class ?: '-',
            'judul'       => $loan->book->title,
            'penulis'     => $loan->book->author ?: '-',
            'kategori'    => $loan->book->category?->name ?: '-',
            'isbn'        => $loan->book->isbn ?: '-',
            'tgl_pinjam'  => $fmt($loan->pickup_date),
            'lama'        => $loan->duration_days.' hari',
            'tgl_kembali' => $fmt($loan->due_date),
            'dikembalikan'=> $loan->returned_at ? $fmt($loan->returned_at) : 'Belum dikembalikan',
            'denda'       => (int) $loan->current_fine,
            'dicetak'     => now()->locale('id')->translatedFormat('d F Y, H:i'),
        ];
    }
}
