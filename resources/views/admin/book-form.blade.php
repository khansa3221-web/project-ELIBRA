@extends('layouts.panel')
@section('title', 'Form Buku — Admin')
@section('heading', ($book->exists ? 'Ubah Buku' : 'Tambah Buku'))
@section('subheading', ($book->exists ? 'Perbarui data buku.' : 'Isi data buku yang akan ditambahkan ke perpustakaan.'))
@section('panel')
<div class="panel" style="max-width:760px;">
    <form method="POST" enctype="multipart/form-data" action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
        @csrf
        @if($book->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="span2">
                <div class="form-label" style="margin-top:0;">Judul Buku *</div>
                <input type="text" name="title" class="input" value="{{ old('title', $book->title) }}" required>
            </div>

            <div>
                <div class="form-label">Penulis *</div>
                <input type="text" name="author" class="input" value="{{ old('author', $book->author) }}" required>
            </div>
            <div>
                <div class="form-label">Kategori *</div>
                <input type="text" name="category" class="input" list="daftar-kategori"
                       value="{{ old('category', $book->category->name ?? '') }}" placeholder="Pilih atau ketik kategori baru" required>
                <datalist id="daftar-kategori">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div>
                <div class="form-label">Penerbit</div>
                <input type="text" name="publisher" class="input" value="{{ old('publisher', $book->publisher) }}">
            </div>
            <div>
                <div class="form-label">Tahun Terbit</div>
                <input type="number" name="year" class="input" min="1000" max="{{ date('Y') + 1 }}" value="{{ old('year', $book->year) }}">
            </div>

            <div>
                <div class="form-label">ISBN</div>
                <input type="text" name="isbn" class="input" value="{{ old('isbn', $book->isbn) }}">
            </div>
            <div>
                <div class="form-label">Lokasi Rak</div>
                <input type="text" name="shelf" class="input" value="{{ old('shelf', $book->shelf) }}" placeholder="Contoh: Rak F1">
            </div>

            <div>
                <div class="form-label">Bahasa</div>
                <input type="text" name="language" class="input" value="{{ old('language', $book->language) }}">
            </div>
            <div>
                <div class="form-label">Jumlah Eksemplar (Total Stok) *</div>
                <input type="number" name="total_stock" class="input" min="0" max="10000" value="{{ old('total_stock', $book->total_stock) }}" required>
                @if($book->exists)
                    <div class="hint">Stok tersedia saat ini: {{ $book->available_stock }}. Mengubah total stok ikut menyesuaikan stok tersedia.</div>
                @endif
            </div>

            <div class="span2">
                <div class="form-label">Cover Buku (JPG/PNG/WEBP, maks 2 MB, portrait 3:4 disarankan)</div>
                <div class="cover-field">
                    <div id="cover-preview">@include('partials.cover', ['book' => $book, 'size' => 'sm'])</div>
                    <div style="flex:1;min-width:200px;">
                        <input type="file" name="cover" id="cover-input" class="input" accept="image/jpeg,image/png,image/webp">
                        @error('cover')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
                        @if($book->exists && $book->cover_path)
                            <label class="chk" style="margin-top:10px;display:flex;gap:8px;align-items:center;font-size:12.5px;">
                                <input type="checkbox" name="remove_cover" value="1"> Hapus cover saat ini
                            </label>
                        @endif
                        <div class="hint">Unggah cover asli yang sesuai judul dan ISBN. Kosongkan bila belum ada: katalog akan menampilkan "Cover Belum Tersedia".</div>
                    </div>
                </div>
            </div>

            <div class="span2">
                <div class="form-label">Deskripsi (opsional)</div>
                <textarea name="description" class="input">{{ old('description', $book->description) }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:26px;align-items:center;">
            <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;">Simpan Buku</button>
            <a href="{{ route('admin.books') }}" class="btn-ghost">Batal</a>
        </div>
    </form>
</div>
<script>
document.getElementById('cover-input')?.addEventListener('change', function () {
    var f = this.files && this.files[0];
    if (!f) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024) {
        alert('Cover harus JPG, PNG, atau WEBP dan maksimal 2 MB.');
        this.value = '';
        return;
    }
    var img = document.createElement('img');
    img.alt = 'Pratinjau cover';
    img.src = URL.createObjectURL(f);
    var box = document.querySelector('#cover-preview .bk-cover');
    box.innerHTML = ''; box.appendChild(img);
});
</script>
@endsection
