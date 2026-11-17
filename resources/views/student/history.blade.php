@extends('layouts.panel')
@section('title', 'Riwayat Peminjaman')
@section('heading', 'Riwayat Peminjaman')
@section('subheading', 'Buku yang sudah kamu kembalikan dan pengajuan yang ditolak.')
@section('panel')
    <div class="stat-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card ok"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div><div class="sv">{{ $summary['returned'] }}</div><div class="sl">Buku dikembalikan</div></div>
        <div class="stat-card warn"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="sv">{{ $summary['late'] }}</div><div class="sl">Pernah terlambat</div></div>
        <div class="stat-card"><div class="si"><svg class="ic" width="17" height="17" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div><div class="sv" style="font-size:20px;">Rp{{ number_format($summary['fine'], 0, ',', '.') }}</div><div class="sl">Total denda</div></div>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Riwayat ({{ number_format($loans->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Buku</th><th>Tgl Ambil</th><th>Dikembalikan</th><th>Status</th><th>Keterangan</th><th>Bukti</th></tr>
            @forelse ($loans as $loan)
            <tr>
                <td>
                    <div class="row-p">
                        @include('partials.cover', ['book' => $loan->book, 'size' => 'xs'])
                        <div>
                            <div class="t">{{ $loan->book->title }}</div>
                            <div class="s">{{ $loan->book->author }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $loan->pickup_date->translatedFormat('d M Y') }}</td>
                <td>{{ $loan->returned_at?->translatedFormat('d M Y') ?? '-' }}</td>
                <td><span class="badge {{ $loan->status === 'returned' ? 'ok' : 'late' }}">{{ $loan->status_label }}</span></td>
                <td>
                    @if($loan->status === 'rejected')
                        {{ $loan->reject_reason ?: 'Tidak ada alasan dicantumkan.' }}
                    @elseif($loan->fine > 0)
                        Denda Rp{{ number_format($loan->fine, 0, ',', '.') }}
                    @else
                        Tepat waktu
                    @endif
                </td>
                <td>
                    @if($loan->has_receipt)
                        <a href="{{ route('loans.receipt', $loan) }}" style="font-size:12px;font-weight:800;color:var(--pink-700);">Bukti</a>
                    @else
                        <span style="color:var(--ink-soft2);font-size:12px;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="de-cell">@include('partials.empty', ['type'=>'history','title'=>'Belum ada riwayat','text'=>'Buku yang sudah kamu kembalikan akan muncul di sini.','href'=>route('catalog'),'cta'=>'Cari buku'])</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">
        {{ $loans->links('vendor.pagination.elibra') }}
    </div>
@endsection
