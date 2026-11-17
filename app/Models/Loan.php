<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    // Status yang masih "berjalan" (pengajuan atau buku belum kembali)
    public const OPEN_STATUSES = ['pending', 'approved', 'active', 'late'];
    // Status ketika buku sedang berada di tangan siswa (stok sudah berkurang)
    public const HOLDING_STATUSES = ['approved', 'active', 'late'];
    // Denda per hari keterlambatan (Rp). Ubah angka ini bila aturan sekolah berbeda.
    public const FINE_PER_DAY = 500;

    protected $fillable = [
        'user_id', 'book_id', 'pickup_date', 'duration_days',
        'due_date', 'note', 'reject_reason', 'status', 'approved_at', 'returned_at', 'fine',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'due_date'    => 'date',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Statistik dashboard siswa — satu sumber data untuk semua controller & view.
    // Hanya menghitung milik $userId. Kunci: total, active, returned, pending, late, due_soon, fine.
    public static function statsFor(int $userId): array
    {
        $own = fn () => static::where('user_id', $userId);

        return [
            'total'    => $own()->count(),
            'active'   => $own()->active()->count(),
            'returned' => $own()->where('status', 'returned')->count(),
            'pending'  => $own()->pending()->count(),
            'late'     => $own()->active()->whereDate('due_date', '<', today())->count(),
            'due_soon' => $own()->active()->whereDate('due_date', '>=', today())
                                ->whereDate('due_date', '<=', today()->addDays(2))->count(),
            'fine'     => (int) $own()->sum('fine'),
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::HOLDING_STATUSES);
    }

    // Tandai peminjaman yang lewat jatuh tempo menjadi 'late' (dan kirim notifikasi)
    public static function markOverdue(): int
    {
        return app(\App\Services\LoanService::class)->markOverdue();
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, self::HOLDING_STATUSES, true)
            && $this->due_date
            && $this->due_date->copy()->startOfDay()->lt(now()->startOfDay());
    }

    // Jumlah hari terlambat (sampai hari ini, atau sampai tanggal dikembalikan)
    public function getLateDaysAttribute(): int
    {
        if (! $this->due_date || in_array($this->status, ['pending', 'rejected'], true)) {
            return 0;
        }
        $end = ($this->returned_at ?? now())->copy()->startOfDay();

        return max(0, (int) $this->due_date->copy()->startOfDay()->diffInDays($end, false));
    }

    // Denda: yang tercatat bila sudah dikembalikan, atau perkiraan berjalan bila belum
    public function getCurrentFineAttribute(): int
    {
        return $this->status === 'returned'
            ? (int) $this->fine
            : $this->late_days * self::FINE_PER_DAY;
    }

    // Status yang boleh dibuatkan Bukti Peminjaman (sudah disetujui petugas)
    public const RECEIPT_STATUSES = ['approved', 'active', 'late', 'returned'];

    // Nomor transaksi unik untuk bukti peminjaman, mis. TRX-20261108-00042
    public function getTransactionNumberAttribute(): string
    {
        return 'TRX-'.($this->created_at ?? now())->format('Ymd').'-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getHasReceiptAttribute(): bool
    {
        return in_array($this->status, self::RECEIPT_STATUSES, true);
    }

    // Teks status yang mudah dibaca pengguna
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'active'   => 'Dipinjam',
            'late'     => 'Terlambat',
            'returned' => 'Dikembalikan',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status),
        };
    }
}
