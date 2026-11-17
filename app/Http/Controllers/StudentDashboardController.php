<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = Loan::statsFor(auth()->id());

        $activeLoans = Loan::with('book')
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->get();

        $recommendations = Book::where('available_stock', '>', 0)
            ->inRandomOrder()->limit(3)->get();

        return view('student.dashboard', compact('stats', 'activeLoans', 'recommendations'));
    }

    public function loans()
    {
        $loans = Loan::with('book')->where('user_id', auth()->id())->latest()->get();
        return view('student.loans', compact('loans'));
    }

    public function history()
    {
        $loans = Loan::with('book')->where('user_id', auth()->id())
            ->whereIn('status', ['returned', 'late'])->latest()->get();
        return view('student.history', compact('loans'));
    }

    public function profile()
    {
        return view('student.profile');
    }
}