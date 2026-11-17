<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJudul = Book::count();
        $peminjamanAktif = Loan::where('status', 'dipinjam')->count();
        $menungguVerifikasi = Loan::where('status', 'pending')->count();
        $dendaBulanIni = Loan::whereMonth('created_at', now()->month)->sum('denda');

        $pengajuanMenunggu = Loan::with(['user', 'book'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $bukuTerpopuler = Book::withCount('loans')
            ->orderByDesc('loans_count')
            ->limit(5)
            ->get();

        $aktivitasTerbaru = Loan::with(['user', 'book'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalJudul', 'peminjamanAktif', 'menungguVerifikasi', 'dendaBulanIni',
            'pengajuanMenunggu', 'bukuTerpopuler', 'aktivitasTerbaru'
        ));
    }
}
