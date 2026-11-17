# Panduan Deployment E-LIBRA 2.0 (untuk pemula)

> Belum ada deployment yang dilakukan. Panduan ini hanya langkah yang bisa kamu ikuti sendiri.

## 1. Kebutuhan server
- PHP **8.2 atau lebih baru** (disarankan 8.3), ekstensi: `pdo_sqlite` (atau `pdo_mysql`), `mbstring`, `openssl`, `ctype`, `fileinfo`, `tokenizer`, `xml`, `curl`.
- Apache dengan `mod_rewrite` (atau Nginx), akses **SSH/Terminal** sangat membantu.
- Composer (di komputer/hosting). Disk ≥ 200 MB. HTTPS (SSL gratis Let's Encrypt).
- Cron job (untuk pengingat), atau cukup andalkan pengingat otomatis saat dashboard dibuka.

## 2. Persiapan di komputermu
1. Jalankan `BACKUP-DATABASE.bat` dulu.
2. Pastikan folder `vendor` ada (`composer install --no-dev --optimize-autoloader`).
3. **Jangan** unggah: `.env`, `.git`, `backup/`, `storage/logs/*`, dan `database.sqlite` yang berisi data siswa asli (kecuali memang ingin memindahkan data — unggah lewat jalur privat, bukan repository publik).

## 3. Unggah & atur
1. Unggah isi project ke server (di luar `public_html` jika bisa, mis. `~/elibra`).
2. **Arahkan document root domain ke folder `public`** (cara terbaik; panel hosting → Domains → Document Root). Bila tidak bisa, `.htaccess` di root project sudah meneruskan ke `public/` dan memblokir `.env`.
3. Salin `.env.production.example` menjadi `.env`, isi `APP_URL` dengan domainmu. Pastikan `APP_ENV=production` dan **`APP_DEBUG=false`**.
4. Database:
   - **SQLite (paling mudah):** buat file kosong `database/database.sqlite`.
   - **MySQL:** buat database di panel hosting, isi `DB_*` di `.env`.
5. Perintah (SSH/Terminal panel):
```
php artisan key:generate --force
php artisan migrate --force
php artisan elibra:admin          # buat akun admin (kata sandi kuat!)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
   **Jangan** menjalankan `db:seed` di production — seeder berisi akun contoh berkata sandi `password`.
6. Izin folder: `storage` dan `bootstrap/cache` harus bisa ditulis (chmod 775); untuk SQLite, `database/` dan file `database.sqlite` juga.

## 4. Pengingat jatuh tempo (scheduler)
Tambahkan cron tiap menit (cPanel → Cron Jobs):
```
* * * * * cd /home/USER/elibra && php artisan schedule:run >> /dev/null 2>&1
```
Tanpa cron pun pengingat tetap jalan saat siswa/admin membuka dashboard (maks. tiap 30 menit).

## 5. Setelah online — periksa
- `https://domain/login` tampil, `https://domain/.env` menghasilkan **403/404** (bukan isi file).
- Login admin, ganti kata sandi, tambah satu buku, cetak QR dari alamat domain, uji pinjam-setujui-kembali.
- Matikan `APP_DEBUG` (harus `false`) — halaman error tidak boleh menampilkan detail.

## 6. Backup & pembaruan
- Rutin salin `database/database.sqlite` (atau ekspor MySQL) — mingguan minimal.
- Update kode: unggah file baru → `php artisan migrate --force` → `php artisan config:cache route:cache view:cache`.
