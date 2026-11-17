{{--
    Dekorasi melayang (ikon buku, bintang, pensil, dll.) — murni hiasan.
    Pemakaian:  @include('partials.floaters', ['tone' => 'light'|'dark', 'count' => 8])
    Taruh di dalam elemen yang position:relative / overflow:hidden.
--}}
@php
    $tone  = $tone ?? 'light';
    $count = min($count ?? 8, 12);
    $paths = [
        'book'     => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
        'star'     => '<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.5l-5.4 3 1.2-6L3.3 9.3l6.1-.7z"/>',
        'bookmark' => '<path d="M6 3h12v18l-6-4-6 4z"/>',
        'pencil'   => '<path d="M4 20l1-4L16 5l3 3L8 19z"/><path d="M14 7l3 3"/>',
        'spark'    => '<path d="M12 3v5M12 16v5M3 12h5M16 12h5"/>',
        'cap'      => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/>',
        'heart'    => '<path d="M12 20s-7-4.4-7-10a4 4 0 017-2.5A4 4 0 0119 10c0 5.6-7 10-7 10z"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>',
    ];
    $names = array_keys($paths);
    // x, y (%), ukuran (px), durasi (dtk), jeda (dtk), rotasi (deg)
    $slots = [
        [6, 10, 30, 7.0, -1.0, -12], [88, 8, 24, 8.5, -3.0, 14], [14, 72, 26, 9.0, -2.0, 8],
        [92, 62, 32, 7.5, -4.5, -10], [48, 4, 20, 10, -5.0, 0],   [72, 86, 22, 8.0, -1.5, 18],
        [30, 38, 18, 11, -6.0, -6],   [60, 48, 16, 9.5, -2.5, 10], [4, 44, 22, 8.8, -3.5, 12],
        [80, 30, 18, 10.5, -7.0, -14],[38, 90, 24, 9.2, -0.5, -8], [56, 20, 14, 12, -8.0, 6],
    ];
@endphp
<div class="floaters floaters-{{ $tone }}" aria-hidden="true">
    @for($i = 0; $i < $count; $i++)
        @php [$x, $y, $s, $d, $dl, $r] = $slots[$i]; $n = $names[$i % count($names)]; @endphp
        <i class="fl" style="--x:{{ $x }}%;--y:{{ $y }}%;--s:{{ $s }}px;--d:{{ $d }}s;--dl:{{ $dl }}s;--r:{{ $r }}deg;">
            <svg class="ic" viewBox="0 0 24 24" width="100%" height="100%">{!! $paths[$n] !!}</svg>
        </i>
    @endfor
</div>
