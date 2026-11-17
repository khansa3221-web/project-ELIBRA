<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $history = $request->input('tab') === 'riwayat';

        $loans = Loan::with(['user', 'book'])
            ->when($history,
                fn ($q) => $q->whereIn('status', ['returned', 'rejected'])->latest('updated_at'),
                fn ($q) => $q->whereIn('status', ['approved', 'active', 'late'])->orderBy('due_date'))
            ->paginate(15)->withQueryString();

        return view('admin.loans.index', compact('loans', 'history'));
    }

    public function returnBook(Loan $loan)
    {
        if (! in_array($loan->status, ['approved', 'active', 'late'])) {
            return back()->with('error', 'Buku ini tidak sedang dipinjam.');
        }

        $daysLate = ($loan->due_date && today()->gt($loan->due_date))
            ? (int) $loan->due_date->copy()->startOfDay()->diffInDays(today())
            : 0;
        $fine = $daysLate * (int) config('app.fine_per_day', 0);

        $loan->update([
            'status'      => 'returned',
            'returned_at' => now(),
            'fine'        => $fine,
        ]);

        $book = $loan->book;
        if ($book->available_stock < $book->total_stock) {
            $book->increment('available_stock');
        }

        $msg = 'Buku "'.$book->title.'" telah dikembalikan.';
        if ($fine > 0) {
            $msg .= ' Denda keterlambatan '.$daysLate.' hari: Rp '.number_format($fine, 0, ',', '.').'.';
        }

        return back()->with('success', $msg);
    }
}
