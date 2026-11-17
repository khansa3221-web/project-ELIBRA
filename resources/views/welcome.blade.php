@extends('layouts.app')
@section('title', 'E-LIBRA — Pinjam Buku Sekolah Tanpa Antre')
@section('content')

<div class="hero">
  <div class="hero-grid">
    <div>
      <div class="hero-badge"><svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9z"/></svg>Perpustakaan digital sekolah</div>
      <h1>Pinjam buku sekolah <span class="accent">tanpa antre</span>, cek stok dari mana saja</h1>
      <p class="lead">E-LIBRA membantu siswa mencari, mengajukan, dan memantau peminjaman buku perpustakaan secara online — sementara petugas mengelola stok dan verifikasi lebih cepat.</p>
      <div class="hero-actions">
        <a href="{{ route('catalog') }}" class="btn-solid">Mulai Cari Buku <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <a href="#cara-kerja" class="btn-ghost">Lihat cara kerja →</a>
      </div>
      <div class="hero-stats">
        <div class="hstat"><div class="v">{{ number_format($totalBuku ?? 0, 0, ',', '.') }}+</div><div class="l">EKSEMPLAR BUKU</div></div>
        <div class="hstat"><div class="v">{{ $judulBuku ?? 0 }}</div><div class="l">JUDUL TERSEDIA</div></div>
        <div class="hstat"><div class="v">98%</div><div class="l">PENGAJUAN CEPAT</div></div>
      </div>
    </div>
    <div class="hero-art">
      <div class="stack-books"><div class="qt">"Cari, ajukan, dan pantau pinjaman buku dalam satu genggaman."</div></div>
      <div class="float-card card-search">
        <div class="row"><svg class="ic" width="13" height="13" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>Cari "Fisika Dasar"...</div>
      </div>
      <div class="float-card card-notif">
        <div class="ic"><svg class="ic" width="16" height="16" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div><div class="t">Pengajuan disetujui</div><div class="s">Ambil buku sebelum jatuh tempo</div></div>
      </div>
      <div class="float-card card-rating">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="#E7A559"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/></svg>
        <span class="v">4.9</span><span style="font-size:11px;color:var(--ink-soft);font-weight:700;">dari siswa</span>
      </div>
    </div>
  </div>
</div>

<div class="logo-strip">
  <div class="l">DIPERCAYA OLEH UNIT SEKOLAH</div>
  <div class="marks"><span>Perpustakaan</span><span>Kesiswaan</span><span>Tata Usaha</span><span>OSIS</span><span>Kurikulum</span></div>
</div>

<div class="section" id="fitur">
  <div class="section-head">
    <div class="k">FITUR UTAMA</div>
    <h2>Semua kebutuhan peminjaman buku, dalam satu sistem</h2>
    <p>Dirancang untuk memudahkan siswa dan meringankan pekerjaan administratif petugas perpustakaan.</p>
  </div>
  <div class="feat-grid">
    <div class="feat-card">
      <div class="fi"><svg class="ic" width="19" height="19" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg></div>
      <h3>Katalog & Stok Real-time</h3>
      <p>Cari judul, penulis, atau kategori dan langsung lihat ketersediaan buku tanpa ke rak.</p>
    </div>
    <div class="feat-card">
      <div class="fi"><svg class="ic" width="19" height="19" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
      <h3>Pengajuan & Verifikasi</h3>
      <p>Ajukan peminjaman dari rumah, petugas menyetujui atau menolak lengkap dengan catatan.</p>
    </div>
    <div class="feat-card">
      <div class="fi"><svg class="ic" width="19" height="19" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
      <h3>Denda Otomatis</h3>
      <p>Perhitungan keterlambatan dihitung sistem berdasarkan tanggal jatuh tempo, tanpa selisih catat manual.</p>
    </div>
    <div class="feat-card">
      <div class="fi"><svg class="ic" width="19" height="19" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></div>
      <h3>Dashboard & Laporan</h3>
      <p>Statistik peminjaman, buku terpopuler, dan aktivitas terbaru untuk petugas.</p>
    </div>
  </div>
</div>

<div class="section" id="cara-kerja" style="padding-top:0;">
  <div class="steps-wrap">
    <div class="section-head" style="margin-bottom:44px;">
      <div class="k">CARA KERJA</div>
      <h2>Empat langkah dari cari buku sampai kembali</h2>
    </div>
    <div class="steps-grid">
      <div class="step-line"></div>
      <div class="step"><div class="sn">1</div><h4>Cari & Pilih Buku</h4><p>Telusuri katalog berdasarkan judul, penulis, atau kategori.</p></div>
      <div class="step"><div class="sn">2</div><h4>Ajukan Peminjaman</h4><p>Pilih tanggal pinjam, sistem kirim ke petugas untuk diverifikasi.</p></div>
      <div class="step"><div class="sn">3</div><h4>Ambil di Perpustakaan</h4><p>Setelah disetujui, ambil buku fisik sesuai tanggal jatuh tempo.</p></div>
      <div class="step"><div class="sn">4</div><h4>Kembalikan Tepat Waktu</h4><p>Kembalikan buku, sistem catat otomatis dan hitung denda bila telat.</p></div>
    </div>
  </div>
</div>

<div class="section" id="tentang">
  <div class="catalog-preview">
    <div class="cp-text">
      <div class="k">KATALOG BUKU</div>
      <h2>Telusuri ribuan judul, langsung tahu stoknya</h2>
      <p>Filter berdasarkan kategori, cek ketersediaan secara real-time, dan ajukan peminjaman hanya dengan beberapa klik.</p>
      <div class="cp-list">
        <div class="cp-item"><span class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>Filter kategori & status ketersediaan</span></div>
        <div class="cp-item"><span class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>Pencarian judul dan penulis instan</span></div>
        <div class="cp-item"><span class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>Detail lengkap ISBN, rak, dan stok</span></div>
      </div>
      <a href="{{ route('catalog') }}" class="btn-solid" style="margin-top:24px; display:inline-flex;">Buka Katalog <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>
    <div class="mini-catalog">
      <div class="mini-search"><svg class="ic" width="13" height="13" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>Cari judul buku...</div>
      <div class="mini-grid">
        <div><div class="mini-cover mc1"><span class="b">Fiksi</span><span class="t">Laskar Pelangi</span></div><div class="mini-cap">Tersedia · 4</div></div>
        <div><div class="mini-cover mc2"><span class="b">Sains</span><span class="t">Fisika Dasar</span></div><div class="mini-cap">Tersedia · 6</div></div>
        <div><div class="mini-cover mc3"><span class="b">Fiksi</span><span class="t">Bumi Manusia</span></div><div class="mini-cap">Tersedia · 2</div></div>
        <div><div class="mini-cover mc4"><span class="b">Teknik</span><span class="t">Algoritma</span></div><div class="mini-cap">Tersedia · 5</div></div>
        <div><div class="mini-cover mc5"><span class="b">Sejarah</span><span class="t">Nusantara</span></div><div class="mini-cap">Tersedia · 3</div></div>
        <div><div class="mini-cover mc6"><span class="b">Sains</span><span class="t">Biologi Sel</span></div><div class="mini-cap">Tersedia · 6</div></div>
      </div>
    </div>
  </div>
</div>

<div class="cta-band">
  <div>
    <h2>Siap mulai pinjam buku tanpa antre?</h2>
    <p>Daftar akun siswa gratis dan mulai ajukan peminjaman buku favoritmu hari ini juga.</p>
  </div>
  <a href="{{ route('register') }}" class="btn-white">Daftar Sekarang <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
</div>

@endsection
