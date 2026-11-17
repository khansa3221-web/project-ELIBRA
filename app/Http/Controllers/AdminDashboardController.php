<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_books'   => Book::count(),
            'active_loans'  => Loan::active()->count(),
            'pending'       => Loan::pending()->count(),
            'fine_month'    => Loan::whereMonth('created_at', now()->month)->sum('fine'),
        ];

        $pendingLoans = Loan::with(['user', 'book'])->pending()->latest()->limit(3)->get();
        $recentActivity = Loan::with(['user', 'book'])->latest()->limit(3)->get();

        $popularBooks = Book::withCount('loans')
            ->orderByDesc('loans_count')
            ->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'pendingLoans', 'recentActivity', 'popularBooks'));
    }

    public function approve(Loan $loan)
    {
        if ($loan->book->available_stock < 1) {
            return back()->withErrors(['stock' => 'Stok habis.']);
        }

        $loan->update([
            'status'      => 'active',
            'approved_at' => now(),
        ]);

        $loan->book->decrement('available_stock');

        return back()->with('success', 'Pengajuan disetujui.');
    }

    public function reject(Request $request, Loan $loan)
    {
        $loan->update([
            'status' => 'rejected',
            'note'   => $request->input('reason', $loan->note),
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    public function books()      { return view('admin.books', ['books' => Book::paginate(10)]); }
    public function submissions(){ return view('admin.submissions', ['loans' => Loan::pending()->with(['user','book'])->paginate(10)]); }
    public function members()    { return view('admin.members', ['members' => User::where('role','siswa')->paginate(10)]); }
    public function reports()    { return view('admin.reports'); }
}