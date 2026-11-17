@extends('layouts.panel')
@section('title', 'Data Buku — Admin')
@section('heading', 'Data Buku')
@section('subheading', 'Tambah, ubah, hapus, atau impor buku perpustakaan dari file CSV.')
@section('action')
<div style="display:flex;gap:10px;">
    <a href="{{ route('admin.books.import') }}" class="btn-solid" style="background:#fff;color:var(--pink-700);border:1.5px solid var(--pink-200);box-shadow:none;">Impor CSV</a>
    <a href="{{ route('admin.books.create') }}" class="btn-solid">+ Tambah Buku</a>
</div>
@endsection
@section('panel')
<div class="panel">
    <div class="panel-head">
        <h3>Semua Judul ({{ number_format($books->total()) }})</h3>
        <form method="GET" action="{{ route('admin.books') }}" style="display:flex;gap:8px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul, penulis, ISBN..." class="input" style="width:240px;padding:9px 13px;font-size:12.5px;">
            <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;padding:9px 18px;">Cari</button>
        </form>
    </div>
    <table class="dtable">
        <tr><th>Cover</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Aksi</th></tr>
        @forelse ($books as $book)
        <tr>
            <td style="width:64px;">@include('partials.cover', ['book' => $book, 'size' => 'xs'])</td>
            <td><a href="{{ route('books.show', $book) }}" style="text-decoration:none;color:inherit;font-weight:700;">{{ $book->title }}</a></td>
            <td>{{ $book->author }}</td>
            <td>{{ $book->category->name ?? '-' }}</td>
            <td><span class="badge {{ $book->available_stock > 0 ? 'active' : 'late' }}">{{ $book->available_stock }} / {{ $book->total_stock }}</span></td>
            <td>
                <div class="act-cell">
                    <a href="{{ route('admin.books.edit', $book) }}" class="act-btn txt edit">Ubah</a>
                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Hapus buku ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="act-btn reject txt" style="border:none;cursor:pointer;">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="de-cell">@if(request('q'))@include('partials.empty', ['type'=>'search','title'=>'Buku tidak ditemukan','text'=>'Coba kata kunci lain.'])@else @include('partials.empty', ['type'=>'shelf','title'=>'Rak masih kosong','text'=>'Klik Tambah Buku atau Impor CSV untuk mengisi koleksi.'])@endif</td></tr>
        @endforelse
    </table>
</div>
<div class="pagination">
    {{ $books->links('vendor.pagination.elibra') }}
</div>
@endsection
