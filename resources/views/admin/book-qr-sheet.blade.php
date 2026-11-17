@extends('layouts.panel')
@section('title', 'Lembar QR Semua Buku')
@section('heading', 'Lembar QR Semua Buku')
@section('subheading', 'Cetak lalu gunting sebagai label untuk ditempel di buku.')
@section('action')
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.books') }}" class="btn-ghost" style="text-decoration:none;">← Kelola Buku</a>
        <button type="button" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;" data-print>Cetak lembar</button>
    </div>
@endsection
@section('panel')
    <div class="panel">
        @if($books->isEmpty())
            <p style="font-size:13px;color:var(--ink-soft);margin:0;">Belum ada buku. Tambahkan buku lebih dulu.</p>
        @else
            <div class="qr-sheet">
                @foreach($books as $b)
                    <div class="qr-label">
                        <div class="qr-mini" data-qr="{{ $b->qr_url }}" data-margin="1"></div>
                        <div class="lt">{{ \Illuminate\Support\Str::limit($b->title, 46) }}</div>
                        <div class="ls">{{ $b->shelf ? 'Rak '.$b->shelf.' · ' : '' }}{{ $b->qr_token }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <script src="{{ asset('js/qr.js') }}"></script>
    <script src="{{ asset('js/qr-init.js') }}"></script>
@endsection
