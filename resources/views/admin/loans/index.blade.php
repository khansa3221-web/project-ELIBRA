@extends('layouts.panel')
@section('title', 'Peminjaman — Admin')
@section('heading', 'Peminjaman & Pengembalian')
@section('subheading', 'Catat buku yang dikembalikan siswa.')
@section('panel')
    <div class="tabs">
        <a href="{{ route('admin.loans.index') }}" class="{{ $history ? '' : 'on' }}">Sedang Dipinjam</a>
        <a href="{{ route('admin.loans.index', ['tab' => 'riwayat']) }}" class="{{ $history ? 'on' : '' }}">Riwayat</a>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>{{ $history ? 'Riwayat Peminjaman' : 'Buku yang Sedang Dipinjam' }} ({{ number_format($loans->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Anggota</th><th>Judul Buku</th><th>Tgl Ambil</th><th>Jatuh Tempo</th><th>Status</th><th></th></tr>
            @forelse ($loans as $loan)
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
                <td>{{ $loan->pickup_date->translatedFormat('d M Y') }}</td>
                <td>{{ $loan->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>
                    @php
                        $cls = $loan->is_overdue ? 'late' : match($loan->status) {
                            'returned' => 'active', 'rejected' => 'late', default => 'due',
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $loan->status_label }}</span>
                    @if($loan->fine > 0)<div class="s" style="font-size:11px;color:var(--ink-soft);">Denda Rp {{ number_format($loan->fine, 0, ',', '.') }}</div>@endif
                </td>
                <td>
                    @unless($history)
                    <form method="POST" action="{{ route('admin.loans.return', $loan) }}" onsubmit="return confirm('Tandai buku ini sudah dikembalikan?');">
                        @csrf
                        <button type="submit" class="btn-solid btn-sm">Tandai Dikembalikan</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:24px 0;">{{ $history ? 'Belum ada riwayat.' : 'Tidak ada buku yang sedang dipinjam.' }}</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">{{ $loans->links('vendor.pagination.elibra') }}</div>
@endsection
