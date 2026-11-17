# Laporan Pengujian E-LIBRA 2.0 (Tahap 5)

Tanggal: 3 November 2026. Aturan laporan ini: **tidak ada yang ditulis "berhasil" kalau belum benar-benar dijalankan.**

## 1. Lingkungan pengujian
Sandbox pengembang **tidak punya PHP, Composer, `vendor/`, maupun akses jaringan**. Akibatnya:
- `php artisan test`, `php artisan route:list`, `php artisan migrate:status` **TIDAK DIJALANKAN**.
- Aplikasi **belum pernah dijalankan di browser** oleh pengembang ini (tampilan, responsif HP/tablet belum diperiksa visual).

## 2. Yang benar-benar dijalankan (hasil nyata)
| Pemeriksaan | Hasil |
|---|---|
| Encoder QR (`public/js/qr.js`) diuji dengan decoder OpenCV, 300 teks acak versi 1–10 | 300/300 terbaca persis |
| QR tautan nyata ke-21 buku di database | 21/21 terbaca, URL cocok |
| Simulasi SQL pada salinan database: migrasi `reject_reason`, `user_notifications`, `qr_token` + backfill + indeks unik | 21 token unik; duplikat ditolak; token tetap setelah judul diubah |
| Simulasi SQL alur stok: setujui dua kali, terlambat, kembalikan dua kali | Setujui ke-2 ditolak; stok 5→4→5; denda 7 hari = 3.500; retur ke-2 ditolak; stok ≤ total |
| Pemeriksaan statis: keseimbangan kurung PHP, direktif Blade, nama route di view | Lulus untuk semua file yang diubah |

Catatan: simulasi SQL **bukan** pengganti uji aplikasi Laravel; ia hanya memastikan skema dan logika SQL-nya masuk akal.

## 3. Tes otomatis yang sudah ditulis (BELUM DIJALANKAN)
`tests/Feature/LoginAccessTest.php` (login per role, NIS, hash, pembatasan login, nama kembar, hak akses role, isolasi data siswa, header keamanan) dan `tests/Feature/LoanFlowTest.php` (alur ajukan→setujui→kembalikan, tidak bisa diproses dua kali, pengajuan ganda, stok habis, denda, batas stok, penandaan terlambat & pengingat tanpa duplikat, alur HTTP admin, siswa dilarang aksi admin, popularitas nyata, QR unik/permanen).
**Jalankan:** `php artisan test`. Tes memakai database memori (sudah dikunci di `phpunit.xml`), jadi data asli aman. Jika ada tes gagal, itu temuan bug nyata atau kesalahan tes — kirim pesan errornya.

## 4. Status fitur
| Fitur | Ada di kode? | Status uji |
|---|---|---|
| Login & registrasi (admin, siswa) | Ya | Tes ditulis, belum dijalankan |
| Role **guru** | **Tidak ada** | — |
| Dashboard admin & siswa | Ya (statistik dari data nyata) | Statis saja |
| Katalog, detail, pencarian, filter, urutan | Ya | Statis saja |
| Peminjaman, persetujuan/penolakan, pengembalian, stok, denda | Ya | SQL disimulasikan; tes ditulis |
| Riwayat peminjaman, notifikasi, pengingat | Ya | Tes ditulis |
| QR Code | Ya | Encoder & tautan dipindai OK; halaman belum diuji di browser |
| CRUD & impor buku | Ya (lama) | Belum diuji ulang |
| Rating & ulasan | **Belum dibuat** | — |
| Reading Challenge | **Belum dibuat** | — |
| Rekomendasi buku | **Belum dibuat** (dashboard hanya memilih acak buku tersedia) | — |
| E-book Corner | **Belum dibuat** | — |
| Asisten informasi | **Belum dibuat** | — |
| Laporan/ekspor peminjaman | **Belum dibuat** (hanya statistik dashboard) | — |

## 5. Bug/risiko yang ditemukan dan diperbaiki di Tahap 5
1. **`php artisan test` bisa menghapus database asli** (phpunit.xml tidak mengunci database uji) → dikunci ke SQLite memori.
2. Login tanpa pembatasan percobaan → dibatasi 5 gagal/menit per akun+IP.
3. Login memakai nama bisa memilih akun yang salah bila nama kembar → hanya diterima jika nama unik.
4. Tidak ada header keamanan → ditambah `SecurityHeaders` (nosniff, frame, referrer, permissions, HSTS di HTTPS).
5. Tidak ada template `.env` production (`APP_DEBUG=false`) → `.env.production.example`.
6. Hosting yang document root-nya folder project bisa membuka `.env` → `.htaccess` root memblokir & meneruskan ke `public/`.
7. Klik ganda pada tombol form bisa mengirim dua kali → `polish.js` (loading state, tombol dinonaktifkan).
8. Tidak ada skrip backup → `BACKUP-DATABASE.bat`; `*.sqlite` dan `/backup` masuk `.gitignore`.
9. README lama mencantumkan kata sandi admin bawaan → dihapus dari README.

## 6. Risiko yang masih ada (belum diperbaiki)
- `DatabaseSeeder` membuat akun contoh berkata sandi `password` → **jangan `db:seed` di production**.
- Role guru, rating, challenge, rekomendasi, e-book, asisten belum ada.
- Tidak ada fitur "lupa kata sandi" (view `auth/forgot` yatim belum terhubung).
- File/view/controller yatim lama belum dibersihkan (tidak berbahaya, hanya membingungkan).
- Optimasi gambar/cover: cover berupa gradient CSS, tidak ada gambar berat. Query utama sudah memakai eager loading; belum diprofilkan.
- Baris `app/Http/Kernel.php` dan `RoleMiddleware` tidak terpakai di Laravel 12.
