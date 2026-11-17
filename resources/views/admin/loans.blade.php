@extends('layouts.panel')
@section('title', 'Peminjaman — Admin')
@section('heading', 'Peminjaman & Pengembalian')
@section('subheading', 'Pantau buku yang sedang dipinjam dan proses pengembaliannya.')
@section('panel')
    @php
        $tabs = ['berjalan' => 'Sedang dipinjam', 'terlambat' => 'Terlambat', 'selesai' => 'Dikembalikan', 'ditolak' => 'Ditolak', 'semua' => 'Semua'];
    @endphp
    <div class="panel">
        <div class="panel-head">
            <div class="seg">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.loans', array_filter(['status' => $key, 'q' => request('q')])) }}" class="{{ $filter === $key ? 'on' : '' }}">
                        {{ $label }}@if(isset($counts[$key]) && $counts[$key] > 0) <span class="n">{{ $counts[$key] }}</span>@endif
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.loans') }}" style="display:flex;gap:8px;">
                <input type="hidden" name="status" value="{{ $filter }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari siswa, NIS, atau judul..." class="input" style="width:230px;padding:9px 13px;font-size:12.5px;">
                <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;padding:9px 18px;">Cari</button>
            </form>
        </div>

        <table class="dtable">
            <tr><th>Anggota</th><th>Judul Buku</th><th>Tgl Ambil</th><th>Jatuh Tempo</th><th>Status</th><th>Denda</th><th></th></tr>
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
                        $cls = match($loan->status) {
                            'pending' => 'pending', 'approved', 'active' => 'active',
                            'late', 'rejected' => 'late', 'returned' => 'ok', default => '',
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $loan->status_label }}@if($loan->status === 'late') · {{ $loan->late_days }} hr @endif</span>
                </td>
                <td>{{ $loan->current_fine > 0 ? 'Rp'.number_format($loan->current_fine, 0, ',', '.') : '-' }}</td>
                <td>
                    @if (in_array($loan->status, \App\Models\Loan::HOLDING_STATUSES, true))
                        <form method="POST" action="{{ route('admin.loans.return', $loan) }}" onsubmit="return confirm('Tandai buku \'{{ addslashes($loan->book->title) }}\' sudah dikembalikan?')">
                            @csrf
                            <button type="submit" class="act-btn approve txt" style="border:none;cursor:pointer;">
                                <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Kembalikan
                            </button>
                        </form>
                    @else
                        <span style="font-size:12px;color:var(--ink-soft2);">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="de-cell">@include('partials.empty', ['type'=>'loan','title'=>'Tidak ada data pada kategori ini','text'=>'Coba pilih filter status yang lain.'])</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">
        {{ $loans->links('vendor.pagination.elibra') }}
    </div>
@endsection
