<?php

namespace App\Http\Controllers;

use App\Models\Book;

class HomeController extends Controller
{
    public function index()
    {
        $totalBooks = Book::count();
        $totalStock = Book::sum('total_stock');
        $books = Book::latest()->take(10)->get();

        return view('catalog.index', compact('totalBooks', 'totalStock', 'books'));
    }
}