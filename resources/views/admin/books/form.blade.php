@extends('layouts.panel')
@section('title', $book->exists ? 'Ubah Buku' : 'Tambah Buku')
@section('heading', ($book->exists ? 'Ubah Buku' : 'Tambah Buku'))
@section('subheading', ($book->exists ? $book->title : 'Isi data buku sesuai koleksi perpustakaan.'))
@section('action')
    <a href="{{ route('admin.books.index') }}" class="btn-line">← Kembali</a>
@endsection
@section('panel')
<div class="panel">
    <form method="POST" action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
        @csrf
        @if($book->exists) @method('PUT') @endif
        <div class="fgrid">
            <div class="full">
                <div class="form-label">Judul *</div>
                <input type="text" name="title" value="{{ old('title', $book->title) }}" class="inp" required>
            </div>
            <div>
                <div class="form-label">Penulis *</div>
                <input type="text" name="author" value="{{ old('author', $book->author) }}" class="inp" required>
            </div>
            <div>
                <div class="form-label">ISBN</div>
                <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="inp">
            </div>
            <div>
                <div class="form-label">Kategori</div>
                <select name="category_id" class="inp">
                    <option value="">— pilih —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected((string) old('category_id', $book->category_id) === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="form-label">Atau buat kategori baru</div>
                <input type="text" name="new_category" value="{{ old('new_category') }}" class="inp" placeholder="Mis. Komputer & Jaringan">
                <div class="fhint">Jika diisi, ini dipakai menggantikan pilihan di samping.</div>
            </div>
            <div>
                <div class="form-label">Lokasi Rak</div>
                <input type="text" name="shelf" value="{{ old('shelf', $book->shelf) }}" class="inp" placeholder="Mis. Rak A2">
            </div>
            <div>
                <div class="form-label">Bahasa</div>
                <input type="text" name="language" value="{{ old('language', $book->language ?: 'Indonesia') }}" class="inp">
            </div>
            <div>
                <div class="form-label">Total Stok (eksemplar) *</div>
                <input type="number" name="total_stock" min="0" value="{{ old('total_stock', $book->total_stock) }}" class="inp" required>
                @if($book->exists)
                    <div class="fhint">Sedang dipinjam: {{ $book->borrowedCount() }} · tersedia dihitung otomatis.</div>
                @endif
            </div>
            <div>
                <div class="form-label">Warna Sampul</div>
                <select name="cover_gradient" class="inp">
                    <option value="">Otomatis</option>
                    @foreach(['mc1' => 'Merah muda', 'mc2' => 'Oranye', 'mc3' => 'Ungu', 'mc4' => 'Hijau', 'mc5' => 'Magenta', 'mc6' => 'Biru'] as $v => $label)
                        <option value="{{ $v }}" @selected(old('cover_gradient', $book->cover_gradient) === $v)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="full">
                <div class="form-label">Deskripsi</div>
                <textarea name="description" class="inp" rows="4">{{ old('description', $book->description) }}</textarea>
            </div>
        </div>
        <div style="margin-top:22px;display:flex;gap:10px;">
            <button type="submit" class="btn-solid">{{ $book->exists ? 'Simpan Perubahan' : 'Simpan Buku' }}</button>
            <a href="{{ route('admin.books.index') }}" class="btn-line">Batal</a>
        </div>
    </form>
</div>
@endsection
