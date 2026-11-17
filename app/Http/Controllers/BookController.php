<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function show(Book $book)
    {
        $related = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->limit(4)->get();

        return view('books.show', compact('book', 'related'));
    }

    public function borrow(Request $request, Book $book)
    {
        $data = $request->validate([
            'pickup_date'   => 'required|date|after_or_equal:today',
            'duration_days' => 'required|integer|min:1|max:30',
            'note'          => 'nullable|string|max:500',
        ]);

        if (!$book->isAvailable()) {
            return back()->withErrors(['stock' => 'Stok buku tidak tersedia.']);
        }

        $dueDate = now()->parse($data['pickup_date'])
            ->addDays($data['duration_days']);

        Loan::create([
            'user_id'       => auth()->id(),
            'book_id'       => $book->id,
            'pickup_date'   => $data['pickup_date'],
            'duration_days' => $data['duration_days'],
            'due_date'      => $dueDate,
            'note'          => $data['note'] ?? null,
            'status'        => 'pending',
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim.');
    }
}