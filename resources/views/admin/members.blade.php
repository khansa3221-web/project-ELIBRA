@extends('layouts.panel')
@section('title', 'Anggota — Admin')
@section('heading', 'Anggota Perpustakaan')
@section('subheading', 'Daftar siswa yang terdaftar sebagai anggota.')
@section('panel')
<div class="panel">
        <div class="panel-head"><h3>Semua Anggota ({{ number_format($members->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Email</th><th>Total Peminjaman</th></tr>
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
                <td>{{ $member->email }}</td>
                <td>{{ $member->loans()->count() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="de-cell">@include('partials.empty', ['type'=>'people','title'=>'Belum ada anggota terdaftar','text'=>'Siswa yang mendaftar akan muncul di sini.'])</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">
        {{ $members->links('vendor.pagination.elibra') }}
    </div>

@endsection
