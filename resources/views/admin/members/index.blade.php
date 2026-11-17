@extends('layouts.panel')
@section('title', 'Anggota — Admin')
@section('heading', 'Anggota Perpustakaan')
@section('subheading', 'Daftar siswa yang bisa meminjam buku.')
@section('action')
    <a href="{{ route('admin.members.create') }}" class="btn-solid">+ Tambah Anggota</a>
@endsection
@section('panel')
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.members.index') }}">
            <input type="text" name="q" value="{{ request('q') }}" class="inp" placeholder="Cari nama, NIS, atau kelas">
            <button type="submit" class="btn-line">Cari</button>
            @if(request('q'))<a href="{{ route('admin.members.index') }}" class="btn-line">Reset</a>@endif
        </form>
    </div>

    <details class="panel import">
        <summary>Impor data siswa dari file CSV</summary>
        <form method="POST" action="{{ route('admin.members.import') }}" enctype="multipart/form-data" class="imp-body">
            @csrf
            <input type="file" name="file" accept=".csv,.txt" required>
            <button type="submit" class="btn-solid btn-sm">Impor</button>
            <a href="{{ route('admin.members.template') }}" class="btn-line btn-sm">Unduh template CSV</a>
            <span>Kolom: nama, nis, kelas, email (opsional), password (opsional). Jika password kosong, kata sandi awal = NIS. Siswa login memakai NIS.</span>
        </form>
    </details>

    <div class="panel">
        <div class="panel-head"><h3>Semua Anggota ({{ number_format($members->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Email</th><th>Peminjaman</th><th></th></tr>
            @forelse ($members as $member)
            <tr>
                <td>
                    <div class="row-p">
                        <div class="av">{{ strtoupper(substr($member->name, 0, 2)) }}</div>
                        <div class="t">{{ $member->name }}</div>
                    </div>
                </td>
                <td>{{ $member->nis ?? '-' }}</td>
                <td>{{ $member->class ?? '-' }}</td>
                <td>{{ str_ends_with($member->email, '@siswa.elibra.test') ? '—' : $member->email }}</td>
                <td>{{ $member->loans_count }}</td>
                <td>
                    <div class="row-acts">
                        <a href="{{ route('admin.members.edit', $member) }}" class="btn-line btn-sm">Ubah</a>
                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}" onsubmit="return confirm('Hapus anggota &quot;{{ addslashes($member->name) }}&quot; beserta riwayat peminjamannya?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-line btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:24px 0;">Belum ada anggota. Klik “+ Tambah Anggota” atau impor dari CSV.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">{{ $members->links('vendor.pagination.elibra') }}</div>
@endsection
