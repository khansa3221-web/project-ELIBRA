<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-LIBRA') — Perpustakaan SMK Negeri 5 Surakarta</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extras.css') }}">
    <link rel="stylesheet" href="{{ asset('css/polish.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dreamy.css') }}">
    <link rel="stylesheet" href="{{ asset('css/motion-boost.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <script src="{{ asset('js/motion-pref.js') }}"></script>
</head>
<body>
    @yield('content')
<script src="{{ asset('js/animations.js') }}" defer></script>
<script src="{{ asset('js/extras.js') }}" defer></script>
<script src="{{ asset('js/polish.js') }}" defer></script>
<script src="{{ asset('js/dreamy.js') }}" defer></script>
<script src="{{ asset('js/motion-boost.js') }}" defer></script>
</body>
</html>