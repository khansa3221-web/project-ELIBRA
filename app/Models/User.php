<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
        'class',
        'username',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    // Jumlah notifikasi belum dibaca; tidak boleh merusak halaman bila tabel belum dimigrasi
    public function unreadNotificationCount(): int
    {
        try {
            return $this->userNotifications()->whereNull('read_at')->count();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo && is_file(public_path($this->photo)) ? asset($this->photo) : null;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr(trim($this->name), 0, 2));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }
}