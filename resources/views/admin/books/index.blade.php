@extends('layouts.panel')
@section('title', 'Data Buku — Admin')
@section('heading', 'Data Buku')
@section('subheading', 'Tambah, ubah, atau hapus judul buku perpustakaan.')
@section('action')
    <a href="{{ route('admin.books.create') }}" class="btn-solid">+ Tambah Buku</a>
@endsection
@section('panel')
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.books.index') }}">
            <input type="text" name="q" value="{{ request('q') }}" class="inp" placeholder="Cari judul, penulis, atau ISBN">
            <button type="submit" class="btn-line">Cari</button>
            @if(request('q'))<a href="{{ route('admin.books.index') }}" class="btn-line">Reset</a>@endif
        </form>
    </div>

    <details class="panel import">
        <summary>Impor banyak buku sekaligus dari file CSV</summary>
        <form method="POST" action="{{ route('admin.books.import') }}" enctype="multipart/form-data" class="imp-body">
            @csrf
            <input type="file" name="file" accept=".csv,.txt" required>
            <button type="submit" class="btn-solid btn-sm">Impor</button>
            <a href="{{ route('admin.books.template') }}" class="btn-line btn-sm">Unduh template CSV</a>
            <span>Kolom: judul, penulis, kategori, isbn, rak, bahasa, deskripsi, stok. Judul + penulis yang sudah ada dilewati.</span>
        </form>
    </details>

    <div class="panel">
        <div class="panel-head"><h3>Semua Judul ({{ number_format($books->total()) }})</h3></div>
        <table class="dtable">
            <tr><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Rak</th><th>Tersedia</th><th>Total</th><th></th></tr>
            @forelse ($books as $book)
            <tr>
                <td><a href="{{ route('books.show', $book) }}" style="font-weight:700;">{{ $book->title }}</a>
                    @if($book->isbn)<div class="s" style="font-size:11px;color:var(--ink-soft);">ISBN {{ $book->isbn }}</div>@endif</td>
                <td>{{ $book->author }}</td>
                <td>{{ $book->category->name ?? '-' }}</td>
                <td>{{ $book->shelf ?: '-' }}</td>
                <td><span class="badge {{ $book->available_stock > 0 ? 'active' : 'late' }}">{{ $book->available_stock }}</span></td>
                <td>{{ $book->total_stock }}</td>
                <td>
                    <div class="row-acts">
                        <a href="{{ route('admin.books.edit', $book) }}" class="btn-line btn-sm">Ubah</a>
                        <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Hapus buku &quot;{{ addslashes($book->title) }}&quot;?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-line btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:24px 0;">Belum ada data buku. Klik “+ Tambah Buku” atau impor dari CSV.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">{{ $books->links('vendor.pagination.elibra') }}</div>
@endsection
