@extends('layouts.panel')
@section('title', 'Dashboard Siswa')
@section('heading', 'Halo, ' . (explode(' ', auth()->user()->name)[0]) . '')
@section('subheading', 'Berikut ringkasan peminjaman bukumu hari ini.')
@section('action')
<a href="{{ route('catalog') }}" class="btn-solid">Cari Buku Baru
                        <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
@endsection
@section('panel')
    @include('partials.hello-banner', ['tips' => ['Cari buku lewat judul, penulis, atau kategori di katalog.','Ajukan peminjaman dulu, ambil bukunya setelah disetujui petugas.','Kembalikan buku tepat waktu supaya terhindar dari denda.']])
    <div class="stat-row" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
        <div class="stat-card "><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div><div class="sv">{{ $stats['active'] }}</div><div class="sl">Sedang dipinjam</div></div>
        <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="sv">{{ $stats['pending'] }}</div><div class="sl">Menunggu persetujuan</div></div>
        <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M12 3l9 16H3z"/><path d="M12 10v4M12 17h.01"/></svg></div><div class="sv">{{ $stats['due_soon'] }}</div><div class="sl">Jatuh tempo ≤ 2 hari</div></div>
        <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div><div class="sv">{{ $stats['late'] }}</div><div class="sl">Terlambat</div></div>
        <div class="stat-card ok"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div><div class="sv">{{ $stats['returned'] }}</div><div class="sl">Sudah dikembalikan</div></div>
        <div class="stat-card ok"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg></div><div class="sv">{{ $stats['total'] }}</div><div class="sl">Total peminjaman</div></div>
    </div>

    @if($notifs->isNotEmpty())
    <div class="panel">
        <div class="panel-head"><h3>Notifikasi Terbaru</h3>
            <a href="{{ route('student.notifications') }}" class="more" style="text-decoration:none;">Lihat semua</a>
        </div>
        @foreach($notifs as $n)
            @php $tone = match($n->type) { 'approved', 'returned' => 'ok', 'rejected', 'overdue' => 'bad', 'due_soon' => 'warn', default => 'info' }; @endphp
            <div class="notif-item {{ $tone }} unread">
                <span class="dot"></span>
                <div class="body"><div class="t">{{ $n->title }}</div><div class="m">{{ $n->message }}</div></div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="panel">
        <div class="panel-head"><h3>Peminjaman Aktif</h3>
            <a href="{{ route('student.loans') }}" class="more" style="text-decoration:none;">Lihat semua</a>
        </div>
        @forelse($activeLoans as $loan)
            <div class="loan-row">
                @include('partials.cover', ['book' => $loan->book, 'size' => 'xs'])
                <div class="loan-info">
                    <div class="t">{{ $loan->book->title }}</div>
                    <div class="a">{{ $loan->book->author }} · Dipinjam {{ $loan->pickup_date->translatedFormat('d M Y') }}</div>
                </div>
                @php $sisa = now()->startOfDay()->diffInDays($loan->due_date->copy()->startOfDay(), false); @endphp
                <span class="badge {{ $sisa < 0 ? 'late' : ($sisa <= 2 ? 'due' : 'active') }}">
                    {{ $sisa < 0 ? 'Terlambat' : 'Jatuh tempo '.$loan->due_date->translatedFormat('d M') }}
                </span>
            </div>
        @empty
            @include('partials.empty', ['type'=>'shelf','title'=>'Belum ada peminjaman aktif','text'=>'Pilih buku di katalog untuk mulai.','href'=>route('catalog'),'cta'=>'Cari buku'])
        @endforelse
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Rekomendasi untukmu</h3>
            <a href="{{ route('catalog') }}" class="more" style="text-decoration:none;">Lihat katalog</a>
        </div>
        <div class="mini-grid">
            @foreach($recommendations as $book)
                <a href="{{ route('books.show', $book) }}" style="text-decoration:none;color:inherit;">
                    @include('partials.cover', ['book' => $book, 'size' => 'mini'])
                    <div class="mini-cap">Tersedia · {{ $book->available_stock }}</div>
                </a>
            @endforeach
        </div>
    </div>

@endsection
