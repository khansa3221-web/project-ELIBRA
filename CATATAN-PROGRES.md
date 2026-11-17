# CATATAN PROGRES E-LIBRA 2.0

## Tahap 1–3 — selesai ditulis (belum dijalankan di Laravel oleh pengembang)
Audit; UI/UX (`public/css/polish.css`); alur peminjaman aman (`app/Services/LoanService.php`: transaction, stok atomik, tidak bisa diproses ulang, pengembalian, denda); riwayat siswa; notifikasi & pengingat (`elibra:reminders`).

## Tahap 4
| # | Fitur | Status |
|---|-------|--------|
| 1 | QR Code buku | Selesai ditulis; encoder & tautan dipindai OK |
| 2 | Rating & ulasan | **Belum** |
| 3 | Reading Challenge | **Belum** |
| 4 | Rekomendasi buku | **Belum** |
| 5 | E-book Corner | **Belum** |
| 6 | Asisten informasi | **Belum** |

## Tahap 5 (finalisasi) — dikerjakan sebagian sesuai kenyataan
Dikerjakan: keamanan (throttle login, nama unik, header, `.env.production.example`, `.htaccess`, DB uji terkunci), tes otomatis, loading state, backup, dokumentasi (README, PANDUAN-PENGGUNA, PANDUAN-DEPLOYMENT, LAPORAN-PENGUJIAN).
**Tidak bisa dilakukan:** menjalankan `php artisan test/route:list/migrate:status` (PHP tidak ada di sandbox), uji browser/responsif.

### File Tahap 5
- Baru: `app/Http/Middleware/SecurityHeaders.php`, `.env.production.example`, `.htaccess`, `BACKUP-DATABASE.bat`, `public/js/polish.js`, `tests/Feature/{Concerns,LoginAccessTest,LoanFlowTest}.php`, `README.md` (ditulis ulang), `PANDUAN-*.md`, `LAPORAN-PENGUJIAN.md`
- Diubah: `AuthController.php`, `bootstrap/app.php`, `phpunit.xml`, `layouts/app|dashboard.blade.php`, `public/css/polish.css`, `.gitignore`

## Pekerjaan berikutnya (urutan disarankan)
1. Jalankan `php artisan migrate && php artisan test` di komputer dan kirim hasilnya.
2. Selesaikan Tahap 4 fitur 2–6 (rating → challenge → rekomendasi → e-book → asisten) sebelum deployment.
3. Putuskan apakah role guru diperlukan, lalu uji ulang dan deploy sesuai `PANDUAN-DEPLOYMENT.md`.

## Perbaikan Register & animasi (4 Nov 2026)
- Route/controller/view register diperiksa statis: tidak ditemukan cacat; penyebab error asli BELUM terbukti (PHP tidak ada di sandbox). Butuh pesan error asli / `storage/logs/laravel.log`.
- AuthController::register diperketat (NIS wajib & unik, email dinormalisasi, pesan Indonesia, role selalu siswa, redirect ke login + pesan sukses); throttle 10/menit; login menampilkan pesan sukses; `tests/Feature/RegisterTest.php` ditulis (BELUM dijalankan).
- Animasi: infrastruktur sudah ada (animations.css/js, floaters, count-up, reduced-motion); hanya ditambah selector heading panel di animations.js.

## Perbaikan login akun baru (4 Nov 2026)
- DB di ZIP (database.sqlite) belum berisi tabel/kolom Tahap 3–4 (`user_notifications`, `qr_token`, dll.): jika `php artisan migrate` tidak dijalankan, dashboard siswa memanggil tabel yang tidak ada -> error SQL setelah login. Ini dugaan terkuat, BELUM dibuktikan (tidak ada laravel.log di ZIP, PHP tidak ada di sandbox).
- Ditambah: `PERBAIKI-LOGIN.bat` (migrate + optimize:clear + diagnosa), `php artisan elibra:diagnose` (uji alur daftar->login dalam transaksi yang dibatalkan), pengaman notifikasi (dashboard tidak jatuh bila tabel belum ada), migrasi indeks unik NIS, role tak dikenal tidak lagi membuat redirect berputar, tes login akun baru.

## Perbaikan "Undefined array key returned" (4 Nov 2026)
- Penyebab: view dashboard siswa di komputer pengguna memakai `$stats['returned']` sedangkan controller hanya mengirim `active/due_soon/total` (dua versi file bercampur). Perbaikan: satu sumber `Loan::statsFor($userId)` (total, active, returned, pending, late, due_soon, fine) dipakai `StudentController` dan `StudentDashboardController`; view menampilkan 6 kartu dari kunci itu. Tes ditambah (belum dijalankan).

## Redesign "Dreamy Digital Library" (Tahap visual, 4 Nov 2026)
- Baru: `public/css/dreamy.css`, `public/js/dreamy.js`, `resources/views/partials/library-scene.blade.php`. Dimuat di `layouts/app` & `layouts/dashboard` setelah polish.css (hapus <link>/<script>-nya untuk mematikan seluruh lapisan).
- Diubah: `auth/login.blade.php`, `auth/register.blade.php` (scene + indikator kekuatan sandi), `landing.blade.php` + `LandingController.php` (statistik peminjaman, kategori, buku terpopuler dari data nyata), `layouts/panel.blade.php` (breadcrumb).
- Diperiksa di Chromium headless (pratinjau HTML statis, bukan Laravel): login & registrasi desktop/HP tanpa overflow horizontal dan tanpa error JS. Beranda, dashboard, katalog BELUM dilihat visual (butuh PHP).
- Tidak dibuat ulang karena sudah ada: reveal scroll, count-up, progres scroll, navbar is-scrolled, transisi keluar halaman (animations.js).
- Belum: ilustrasi khusus tiap halaman manajemen, laporan admin, E-book/Rating/Challenge (fitur Tahap 4 itu sendiri belum ada), dropdown navbar & menu mobile baru.

## Audit statis + laporan + motion lanjutan (4 Nov 2026)
- Audit tanpa PHP: semua route aktif -> controller/method ada; semua view yang dipanggil controller aktif ada; variabel Blade vs controller (heuristik) konsisten; `$fillable` model cocok dengan migrasi; sintaks JS lolos `node --check`.
- Temuan: view lama (`admin/loans/index`, `admin/members/*`, `admin/books/index`, `loans/index`, `auth/forgot`, `dashboard/siswa`, controller `Admin/*`, `LoanController`, `HomeController`) memakai nama route yang tidak ada -> TIDAK terjangkau route aktif; dibiarkan (belum dihapus). `/admin/laporan` sebelumnya tidak punya route dan view lamanya tidak menerima data -> dibuat: `AdminController::reports/exportLoans`, route `admin.reports(.export)`, view `admin/reports.blade.php` (statistik, grafik 6 bulan, terpopuler, terlambat, denda, unduh CSV), link sidebar "Laporan".
- Motion baru (dreamy.js/css): partikel, tilt 3D kartu, toast sukses dengan centang tergambar, skeleton saat cari/filter/pagination, validasi real-time registrasi. Diuji di Chromium pada pratinjau HTML statis (tanpa error JS).
- BELUM bisa diuji (tidak ada PHP/Composer/npm di sandbox): route:list, migrate:status, php artisan test, npm run build, alur peminjaman/persetujuan/pengembalian, QR, upload.
- Belum ada: Rating/ulasan, Reading Challenge, rekomendasi personal, E-book Corner, asisten informasi (Tahap 4B–F).

## Premium motion & visual V2 (4 Nov 2026) — DIJALANKAN & DIUJI
Kali ini PHP 8.3 + Composer tersedia, jadi Laravel benar-benar dijalankan (sebelumnya hanya pemeriksaan statis).

**Hasil uji nyata:** `php artisan migrate` OK; `php artisan test` **34 lulus** (28 lama + 6 baru `VisualLayerTest`); audit Chromium 60 halaman (guest/siswa/admin × desktop/tablet/HP): 0 error JS, 0 request gagal, 0 overflow horizontal (marquee kategori sengaja lebar & dipotong); alur UI daftar → login → ajukan → setujui → kembalikan → riwayat/notifikasi/laporan/QR OK; simulasi origin HTTPS Ngrok identik dengan localhost (0 mixed-content); `prefers-reduced-motion` mematikan animasi (45–64 → 0–4 aktif).

**Bug nyata yang ditemukan & diperbaiki**
1. **Ngrok:** Laravel 12 tidak memakai `Http/Kernel.php`, sehingga proxy tidak dipercaya → `asset()` menghasilkan `http://127.0.0.1:8000/...` (CSS/JS gagal dimuat). Perbaikan: `trustProxies(at:'*')` di `bootstrap/app.php`.
2. Pil navbar aktif tanpa padding (`polish.css` vs `dreamy.css` bentrok) → pil lembut + indikator yang meluncur.
3. Detail buku di HP overflow horizontal (inline `repeat(4,1fr)` pada "buku terkait") → class `.kat-related` responsif.
4. Judul seksi beranda bergeser (`.section-head` ber-margin auto tapi rata kiri); caption hero tertimpa sampul melayang; kategori HP 1 kolom panjang → 2 kolom.
5. Panel "Aktivitas Terbaru" dashboard admin kosong total & grafik rata saat data 0 → empty state.
6. Subjudul katalog "0+ judul tersedia" saat pencarian kosong → jumlah koleksi sebenarnya.

**Baru (tanpa file CSS/JS tambahan; lapisan "V2" di akhir `dreamy.css`/`dreamy.js`)**
- Navbar: indikator pil meluncur (desktop/tablet), tetap menu biasa di HP.
- Teks: rotating words 4 tagline (beranda), highlight kata penting (`.hl`), gradient animasi (`.grad-anim`); typing, text-reveal, count-up sudah ada sebelumnya.
- `partials/empty.blade.php`: empty state berilustrasi (8 varian: history, bell, shelf, search, inbox, people, chart, loan) dipakai di 12 tempat.
- `partials/panel-art.blade.php`: ornamen kartu kaca melayang di header panel, ikon/caption otomatis per route (disembunyikan ≤900px).

**Tidak dibuat (fiturnya memang belum ada di project):** Rating & ulasan, Reading Challenge, E-book Corner, rekomendasi, asisten (Tahap 4B–F). Begitu halamannya ada, cukup `@include('partials.empty'|'partials.panel-art')` dan kelas `.hl/.grad-anim/data-rotate`.

**Catatan:** `public/css/elibra.css` (1983 baris), `depth.css`, `depth.js` tidak dimuat layout mana pun (kode mati) — dibiarkan, aman dihapus. Font Google tidak bisa diuji di sandbox (diblokir); di komputer Anda dimuat normal. `database.sqlite` di ZIP sudah dimigrasi (data pengguna/buku tidak diubah).

## Perbaikan total animasi (5 Nov 2026)
- **Akar masalah:** `animations.css` dan 3 file CSS lain memadamkan SEMUA animasi (`animation-iteration-count:1`, durasi ~0) bila OS memakai "hemat gerak" (`prefers-reduced-motion`, mis. Windows > Efek animasi = mati); 3 file JS (typing, count-up, reveal, partikel) juga berhenti. Di komputer seperti itu situs tampak diam total. Diukur di Chromium: dengan flag itu animasi berjalan = 0 di semua halaman.
- **Perbaikan:** blok hemat-gerak dipindah ke kelas `html.no-motion` yang dikontrol tombol **"Animasi: Hidup/Mati"** (pojok kiri bawah, disimpan di localStorage). Default HIDUP, tidak mengikuti pengaturan OS. JS memakai `window.ELIBRA_REDUCE`.
- **Baru:** `public/js/motion-pref.js` (dimuat pertama), `public/css/motion-boost.css`, `public/js/motion-boost.js` (dimuat terakhir di `layouts/app` & `layouts/dashboard`): ikon buku/bookmark/bintang melayang di semua halaman (kecuali login/registrasi yang punya scene sendiri), gradient bergerak, kartu melayang + kilau berkala, ikon statistik bergoyang, bar grafik "bernapas", sampul detail buku melayang + cincin berputar + kilau, muncul bertahap, transisi halaman fade+slide masuk dan keluar.
- Tidak diubah: backend, database, autentikasi, route, controller.
- **Diuji (pratinjau statis hasil render kasar Blade -> HTML, BUKAN Laravel; PHP tidak ada di sandbox):** Chromium headless, 10 halaman x desktop 1280 / HP 390, OS reduced-motion AKTIF: animasi infinite 14-60 per halaman, 0 error JS, 0 request gagal, 0 overflow horizontal, tidak ada blok gelap besar; typing 0->83 karakter, marquee bergeser, count-up 2->12, scroll reveal opacity 0->1, tombol Animasi mati -> 0 animasi berjalan.
- **Belum diuji:** halaman nyata via `php artisan serve`, Ngrok, tampilan dengan data asli, Firefox/Safari.

## Revisi tema Navy, cover, profil, logout (4 Nov 2026)
- Tema warna: navy, putih tulang, teal, abu lembut (`public/css/theme.css` + palet semua CSS diganti). Sidebar navy, tombol teal.
- QR Code dihapus (route, controller, view, JS, CSS, tes). Kolom `books.qr_token` dibiarkan di database (tidak ada data dihapus).
- Animasi gelombang dihapus (`.hero-wave`, `.de-wave`).
- Cover buku 3:4: `partials/cover.blade.php` dipakai di katalog, detail, beranda, dashboard, daftar admin. Tanpa cover tampil "Cover Belum Tersedia". Admin unggah/ganti/hapus cover di form buku (JPG/PNG/WEBP, maks 2 MB, validasi gambar).
- Kolom baru: books.publisher/year/cover_path, users.username/photo (migrasi `2026_11_04_000002...`, hanya menambah kolom).
- Profil siswa & admin: `/profil` (`ProfileController`), hanya akun yang login; role & NIS tidak bisa diubah dari form.
- Logout: tombol di bar atas semua halaman panel, sidebar, dan navbar publik saat login.
- Impor data: `php artisan elibra:import-induk "BUKU INDUK KESELURUHAN.xlsx" --count=25` (cover dicocokkan lewat ISBN dari Open Library/Google Books).
- BELUM dijalankan: PHP & internet tidak ada di sandbox, file Excel belum diterima. Tes baru: `tests/Feature/ProfileCoverLogoutTest.php`.
- 14 foto cover dari pengguna dipasang (public/uploads/covers/<slug>.jpg, dicocokkan ke judul setelah dilihat satu per satu). Pasang ke database: `php artisan elibra:pasang-cover` (setelah `migrate`). Pemetaan: database/seeders/cover-map.json.

## Revisi cover, daftar langsung dashboard, lupa kata sandi (5 Nov 2026)
- **Daftar -> dashboard:** `AuthController::register` kini langsung `Auth::login` + regenerate sesi, lalu redirect ke `student.dashboard` (pesan selamat datang). Tidak lagi kembali ke halaman login.
- **Lupa kata sandi:** tautan "Lupa kata sandi?" di halaman login. Rute `/lupa-kata-sandi` (isi email ATAU NIS), `/atur-ulang-kata-sandi/{token}`; `PasswordResetController`, view `auth/forgot` + `auth/reset`, email berbahasa Indonesia (`AppServiceProvider`). Link berlaku 60 menit, jawaban selalu sama (tidak membocorkan akun terdaftar), dibatasi throttle. Perlu SMTP di `.env` (lihat `.env.example`); dengan `MAIL_MAILER=log` link ada di `storage/logs/laravel.log`.
- **Buku tanpa cover diganti:** `php artisan elibra:import-induk "BUKU_INDUK_KESELURUHAN.xlsx" --count=25 --ganti-tanpa-cover` menghapus buku yang belum punya cover (dan tidak punya riwayat pinjam), lalu mengisi dengan buku lain dari Excel yang covernya ditemukan lewat ISBN (Open Library / Google Books). Kandidat diurutkan dari yang paling mungkin punya cover (novel/populer, penerbit umum, terbitan baru; buku paket diturunkan). Tanpa cover tidak dipilih kecuali `--boleh-tanpa-cover`. Jika tak ada pengganti ditemukan, buku lama tidak dihapus.
- **Belum diuji:** PHP & internet tidak ada di sandbox, sehingga `php artisan test` (tes baru `PasswordResetTest`, `RegisterTest` diperbarui) dan pencarian cover belum dijalankan.

## Pendaftaran dibatasi daftar NIS (5 Nov 2026)
- Daftar NIS yang boleh mendaftar ada di `database/seeders/nis-terdaftar.json` (31 siswa, 24088-24121), dibaca langsung tanpa migrasi. NIS di luar daftar ditolak dengan satu pesan jelas. Hanya NIS yang diperiksa, nama tidak.
- Menambah siswa: tambahkan baris "NIS": "NAMA" di file JSON itu.
- Belum diuji (tidak ada PHP di sandbox); tes baru `test_nis_di_luar_daftar_ditolak` di `RegisterTest`.
- (5 Nov, perbaikan) NIS salah kini selalu tampil sebagai pesan jelas di bawah kolom NIS ("NIS salah: ... tidak ada dalam daftar siswa"), bukan halaman error. Jika tabel `nis_terdaftar` belum dimigrasi atau database bermasalah, juga tampil pesan ramah, bukan crash.
- (5 Nov, perbaikan) Cover tidak hilang lagi: bila `cover_path` kosong, `Book::cover_url` otomatis memakai foto di `public/uploads/covers` yang judulnya sama persis di `cover-map.json`. Emoji (👋 di sapaan dashboard siswa, tanda centang di dreamy.js) dihapus. ZIP tidak lagi menyertakan `database/database.sqlite` agar data/ database yang sedang dipakai tidak tertimpa.
- (5 Nov) Ganti buku tanpa cover minimal 50: `php artisan elibra:import-induk "BUKU_INDUK_KESELURUHAN.xlsx" --count=50 --ganti-tanpa-cover --cari-judul`. `--count` kini 20-100. Buku yang punya foto (termasuk dari cover-map.json) TIDAK dihapus; buku tanpa cover dan tanpa riwayat pinjam dihapus HANYA jika pengganti bercover yang ditemukan >= --count (atau pakai --paksa). Buku tanpa cover yang punya riwayat peminjaman tidak dihapus agar riwayatnya aman.
- (8 Nov) 30 buku (daftar-30-buku.json) ditambahkan otomatis SEKALI saat website pertama dibuka (`App\Support\Tambah30Buku`, dipanggil dari AppServiceProvider::boot), tanpa perintah. Tanpa cover (tampil "Cover Belum Tersedia"); tidak menghapus apa pun. Penanda: `storage/app/tambah-30-buku.done`.

## Bukti Peminjaman + 30 buku lengkap (8 Nov 2026)
- Rute `loans.receipt` (/bukti-peminjaman/{loan}) dan `loans.receipt.pdf`; `ReceiptController`, view `loans/receipt.blade.php` (tombol Cetak + Download PDF), PDF dibuat tanpa library (`App\Support\SimplePdf`, `ReceiptPdf`). Nomor transaksi `TRX-YYYYMMDD-00000` dari tanggal dibuat + id (tanpa migrasi). Hanya pemilik atau admin; hanya status disetujui/dipinjam/terlambat/dikembalikan.
- Tombol "Bukti" ditambahkan di Peminjaman Saya dan Riwayat.
- 30 buku kini memuat sinopsis (daftar-30-buku.json); `Tambah30Buku` (penanda v2) menambah buku baru dan melengkapi sinopsis buku yang sudah ada. Cover belum ada (tidak bisa diunduh dari sandbox).
- Tes baru: tests/Feature/ReceiptTest.php. BELUM dijalankan (tidak ada PHP di sandbox); struktur PDF diverifikasi lewat cermin Python (qpdf/pypdf/pdftotext).

## Perbaikan katalog & loading ganda (9 Nov 2026)
- **Katalog:** label "Menampilkan 21 dari 51 buku" memakai `$books->count()` (jumlah item di halaman, halaman 2 = 21). Kini rentang nyata: "Menampilkan 31–51 dari 51 buku" (`firstItem()`–`lastItem()`); hasil kosong: "Tidak ada buku yang cocok". `CatalogController`: semua urutan diakhiri `id` (urutan "Terbaru" hanya `created_at`, padahal 30 buku dibuat bersamaan -> bisa ganda/hilang antar halaman); `?page` di luar rentang diarahkan ke halaman terakhir.
- **Loading ganda:** `animations.js` (spinner `.is-loading::before`) dan `polish.js` (`.spin`) sama-sama menambah spinner di tombol; di katalog skeleton (`dreamy.js`) juga muncul bersama spinner tombol (form GET) dan fade keluar (klik pagination, jeda 180 ms). Kini: tombol POST = satu spinner (`polish.js`); form GET/pagination/kategori katalog = skeleton saja (tanpa fade & tanpa jeda); tautan lain = fade halaman. Ctrl/Cmd+klik tidak lagi mengunci skeleton.
- File: `CatalogController.php`, `catalog.blade.php`, `public/js/animations.js`, `public/js/dreamy.js`, `public/css/animations.css`, tes baru `tests/Feature/CatalogPaginationTest.php` (BELUM dijalankan: PHP tidak ada di sandbox).
- Diuji di Chromium (harness statis memakai CSS/JS asli): indikator aktif bersamaan sebelum 2 -> sesudah 1; jeda klik->request pagination 198 ms -> 7 ms.

## Cover 22 dari 30 buku (9 Nov 2026)
- 22 foto (ZIP WhatsApp) dicocokkan ke judul satu per satu, dipotong hanya bagian buku (6 foto punya latar/padding), dikecilkan maks. tinggi 900 px, disimpan di `public/uploads/covers/<slug>.jpg` dan didaftarkan di `database/seeders/cover-map.json` (kunci = judul persis di `daftar-30-buku.json`). `Book::cover_url` memakai peta ini bila `cover_path` kosong -> tidak perlu migrasi/perintah, cukup timpa file + `php artisan optimize:clear`.
- Belum ada foto: no. 2 Negeri Di Ujung Tanduk, 8 Harga Sebuah Percaya, 9 Kau, Aku, & Sepucuk Angpau Merah, 10 Seopotong Hati Yang Baru, 13 Dalam Mihrab Cinta, 24 Sang Singa, Sang Penyihir Dari Lampau, 26 Pria Tak Tampak, 28 Jo & Kas (tetap tampil "Cover Belum Tersedia").

## 8 buku tanpa cover diganti (9 Nov 2026)
- Permintaan: buku yang covernya sengaja belum dipasang diganti buku lain (novel dll). Daftar `daftar-30-buku.json` kini 22 buku (semua bercover); 8 judul dibuang: Negeri Di Ujung Tanduk, Harga Sebuah Percaya, Kau Aku & Sepucuk Angpau Merah, Seopotong Hati Yang Baru, Dalam Mihrab Cinta, Sang Singa Sang Penyihir Dari Lampau, Pria Tak Tampak, Jo & Kas.
- `Tambah30Buku` (penanda v3, otomatis saat website dibuka): menghapus 8 judul itu dari database hanya bila belum bercover DAN belum pernah dipinjam. Tes: `HapusBukuTanpaCoverTest` (baru), `ReceiptTest` disesuaikan (22). BELUM dijalankan (tidak ada PHP di sandbox).
- Pengganti: menunggu judul + foto cover dari pengguna (tidak bisa mengunduh cover di sandbox, dan tidak dibuat cover palsu).

## 7 cover buku pelajaran (9 Nov 2026)
- 7 foto cover (ZIP WhatsApp kedua) dipasang: Biologi, Bahasa Indonesia Kelas X, Fisika Dasar, Informatika Kelas X, Bahasa Inggris Kelas X, Kimia Dasar, Matematika Kelas X. File di `public/uploads/covers/<slug>.jpg`, terdaftar di `cover-map.json` dan `daftar-30-buku.json` (kategori Pelajaran, stok 3). Penanda `Tambah30Buku` naik ke v4 sehingga buku baru otomatis masuk saat website dibuka (lalu `php artisan optimize:clear`).
- Hanya 7 foto untuk 8 buku pengganti: masih kurang 1 judul + cover. Penulis/penerbit/tahun buku pelajaran adalah perkiraan dari tampilan sampul; ubah di JSON bila perlu. BELUM dijalankan (tidak ada PHP di sandbox).
