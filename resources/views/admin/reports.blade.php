@extends('layouts.panel')
@section('title', 'Laporan — Admin')
@section('heading', 'Laporan Perpustakaan')
@section('subheading', 'Ringkasan peminjaman berdasarkan data sebenarnya.')
@section('action')
    <a href="{{ route('admin.reports.export') }}" class="btn-solid" style="text-decoration:none;">Unduh CSV</a>
@endsection
@section('panel')
    <div class="stat-row" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
        @foreach([['Judul buku', $stats['total_books'], ''], ['Anggota', $stats['total_members'], ''], ['Sedang dipinjam', $stats['active_loans'], 'ok'], ['Terlambat', $stats['late_loans'], 'warn'], ['Menunggu', $stats['pending'], 'warn'], ['Dikembalikan', $stats['returned'], 'ok'], ['Total denda (Rp)', $stats['fine'], 'warn']] as [$label, $val, $cls])
            <div class="stat-card {{ $cls }}"><div class="sv">{{ number_format($val) }}</div><div class="sl">{{ $label }}</div></div>
        @endforeach
    </div>

    <div class="panel-row">
        <div class="panel">
            <div class="panel-head"><h3>Peminjaman 6 Bulan Terakhir</h3></div>
            @php $max = max(1, $monthly->max('total')); @endphp
            <div class="dl-chart">
                @foreach($monthly as $m)
                    <div class="dl-bar">
                        <span class="n">{{ $m['total'] }}</span>
                        <div class="b" style="--h:{{ max(4, ($m['total'] / $max) * 100) }}%;"></div>
                        <span class="l">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Buku Terpopuler</h3></div>
            @forelse($topBooks as $i => $b)
                <div class="loan-row"><div class="dl-rank">{{ $i + 1 }}</div>
                    <div class="loan-info"><div class="t">{{ $b->title }}</div><div class="a">{{ $b->author }} · {{ $b->borrow_count }}× dipinjam</div></div>
                </div>
            @empty
                @include('partials.empty', ['type'=>'chart','title'=>'Belum ada data peminjaman','text'=>'Statistik terisi otomatis dari transaksi nyata.'])
            @endforelse
        </div>
    </div>

    <div class="panel" style="overflow-x:auto;">
        <div class="panel-head"><h3>Peminjaman Terlambat</h3><a class="more" style="text-decoration:none;" href="{{ route('admin.loans', ['status' => 'late']) }}">Lihat semua</a></div>
        <table class="dtable">
            <tr><th>Anggota</th><th>Buku</th><th>Jatuh Tempo</th><th>Terlambat</th></tr>
            @forelse($lateLoans as $l)
                <tr>
                    <td>{{ $l->user->name }}</td><td>{{ $l->book->title }}</td>
                    <td>{{ $l->due_date->translatedFormat('d M Y') }}</td>
                    <td><span class="badge late">{{ $l->due_date->diffInDays(today()) }} hari</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="de-cell">@include('partials.empty', ['type'=>'check' ,'title'=>'Tidak ada peminjaman terlambat','text'=>'Semua buku dikembalikan tepat waktu.'])</td></tr>
            @endforelse
        </table>
    </div>
@endsection
