{{-- Aset <head>: dipakai layouts.app & layouts.dashboard. Tampilan tidak berubah; hanya cara muat yang lebih cepat. --}}
@php
    $fontUrl = 'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap';
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
{{-- Font dimuat tanpa memblokir tampilan halaman (teks langsung tampil dengan font cadangan, lalu berganti) --}}
<link rel="stylesheet" href="{{ $fontUrl }}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{{ $fontUrl }}"></noscript>
@if(\App\Support\Assets::bundled())
<link rel="stylesheet" href="{{ \App\Support\Assets::url('build/elibra.min.css') }}">
@else
@foreach(\App\Support\Assets::CSS as $css)
<link rel="stylesheet" href="{{ \App\Support\Assets::url($css) }}">
@endforeach
@endif
{{-- Preferensi animasi (kecil, harus jalan lebih dulu): disisipkan langsung agar tidak ada request tambahan --}}
<script>{!! \App\Support\Assets::inline('js/motion-pref.js') !!}</script>
