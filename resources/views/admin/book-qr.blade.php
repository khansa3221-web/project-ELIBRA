@extends('layouts.panel')
@section('title', 'QR Code — '.$book->title)
@section('heading', 'QR Code Buku')
@section('subheading', 'Pindai QR ini untuk membuka halaman detail buku. QR tetap sama walau judul diubah.')
@section('action')
    <a href="{{ route('admin.books') }}" class="btn-ghost" style="text-decoration:none;">← Kembali ke Kelola Buku</a>
@endsection
@section('panel')
    <div class="panel qr-print-area">
        <div class="qr-detail">
            <div class="qr-big" id="qr-main" data-qr="{{ $book->qr_url }}" data-margin="3"></div>
            <div class="qr-info">
                <div class="k">{{ strtoupper($book->category->name ?? '') }}</div>
                <h2>{{ $book->title }}</h2>
                <div class="a">{{ $book->author }}</div>
                <dl>
                    <div><dt>ISBN</dt><dd>{{ $book->isbn ?? '-' }}</dd></div>
                    <div><dt>Rak</dt><dd>{{ $book->shelf ?? '-' }}</dd></div>
                    <div><dt>Kode QR</dt><dd>{{ $book->qr_token }}</dd></div>
                </dl>
                <div class="qr-url">{{ $book->qr_url }}</div>
                <div class="qr-actions no-print">
                    <button type="button" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;" data-qr-download="png" data-target="#qr-main" data-filename="qr-{{ $book->slug }}">Unduh PNG</button>
                    <button type="button" class="btn-ghost" style="cursor:pointer;font-family:inherit;" data-qr-download="svg" data-target="#qr-main" data-filename="qr-{{ $book->slug }}">Unduh SVG</button>
                    <button type="button" class="btn-ghost" style="cursor:pointer;font-family:inherit;" data-print>Cetak</button>
                    <button type="button" class="btn-ghost" style="cursor:pointer;font-family:inherit;" data-copy="{{ $book->qr_url }}">Salin tautan</button>
                </div>
                <p class="qr-note no-print">Tautan memakai alamat situs yang sedang kamu buka. Cetak label setelah website dibuka lewat alamat yang akan dipakai siswa.</p>
            </div>
        </div>
    </div>
    <div class="no-print" style="margin-top:14px;"><a href="{{ route('admin.books.qr.sheet') }}" class="more" style="font-size:13px;font-weight:800;">Cetak lembar QR semua buku →</a></div>
    <script src="{{ asset('js/qr.js') }}"></script>
    <script src="{{ asset('js/qr-init.js') }}"></script>
@endsection
