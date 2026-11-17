{{-- Banner sapaan di dashboard. Variabel: $tips (array kalimat yang diketik bergantian). --}}
<div class="hello-banner">
    @include('partials.floaters', ['tone' => 'dark', 'count' => 7])
    <div class="hb-main">
        <div class="hb-k"><span class="hb-dot"></span> <span data-today>Hari ini</span></div>
        <h2 class="hb-title" data-txt>Selamat <span data-greet>datang</span>!</h2>
        <p class="hb-tip"><span class="hb-tip-label">Tips:</span> <span data-typed='@json($tips)'>{{ $tips[0] ?? '' }}</span></p>
    </div>
    <div class="hb-clock" aria-hidden="true">
        <div class="hb-time" data-clock>--:--</div>
        <div class="hb-sub">waktu setempat</div>
    </div>
</div>
