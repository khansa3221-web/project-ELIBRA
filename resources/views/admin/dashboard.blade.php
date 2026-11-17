@extends('layouts.panel')
@section('title', 'Dashboard Admin')
@section('heading', 'Halo, ' . (explode(' ', auth()->user()->name)[0]))
@section('subheading', 'Ada ' . ($stats['pending']) . ' pengajuan peminjaman yang menunggu verifikasi hari ini.')
@section('panel')
    @include('partials.hello-banner', ['tips' => ['Cek pengajuan baru supaya siswa tidak menunggu lama.','Perbarui stok buku setiap ada buku masuk atau rusak.','Lihat laporan untuk tahu buku apa yang paling sering dipinjam.']])
    <div class="stat-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div><div class="sv">{{ number_format($stats['total_books']) }}</div><div class="sl">Total judul buku</div></div>
        <div class="stat-card ok"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div><div class="sv">{{ $stats['active_loans'] }}</div><div class="sl">Peminjaman aktif</div></div>
        <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="sv">{{ $stats['pending'] }}</div><div class="sl">Menunggu verifikasi</div></div>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Pengajuan Menunggu Verifikasi</h3>
            <a href="{{ route('admin.submissions') }}" class="more" style="text-decoration:none;">Lihat semua</a>
        </div>
        <table class="dtable">
            <tr><th>Anggota</th><th>Judul Buku</th><th>Tanggal Ajuan</th><th>Status</th><th></th></tr>
            @forelse($pendingLoans as $loan)
                <tr>
                    <td>
                        <div class="row-p">
                            <div class="av">{{ strtoupper(substr($loan->user->name, 0, 2)) }}</div>
                            <div>
                                <div class="t">{{ $loan->user->name }}</div>
                                <div class="s">{{ $loan->user->class ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $loan->book->title }}</td>
                    <td>{{ $loan->created_at->translatedFormat('d M Y') }}</td>
                    <td><span class="badge pending">Menunggu</span></td>
                    <td>
                        <div class="act-cell">
                            <form method="POST" action="{{ route('admin.approve', $loan) }}">
                                @csrf
                                <button class="act-btn approve txt" style="border:none;cursor:pointer;"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reject', $loan) }}">
                                @csrf
                                <button class="act-btn reject txt" style="border:none;cursor:pointer;"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg> Tolak</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="de-cell">@include('partials.empty', ['type'=>'inbox','title'=>'Semua pengajuan sudah ditangani','text'=>'Pengajuan baru dari siswa akan tampil di sini.'])</td></tr>
            @endforelse
        </table>
    </div>

    <div class="panel-row">
        <div class="panel">
            <div class="panel-head"><h3>Buku Terpopuler</h3></div>
            @if($popularBooks->sum('loans_count') === 0)
                @include('partials.empty', ['type'=>'chart','title'=>'Belum ada data peminjaman','text'=>'Grafik buku terpopuler muncul setelah ada peminjaman.'])
            @else
            <div class="bar-chart">
                @php $max = $popularBooks->max('loans_count') ?: 1; @endphp
                @foreach($popularBooks as $b)
                    <div class="bar-wrap">
                        <div class="bar" style="height:{{ max(20, ($b->loans_count / $max) * 110) }}px;"></div>
                        <div class="bl">{{ \Illuminate\Support\Str::limit($b->title, 12) }}</div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Aktivitas Terbaru</h3></div>
            @forelse($recentActivity as $act)
                <div class="loan-row">
                    @include('partials.cover', ['book' => $act->book, 'size' => 'xs'])
                    <div class="loan-info">
                        <div class="t">{{ $act->book->title }} — {{ $act->status_label }}</div>
                        <div class="a">oleh {{ $act->user->name }} · {{ $act->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                @include('partials.empty', ['type'=>'loan','title'=>'Belum ada aktivitas','text'=>'Peminjaman dan pengembalian terbaru akan tercatat di sini.'])
            @endforelse
        </div>
    </div>

@endsection
