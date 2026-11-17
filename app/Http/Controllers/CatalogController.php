<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // Daftar buku + pencarian, filter kategori, urutan
    public function index(Request $request)
    {
        $query = Book::with('category')->withBorrowCount();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
        }

        if ($request->boolean('available_only')) {
            $query->where('available_stock', '>', 0);
        }

        // Setiap urutan diakhiri id agar buku dengan nilai sama (mis. 30 buku dibuat bersamaan)
        // tidak berpindah/terduplikasi/hilang antar halaman pagination.
        match ($request->input('sort', 'popular')) {
            'title'  => $query->orderBy('title')->orderBy('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'stock'  => $query->orderByDesc('available_stock')->orderBy('title')->orderBy('id'),
            default  => $query->orderByDesc('loans_count')->orderBy('title')->orderBy('id'), // popular: jumlah peminjaman
        };

        $books = $query->paginate(30)->withQueryString();

        // ?page=99 (di luar rentang) tidak lagi menampilkan daftar kosong: arahkan ke halaman terakhir
        if ($books->isEmpty() && $books->total() > 0 && $books->currentPage() > $books->lastPage()) {
            return redirect()->to($books->url($books->lastPage()));
        }

        $categories = Category::all();

        return view('catalog', compact('books', 'categories'));
    }

    // Detail satu buku (URL memakai slug, mis. /katalog/laskar-pelangi)
    public function show(Book $book)
    {
        $related = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->limit(4)->get();

        return view('books.show', compact('book', 'related'));
    }
}
