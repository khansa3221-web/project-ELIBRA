@extends('layouts.panel')
@section('title', 'Pengajuan Peminjaman — Admin')
@section('heading', 'Pengajuan Peminjaman')
@section('subheading', 'Verifikasi pengajuan peminjaman buku dari siswa.')
@section('panel')
    <div class="panel">
        <div class="panel-head"><h3>Menunggu Verifikasi ({{ number_format($loans->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Anggota</th><th>Judul Buku</th><th>Tgl Ambil</th><th>Jatuh Tempo</th><th>Diajukan</th><th></th></tr>
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
                <td>{{ $loan->created_at->diffForHumans() }}</td>
                <td>
                    <div class="act-cell">
                        <form method="POST" action="{{ route('admin.approve', $loan) }}">
                            @csrf
                            <button class="act-btn approve txt" style="border:none;cursor:pointer;"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reject', $loan) }}" style="display:flex;gap:6px;">
                            @csrf
                            <input type="text" name="reason" maxlength="255" placeholder="Alasan (opsional)" class="input" style="width:140px;padding:6px 10px;font-size:12px;">
                            <button class="act-btn reject txt" style="border:none;cursor:pointer;"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg> Tolak</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="de-cell">@include('partials.empty', ['type'=>'inbox','title'=>'Tidak ada pengajuan menunggu','text'=>'Kerja bagus! Semua pengajuan sudah diverifikasi.'])</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">
        {{ $loans->links('vendor.pagination.elibra') }}
    </div>

@endsection
