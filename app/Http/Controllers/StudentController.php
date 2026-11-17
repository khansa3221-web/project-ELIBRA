<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Services\LoanService;
use DomainException;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private LoanService $loanService)
    {
    }

    public function index()
    {
        $this->loanService->syncIfStale();
        $userId = auth()->id();

        $stats = Loan::statsFor(auth()->id());

        $activeLoans = Loan::with('book')
            ->where('user_id', $userId)
            ->active()
            ->latest()
            ->get();

        $recommendations = Book::with('category')->where('available_stock', '>', 0)
            ->inRandomOrder()->limit(3)->get();

        try {
            $notifs = auth()->user()->userNotifications()->whereNull('read_at')->latest()->limit(3)->get();
        } catch (\Throwable $e) {   // tabel notifikasi belum dimigrasi: jangan jatuhkan dashboard
            report($e);
            $notifs = collect();
        }

        return view('student.dashboard', compact('stats', 'activeLoans', 'recommendations', 'notifs'));
    }

    // Riwayat: buku yang sudah dikembalikan atau pengajuan yang ditolak (hanya milik siswa ini)
    public function history()
    {
        $userId = auth()->id();

        $loans = Loan::with('book')
            ->where('user_id', $userId)
            ->whereIn('status', ['returned', 'rejected'])
            ->latest('updated_at')
            ->paginate(10);

        $summary = [
            'returned' => Loan::where('user_id', $userId)->where('status', 'returned')->count(),
            'late'     => Loan::where('user_id', $userId)->where('status', 'returned')->where('fine', '>', 0)->count(),
            'fine'     => (int) Loan::where('user_id', $userId)->where('status', 'returned')->sum('fine'),
        ];

        return view('student.history', compact('loans', 'summary'));
    }

    public function notifications()
    {
        $this->loanService->syncIfStale();

        $items = auth()->user()->userNotifications()->latest()->paginate(15);

        return view('student.notifications', compact('items'));
    }

    public function readNotifications()
    {
        auth()->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function loans()
    {
        $this->loanService->syncIfStale();
        $loans = Loan::with('book')->where('user_id', auth()->id())->latest()->get();

        return view('student.loans', compact('loans'));
    }

    // Siswa mengajukan peminjaman buku
    public function borrow(Request $request, Book $book)
    {
        $data = $request->validate([
            'pickup_date'   => 'required|date|after_or_equal:today',
            'duration_days' => 'required|integer|min:1|max:30',
            'note'          => 'nullable|string|max:500',
        ]);

        try {
            $this->loanService->request($request->user(), $book, $data);
        } catch (DomainException $e) {
            return back()->withErrors(['borrow' => $e->getMessage()])->withInput();
        }

        return redirect()->route('student.loans')
            ->with('success', 'Pengajuan peminjaman "'.$book->title.'" berhasil dikirim. Tunggu persetujuan petugas.');
    }
}
