<?php

namespace App\Http\Controllers;

use App\Models\Book;

class LandingController extends Controller
{
    public function index()
    {
        $featuredBooks = Book::limit(6)->get();
        $stats = [
            'titles'    => Book::count(),
            'members'   => \App\Models\User::where('role', 'siswa')->count(),
        ];

        $stats['loans'] = \App\Models\Loan::whereIn('status', ['approved', 'active', 'late', 'returned'])->count();

        $categories = \App\Models\Category::withCount('books')->orderBy('name')->get();

        // Terpopuler = jumlah peminjaman sungguhan
        $popularBooks = Book::with('category')
            ->withCount(['loans as borrow_count' => fn ($q) => $q->whereIn('status', ['approved', 'active', 'late', 'returned'])])
            ->orderByDesc('borrow_count')->orderBy('title')->limit(4)->get();

        return view('landing', compact('featuredBooks', 'stats', 'categories', 'popularBooks'));
    }
}