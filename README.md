# E-LIBRA 2.0 — Perpustakaan SMK Negeri 5 Surakarta

Aplikasi web perpustakaan sekolah. **Laravel 12 · PHP 8.2+ · SQLite (bisa MySQL)**.

> Status fitur dan hasil uji yang jujur ada di [`LAPORAN-PENGUJIAN.md`](LAPORAN-PENGUJIAN.md).
> Fitur yang belum dibuat ditandai di sana — jangan mengandalkan fitur yang tidak tercantum "Ada".

## Fitur yang sudah ada
- Login / daftar siswa, role **admin** dan **siswa** (role guru belum ada).
- Katalog buku: pencarian, filter kategori & ketersediaan, urutan (terpopuler dari data peminjaman nyata, judul, terbaru).
- Peminjaman: pengajuan siswa → persetujuan/penolakan admin → pengembalian, stok otomatis, denda keterlambatan.
- Riwayat peminjaman siswa, notifikasi status, pengingat jatuh tempo.
- QR Code unik tiap buku (lihat, unduh PNG/SVG, cetak, lembar label).
- Admin: kelola buku (tambah/ubah/hapus/impor CSV), pengajuan, peminjaman, anggota, dashboard statistik.

## Menjalankan di komputer sendiri (Windows)
1. Pasang **XAMPP** (apachefriends.org), cukup sekali.
2. Ekstrak ZIP sampai selesai, pastikan ada folder `vendor` (jika tidak: `composer install`).
3. Klik dua kali **`JALANKAN.bat`** (otomatis menjalankan migrasi database lalu membuka http://localhost:8000).
4. Login admin: `admin@elibra.test`. **Kata sandi diatur lewat `GANTI-PASSWORD-ADMIN.bat`** (jangan memakai kata sandi bawaan lama).

Detail pemakaian: [`PANDUAN-PENGGUNA.md`](PANDUAN-PENGGUNA.md). Pasang online: [`PANDUAN-DEPLOYMENT.md`](PANDUAN-DEPLOYMENT.md).

## Perintah penting
| Tujuan | Perintah |
|---|---|
| Migrasi (aman, hanya menambah) | `php artisan migrate` |
| Buat / ganti admin | `php artisan elibra:admin` (atau `GANTI-PASSWORD-ADMIN.bat`) |
| Pengingat jatuh tempo manual | `php artisan elibra:reminders` |
| Jalankan tes otomatis | `php artisan test` (memakai database memori, tidak menyentuh data asli) |
| Cadangkan database | `BACKUP-DATABASE.bat` |

## Keamanan singkat
Kata sandi di-hash, CSRF aktif, role dicek di middleware, login dibatasi 5 percobaan/menit, header keamanan terpasang, `.env` tidak ikut ZIP/repository.
