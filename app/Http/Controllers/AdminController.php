<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use DomainException;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private LoanService $loanService)
    {
    }

    public function index()
    {
        $this->loanService->syncIfStale();

        $stats = [
            'total_books'  => Book::count(),
            'active_loans' => Loan::active()->count(),
            'pending'      => Loan::pending()->count(),
        ];

        $pendingLoans   = Loan::with(['user', 'book'])->pending()->latest()->limit(3)->get();
        $recentActivity = Loan::with(['user', 'book'])->latest()->limit(3)->get();

        $popularBooks = Book::withBorrowCount()
            ->orderByDesc('loans_count')->orderBy('title')
            ->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'pendingLoans', 'recentActivity', 'popularBooks'));
    }

    public function submissions()
    {
        return view('admin.submissions', [
            'loans' => Loan::pending()->with(['user', 'book'])->paginate(10),
        ]);
    }

    public function approve(Loan $loan)
    {
        try {
            $this->loanService->approve($loan);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan disetujui. Stok buku berkurang satu.');
    }

    public function reject(Request $request, Loan $loan)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);

        try {
            $this->loanService->reject($loan, $data['reason'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan ditolak.');
    }

    // Semua peminjaman + filter status / pencarian
    public function loans(Request $request)
    {
        $this->loanService->syncIfStale();

        $filter = $request->input('status', 'berjalan');
        $query = Loan::with(['user', 'book'])->latest();

        match ($filter) {
            'berjalan'  => $query->whereIn('status', Loan::HOLDING_STATUSES),
            'terlambat' => $query->where('status', 'late'),
            'selesai'   => $query->where('status', 'returned'),
            'ditolak'   => $query->where('status', 'rejected'),
            default     => null, // 'semua'
        };

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"))
                  ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$q}%"));
            });
        }

        $counts = [
            'berjalan'  => Loan::whereIn('status', Loan::HOLDING_STATUSES)->count(),
            'terlambat' => Loan::where('status', 'late')->count(),
        ];

        return view('admin.loans', [
            'loans'  => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'counts' => $counts,
        ]);
    }

    public function returnBook(Loan $loan)
    {
        try {
            $loan = $this->loanService->returnBook($loan);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = 'Buku dikembalikan. Stok bertambah satu.';
        if ($loan->fine > 0) {
            $msg .= ' Denda keterlambatan: Rp'.number_format($loan->fine, 0, ',', '.').'.';
        }

        return back()->with('success', $msg);
    }

    // Laporan peminjaman (semua angka dihitung dari database)
    public function reports()
    {
        $this->loanService->syncIfStale();
        $counted = array_merge(Loan::HOLDING_STATUSES, ['returned']);

        $stats = [
            'total_books'   => Book::count(),
            'total_members' => User::where('role', 'siswa')->count(),
            'active_loans'  => Loan::whereIn('status', Loan::HOLDING_STATUSES)->count(),
            'late_loans'    => Loan::where('status', 'late')->count(),
            'pending'       => Loan::where('status', 'pending')->count(),
            'returned'      => Loan::where('status', 'returned')->count(),
            'fine'          => (int) Loan::sum('fine'),
        ];

        // 6 bulan terakhir
        $months  = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $byMonth = Loan::whereIn('status', $counted)
            ->where('created_at', '>=', $months->first())
            ->get(['created_at'])
            ->groupBy(fn ($l) => $l->created_at->format('Y-m'));
        $monthly = $months->map(fn ($m) => [
            'label' => $m->translatedFormat('M y'),
            'total' => $byMonth->get($m->format('Y-m'), collect())->count(),
        ]);

        $topBooks = Book::withCount(['loans as borrow_count' => fn ($q) => $q->whereIn('status', $counted)])
            ->orderByDesc('borrow_count')->orderBy('title')->limit(5)->get()
            ->filter(fn ($b) => $b->borrow_count > 0)->values();

        $lateLoans = Loan::with(['user', 'book'])->where('status', 'late')
            ->orderBy('due_date')->limit(10)->get();

        return view('admin.reports', compact('stats', 'monthly', 'topBooks', 'lateLoans'));
    }

    public function exportLoans()
    {
        $safe = fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'".$v : (string) $v;

        return response()->streamDownload(function () use ($safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Anggota', 'NIS', 'Kelas', 'Judul Buku', 'Status', 'Tgl Ambil', 'Jatuh Tempo', 'Dikembalikan']);
            Loan::with(['user', 'book'])->orderBy('id')->chunk(200, function ($loans) use ($out, $safe) {
                foreach ($loans as $l) {
                    fputcsv($out, [
                        $safe($l->user->name), $safe($l->user->nis), $safe($l->user->class), $safe($l->book->title),
                        $l->status_label, $l->pickup_date?->format('Y-m-d'), $l->due_date?->format('Y-m-d'), $l->returned_at?->format('Y-m-d'),
                    ]);
                }
            });
            fclose($out);
        }, 'laporan-peminjaman-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function members()
    {
        return view('admin.members', ['members' => User::where('role', 'siswa')->paginate(10)]);
    }
}
