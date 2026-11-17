<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'author', 'isbn',
        'shelf', 'language', 'description',
        'total_stock', 'available_stock', 'cover_gradient',
        'publisher', 'year', 'cover_path',
    ];

    // URL cover asli; null bila belum ada (tampilan memakai tulisan "Cover Belum Tersedia").
    // Jika cover_path kosong (mis. database tertimpa), dicari foto di database/seeders/cover-map.json
    // yang judulnya SAMA PERSIS dan filenya ada, sehingga cover yang sudah diunggah tidak hilang.
    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_path && is_file(public_path($this->cover_path))) {
            return asset($this->cover_path);
        }

        static $map = null;
        if ($map === null) {
            $file = database_path('seeders/cover-map.json');
            $raw = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
            $map = [];
            foreach (is_array($raw) ? $raw : [] as $judul => $path) {
                $map[mb_strtolower(trim((string) $judul))] = $path;
            }
        }
        $path = $map[mb_strtolower(trim((string) $this->title))] ?? null;

        return $path && is_file(public_path($path)) ? asset($path) : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    // Jumlah peminjaman sebenarnya (pengajuan yang ditolak/menunggu tidak dihitung)
    public function scopeWithBorrowCount($query)
    {
        return $query->withCount(['loans as loans_count' => fn ($l) => $l->whereIn('status', ['approved', 'active', 'late', 'returned'])]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isAvailable(): bool
    {
        return $this->available_stock > 0;
    }
}