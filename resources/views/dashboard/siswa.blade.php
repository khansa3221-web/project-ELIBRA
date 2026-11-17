@extends('layouts.dashboard')
@section('title', 'Dashboard Siswa — E-LIBRA')
@section('content')

<div class="app-shell">
  <div class="app-side">
    <div class="brand"><div class="mark"><svg class="ic" width="17" height="17" viewBox="0 0 24 24" style="color:#fff"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>E-LIBRA</div>
    <div class="app-nav">
      <a href="{{ route('dashboard') }}" class="active"><span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></span>Dashboard</a>
      <a href="{{ route('catalog') }}"><span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg></span>Cari Buku</a>
      <a href="{{ route('landing') }}"><span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg></span>Beranda</a>
    </div>
    <div class="app-side-bottom">
      <div class="app-user">
        <div class="av">{{ auth()->user()->initials() }}</div>
        <div><div class="n">{{ auth()->user()->name }}</div><div class="r">Kelas {{ auth()->user()->kelas }}</div></div>
      </div>
      <form method="POST" action="{{ route('logout') }}" style="margin-top:10px;">
        @csrf
        <button type="submit" class="btn-ghost" style="width:100%;justify-content:center;display:flex;">Keluar</button>
      </form>
    </div>
  </div>

  <div class="app-main">
    <div class="app-topbar">
      <div><h1>Halo, {{ explode(' ', auth()->user()->name)[0] }}</h1><p>Berikut ringkasan peminjaman bukumu hari ini.</p></div>
      <a href="{{ route('catalog') }}" class="btn-solid">Cari Buku Baru <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
    </div>

    @if (session('success'))<div class="alert alert-success" style="margin:0 0 18px;">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-error" style="margin:0 0 18px;">{{ session('error') }}</div>@endif

    <div class="stat-row">
      <div class="stat-card"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div><div class="sv">{{ $loansAktif->count() }}</div><div class="sl">PEMINJAMAN AKTIF</div></div>
      <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div><div class="sv">{{ $jatuhTempoDekat }}</div><div class="sl">JATUH TEMPO ≤ 3 HARI</div></div>
      <div class="stat-card"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg></div><div class="sv">{{ $totalPernahPinjam }}</div><div class="sl">TOTAL PERNAH DIPINJAM</div></div>
      <div class="stat-card {{ $dendaBerjalan == 0 ? 'ok' : '' }}"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div><div class="sv">Rp{{ number_format($dendaBerjalan, 0, ',', '.') }}</div><div class="sl">DENDA BERJALAN</div></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Peminjaman Aktif</h3><span class="more">{{ $loansAktif->count() }} buku</span></div>
      @forelse ($loansAktif as $loan)
        <div class="loan-row">
          <div class="loan-cover {{ $loan->book->cover_theme }}"></div>
          <div class="loan-info"><div class="t">{{ $loan->book->title }}</div><div class="a">{{ $loan->book->author }} · Dipinjam {{ $loan->tanggal_ambil->translatedFormat('d M Y') }}</div></div>
          <span class="badge {{ $loan->isTerlambat() ? 'late' : 'active' }}">
            {{ $loan->isTerlambat() ? 'Terlambat' : 'Jatuh tempo '.$loan->jatuhTempo()->translatedFormat('d M') }}
          </span>
        </div>
      @empty
        <p style="font-size:13px;color:var(--ink-soft);">Kamu belum memiliki peminjaman aktif.</p>
      @endforelse
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Rekomendasi untukmu</h3><a href="{{ route('catalog') }}" class="more">Lihat katalog</a></div>
      <div class="mini-grid">
        @foreach ($rekomendasi as $buku)
          <a href="{{ route('books.show', $buku) }}">
            <div class="mini-cover {{ $buku->cover_theme }}"><span class="b">{{ $buku->category->name }}</span><span class="t">{{ $buku->title }}</span></div>
            <div class="mini-cap">Tersedia · {{ $buku->stock }}</div>
          </a>
        @endforeach
      </div>
    </div>
  </div>
</div>

@endsection
