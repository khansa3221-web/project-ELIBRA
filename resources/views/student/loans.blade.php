@extends('layouts.panel')
@section('title', 'Peminjaman Saya')
@section('heading', 'Peminjaman Saya')
@section('subheading', 'Semua pengajuan dan peminjaman buku milikmu.')
@section('action')
<a href="{{ route('catalog') }}" class="btn-solid">Cari Buku Baru
                        <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
@endsection
@section('panel')
    <div class="panel">
        <div class="panel-head"><h3>Semua Peminjaman</h3></div>
        <table class="dtable">
            <tr><th>Buku</th><th>Tgl Ambil</th><th>Jatuh Tempo</th><th>Status</th><th>Keterangan</th><th>Bukti</th></tr>
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
                <td>{{ $loan->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>
                    @php
                        $badgeClass = match($loan->status) {
                            'pending' => 'pending',
                            'approved', 'active' => 'active',
                            'late' => 'late',
                            'returned' => 'ok',
                            'rejected' => 'late',
                            default => '',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $loan->status_label }}</span>
                </td>
                <td>
                    @if($loan->status === 'rejected')
                        {{ $loan->reject_reason ?: '-' }}
                    @elseif($loan->status === 'late')
                        Terlambat {{ $loan->late_days }} hari · denda Rp{{ number_format($loan->current_fine, 0, ',', '.') }}
                    @elseif($loan->status === 'returned')
                        {{ $loan->fine > 0 ? 'Denda Rp'.number_format($loan->fine, 0, ',', '.') : 'Tepat waktu' }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($loan->has_receipt)
                        <a href="{{ route('loans.receipt', $loan) }}" class="btn-ghost" style="font-size:12px;font-weight:800;color:var(--pink-700);">Bukti</a>
                    @else
                        <span style="color:var(--ink-soft2);font-size:12px;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="de-cell">@include('partials.empty', ['type'=>'loan','title'=>'Belum ada peminjaman','text'=>'Yuk cari buku favoritmu di katalog dan ajukan peminjaman.','href'=>route('catalog'),'cta'=>'Jelajahi katalog'])</td></tr>
            @endforelse
        </table>
    </div>

@endsection
