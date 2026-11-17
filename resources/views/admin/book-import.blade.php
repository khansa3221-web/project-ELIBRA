@extends('layouts.panel')
@section('title', 'Impor Buku — Admin')
@section('heading', 'Impor Buku dari CSV')
@section('subheading', 'Masukkan banyak buku sekaligus dari daftar buku perpustakaan di Excel.')
@section('action')
<a href="{{ route('admin.books') }}" class="btn-solid" style="background:#fff;color:var(--pink-700);border:1.5px solid var(--pink-200);box-shadow:none;">← Kembali</a>
@endsection
@section('panel')
<div class="panel" style="max-width:760px;">
    <div class="panel-head"><h3>Cara mengimpor</h3></div>
    <ol style="margin:0 0 20px;padding-left:20px;font-size:13px;line-height:1.9;">
        <li>Unduh <a href="{{ route('admin.books.template') }}" style="color:var(--pink-700);font-weight:800;">template-data-buku.csv</a> lalu buka di Excel.</li>
        <li>Isi satu baris untuk satu buku. Kolom <b>judul</b> dan <b>penulis</b> wajib. Kategori kosong menjadi "Umum", stok kosong menjadi 1.</li>
        <li>Simpan sebagai <b>CSV</b> (Excel: File → Save As → <i>CSV UTF-8</i> atau <i>CSV (Comma delimited)</i>).</li>
        <li>Unggah file di bawah ini.</li>
    </ol>
    <div class="hint" style="margin:0 0 22px;">
        Kolom yang dikenali: judul, penulis, kategori, isbn, rak, bahasa, stok, deskripsi.
        Format kolom <b>ISBN sebagai Teks</b> agar Excel tidak mengubahnya jadi 9.79E+12.
        Buku yang sudah ada (judul + penulis sama, atau ISBN sama) otomatis dilewati, jadi aman bila file diunggah dua kali.
    </div>

    <form method="POST" action="{{ route('admin.books.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-label" style="margin-top:0;">File CSV</div>
        <input type="file" name="file" accept=".csv,.txt" class="input" required>
        <label style="display:flex;gap:10px;align-items:flex-start;margin-top:18px;font-size:13px;cursor:pointer;line-height:1.5;">
            <input type="checkbox" name="replace" value="1" style="margin-top:3px;">
            <span><b>Hapus semua buku lama dulu</b> (berguna untuk mengganti data contoh dengan data asli sekolah). Buku yang sedang dipinjam tidak ikut dihapus.</span>
        </label>
        <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;margin-top:22px;">Impor Sekarang</button>
    </form>
</div>

@if(session('import_errors'))
    <div class="panel" style="max-width:760px;">
        <div class="panel-head"><h3>Catatan Impor</h3></div>
        <ul style="margin:0;padding-left:18px;font-size:12.5px;color:var(--ink-soft);line-height:1.9;">
            @foreach(session('import_errors') as $catatan)
                <li>{{ $catatan }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
