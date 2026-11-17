<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'tanggal_ambil' => ['required', 'date', 'after_or_equal:today'],
            'lama_hari' => ['required', 'integer', 'in:3,7,14'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $book->isAvailable()) {
            return back()->with('error', 'Maaf, stok buku ini sedang habis.');
        }

        $sudahMengajukan = $book->loans()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'dipinjam'])
            ->exists();

        if ($sudahMengajukan) {
            return back()->with('error', 'Anda sudah memiliki pengajuan/peminjaman aktif untuk buku ini.');
        }

        Loan::create([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
            'tanggal_ambil' => $data['tanggal_ambil'],
            'lama_hari' => $data['lama_hari'],
            'catatan' => $data['catatan'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengajuan peminjaman "'.$book->title.'" berhasil dikirim, menunggu verifikasi petugas.');
    }
}
