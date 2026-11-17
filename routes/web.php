<?php

use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::redirect('/', '/login');
Route::get('/beranda', [LandingController::class, 'index'])->name('landing');

Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/katalog/{book}', [CatalogController::class, 'show'])->name('books.show');

// ---------- Login / Daftar / Keluar ----------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---------- Lupa kata sandi ----------
Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'showForgot'])->name('password.request');
Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');

// ---------- Profil (siswa & admin, hanya akun yang login) ----------
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil', [ProfileController::class, 'update'])->middleware('throttle:20,1')->name('profile.update');

    // Bukti peminjaman (pemilik peminjaman atau admin)
    Route::get('/bukti-peminjaman/{loan}', [ReceiptController::class, 'show'])->name('loans.receipt');
    Route::get('/bukti-peminjaman/{loan}/pdf', [ReceiptController::class, 'pdf'])->name('loans.receipt.pdf');
});

// ---------- Siswa ----------
Route::middleware(['auth', 'role:siswa'])->prefix('dashboard')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('student.dashboard');
    Route::get('/peminjaman', [StudentController::class, 'loans'])->name('student.loans');
    Route::post('/books/{book}/borrow', [StudentController::class, 'borrow'])->name('books.borrow');
    Route::get('/riwayat', [StudentController::class, 'history'])->name('student.history');
    Route::get('/notifikasi', [StudentController::class, 'notifications'])->name('student.notifications');
    Route::post('/notifikasi/baca', [StudentController::class, 'readNotifications'])->name('student.notifications.read');
});

// ---------- Admin / Petugas ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    // Data buku
    Route::get('/buku', [AdminBookController::class, 'index'])->name('admin.books');
    Route::get('/buku/tambah', [AdminBookController::class, 'create'])->name('admin.books.create');
    Route::post('/buku', [AdminBookController::class, 'store'])->name('admin.books.store');
    Route::get('/buku/impor', [AdminBookController::class, 'importForm'])->name('admin.books.import');
    Route::post('/buku/impor', [AdminBookController::class, 'import'])->name('admin.books.import.store');
    Route::get('/buku/template', [AdminBookController::class, 'template'])->name('admin.books.template');
    Route::get('/buku/{book}/ubah', [AdminBookController::class, 'edit'])->name('admin.books.edit');
    Route::put('/buku/{book}', [AdminBookController::class, 'update'])->name('admin.books.update');
    Route::delete('/buku/{book}', [AdminBookController::class, 'destroy'])->name('admin.books.destroy');

    // Pengajuan, anggota
    Route::get('/pengajuan', [AdminController::class, 'submissions'])->name('admin.submissions');
    Route::post('/pengajuan/{loan}/approve', [AdminController::class, 'approve'])->name('admin.approve');
    Route::post('/pengajuan/{loan}/reject', [AdminController::class, 'reject'])->name('admin.reject');
    Route::get('/peminjaman', [AdminController::class, 'loans'])->name('admin.loans');
    Route::post('/peminjaman/{loan}/return', [AdminController::class, 'returnBook'])->name('admin.loans.return');
    Route::get('/laporan', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/laporan/ekspor', [AdminController::class, 'exportLoans'])->name('admin.reports.export');
    Route::get('/anggota', [AdminController::class, 'members'])->name('admin.members');
});
