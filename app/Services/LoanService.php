<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Models\UserNotification;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh perubahan status peminjaman + stok lewat sini, selalu di dalam
 * database transaction dengan lockForUpdate (diabaikan SQLite, tetapi transaksi
 * SQLite sudah menserialkan penulisan) dan pengecekan status/stok ulang.
 */
class LoanService
{
    public function request(User $user, Book $book, array $data): Loan
    {
        return DB::transaction(function () use ($user, $book, $data) {
            $book = Book::lockForUpdate()->findOrFail($book->id);

            if ($book->available_stock < 1) {
                throw new DomainException('Maaf, stok buku ini sedang habis.');
            }

            $sudahAda = Loan::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->whereIn('status', Loan::OPEN_STATUSES)
                ->exists();
            if ($sudahAda) {
                throw new DomainException('Kamu sudah mengajukan atau sedang meminjam buku "'.$book->title.'".');
            }

            $pickup = Carbon::parse($data['pickup_date'])->startOfDay();
            $days = (int) $data['duration_days'];

            $loan = Loan::create([
                'user_id'       => $user->id,
                'book_id'       => $book->id,
                'pickup_date'   => $pickup,
                'duration_days' => $days,
                'due_date'      => $pickup->copy()->addDays($days),
                'note'          => $data['note'] ?? null,
                'status'        => 'pending',
            ]);

            $this->notify($user->id, 'submitted', 'Pengajuan terkirim',
                'Pengajuan peminjaman "'.$book->title.'" menunggu persetujuan petugas.', $loan->id);

            return $loan;
        });
    }

    public function approve(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $loan = Loan::with('book')->lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw new DomainException('Pengajuan ini sudah diproses sebelumnya (status: '.$loan->status_label.').');
            }

            // Pengurangan stok atomik: gagal bila stok sudah 0
            $berhasil = Book::whereKey($loan->book_id)->where('available_stock', '>', 0)->decrement('available_stock');
            if (! $berhasil) {
                throw new DomainException('Stok buku "'.$loan->book->title.'" habis, pengajuan tidak bisa disetujui.');
            }

            $loan->update(['status' => 'active', 'approved_at' => now(), 'reject_reason' => null]);

            $this->notify($loan->user_id, 'approved', 'Pengajuan disetujui',
                'Buku "'.$loan->book->title.'" disetujui. Ambil pada '.$loan->pickup_date->translatedFormat('d M Y')
                .', jatuh tempo '.$loan->due_date->translatedFormat('d M Y').'.', $loan->id);

            return $loan;
        });
    }

    public function reject(Loan $loan, ?string $reason = null): Loan
    {
        return DB::transaction(function () use ($loan, $reason) {
            $loan = Loan::with('book')->lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw new DomainException('Pengajuan ini sudah diproses sebelumnya (status: '.$loan->status_label.').');
            }

            $reason = $reason !== null ? trim($reason) : null;
            $loan->update(['status' => 'rejected', 'reject_reason' => $reason ?: null]);

            $this->notify($loan->user_id, 'rejected', 'Pengajuan ditolak',
                'Pengajuan "'.$loan->book->title.'" ditolak.'.($reason ? ' Alasan: '.$reason : ''), $loan->id);

            return $loan;
        });
    }

    public function returnBook(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $loan = Loan::with('book')->lockForUpdate()->findOrFail($loan->id);

            if (! in_array($loan->status, Loan::HOLDING_STATUSES, true)) {
                throw new DomainException('Peminjaman ini tidak sedang dipinjam, jadi tidak bisa dikembalikan.');
            }

            $loan->returned_at = now();
            $lateDays = $loan->late_days;
            $fine = $lateDays * Loan::FINE_PER_DAY;

            $loan->status = 'returned';
            $loan->fine = $fine;
            $loan->save();

            // Stok kembali, tidak pernah melebihi total stok
            Book::whereKey($loan->book_id)->whereColumn('available_stock', '<', 'total_stock')->increment('available_stock');

            $msg = 'Buku "'.$loan->book->title.'" sudah dikembalikan. Terima kasih!';
            if ($fine > 0) {
                $msg .= ' Terlambat '.$lateDays.' hari, denda Rp'.number_format($fine, 0, ',', '.').'.';
            }
            $this->notify($loan->user_id, 'returned', 'Buku dikembalikan', $msg, $loan->id);

            return $loan;
        });
    }

    /** Tandai terlambat + kirim pengingat tenggat. Aman dipanggil berulang (tidak membuat notifikasi ganda). */
    public function markOverdue(): int
    {
        $jumlah = 0;
        $kandidat = Loan::with('book')
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('due_date', '<', today())
            ->get();

        foreach ($kandidat as $item) {
            DB::transaction(function () use ($item, &$jumlah) {
                $loan = Loan::lockForUpdate()->find($item->id);
                if (! $loan || ! in_array($loan->status, ['approved', 'active'], true)) {
                    return;
                }
                $loan->update(['status' => 'late']);
                $this->notify($loan->user_id, 'overdue', 'Peminjaman terlambat',
                    'Buku "'.$item->book->title.'" sudah lewat jatuh tempo ('.$loan->due_date->translatedFormat('d M Y')
                    .'). Denda Rp'.number_format(Loan::FINE_PER_DAY, 0, ',', '.').' per hari. Segera kembalikan.', $loan->id);
                $jumlah++;
            });
        }

        return $jumlah;
    }

    public function remind(): array
    {
        $terlambat = $this->markOverdue();
        $diingatkan = 0;
        $hariIni = today();

        $akanJatuhTempo = Loan::with('book')
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('due_date', '>=', $hariIni)
            ->whereDate('due_date', '<=', $hariIni->copy()->addDays(2))
            ->get();

        foreach ($akanJatuhTempo as $loan) {
            $sudah = UserNotification::where('loan_id', $loan->id)
                ->where('type', 'due_soon')
                ->whereDate('created_at', $hariIni)
                ->exists();
            if ($sudah) {
                continue;
            }
            $sisa = (int) $hariIni->diffInDays($loan->due_date->copy()->startOfDay(), false);
            $kapan = $sisa <= 0 ? 'hari ini' : $sisa.' hari lagi';
            $this->notify($loan->user_id, 'due_soon', 'Pengingat jatuh tempo',
                'Buku "'.$loan->book->title.'" jatuh tempo '.$kapan.' ('.$loan->due_date->translatedFormat('d M Y').').', $loan->id);
            $diingatkan++;
        }

        return ['terlambat' => $terlambat, 'diingatkan' => $diingatkan];
    }

    /** Dipanggil dari halaman dashboard agar pengingat jalan tanpa scheduler (maks. sekali per 30 menit). */
    public function syncIfStale(int $minutes = 30): void
    {
        try {
            if (Cache::add('elibra.loan-sync', now()->toDateTimeString(), now()->addMinutes($minutes))) {
                $this->remind();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function notify(int $userId, string $type, string $title, string $message, ?int $loanId = null): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'loan_id' => $loanId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
        ]);
    }
}
