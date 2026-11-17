{{--
    Empty state berilustrasi, reusable. Ilustrasi disesuaikan dengan fungsi halaman.
    @include('partials.empty', ['type' => 'history|bell|shelf|search|inbox|people|chart|loan', 'title' => '...', 'text' => '...', 'href' => url, 'cta' => 'label'])
    Murni SVG + CSS (gaya di public/css/dreamy.css, bagian "V2").
--}}
@php
    $type  = $type ?? 'inbox';
    $title = $title ?? 'Belum ada data';
    $text  = $text ?? '';
@endphp
<div class="dl-empty2" data-empty="{{ $type }}">
    <div class="de-art" aria-hidden="true">
        <i class="de-blob"></i>
        <svg viewBox="0 0 220 150" class="de-svg" focusable="false">
            <defs>
                <linearGradient id="deg1-{{ $type }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#A0CDD4"/><stop offset="1" stop-color="#279CA4"/></linearGradient>
                <linearGradient id="deg2-{{ $type }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFFFFF"/><stop offset="1" stop-color="#EAEFF1"/></linearGradient>
            </defs>
            <ellipse cx="110" cy="136" rx="74" ry="8" fill="#2A4B84" opacity=".12"/>
            @switch($type)
                @case('history')
                    <g class="de-bob">
                        <rect x="52" y="86" width="116" height="30" rx="8" fill="url(#deg1-{{ $type }})"/>
                        <rect x="60" y="106" width="100" height="5" rx="2.5" fill="#fff" opacity=".8"/>
                        <rect x="62" y="56" width="96" height="30" rx="8" fill="url(#deg2-{{ $type }})" stroke="#BBDCE0"/>
                    </g>
                    <g class="de-spin-wrap"><circle cx="150" cy="44" r="26" fill="#fff" stroke="#A0CDD4" stroke-width="3"/>
                        <path d="M150 28v16l11 6" fill="none" stroke="#279CA4" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" class="de-hand"/></g>
                    @break
                @case('bell')
                    <g class="de-ring-bell">
                        <path d="M110 30c-20 0-32 15-32 34v18l-9 14h82l-9-14V64c0-19-12-34-32-34z" fill="url(#deg1-{{ $type }})"/>
                        <circle cx="110" cy="26" r="6" fill="#A0CDD4"/>
                        <path d="M99 100a11 11 0 0022 0z" fill="#279CA4"/>
                    </g>
                    <path d="M62 60c-6 8-6 20 0 28M158 60c6 8 6 20 0 28" fill="none" stroke="#A0CDD4" stroke-width="3" stroke-linecap="round" class="de-wave"/>
                    <circle cx="150" cy="38" r="7" fill="#fff" stroke="#A0CDD4" stroke-width="2.5"/>
                    @break
                @case('shelf')
                    <rect x="34" y="112" width="152" height="8" rx="4" fill="#279CA4" opacity=".85"/>
                    <g class="de-bob">
                        <rect x="46" y="58" width="18" height="54" rx="4" fill="url(#deg1-{{ $type }})"/>
                        <rect x="68" y="70" width="16" height="42" rx="4" fill="#BBDCE0"/>
                        <rect x="88" y="52" width="20" height="60" rx="4" fill="url(#deg2-{{ $type }})" stroke="#BBDCE0"/>
                        <rect x="112" y="66" width="16" height="46" rx="4" fill="#13223B"/>
                        <rect x="132" y="60" width="22" height="52" rx="4" fill="url(#deg1-{{ $type }})"/>
                    </g>
                    <g class="de-tilt-book"><rect x="160" y="78" width="16" height="34" rx="4" fill="#A0CDD4"/></g>
                    @break
                @case('search')
                    <g class="de-bob">
                        <rect x="58" y="48" width="84" height="68" rx="10" fill="url(#deg2-{{ $type }})" stroke="#BBDCE0" stroke-width="2"/>
                        <rect x="70" y="62" width="48" height="6" rx="3" fill="#BBDCE0"/><rect x="70" y="76" width="36" height="6" rx="3" fill="#DFE7EB"/><rect x="70" y="90" width="42" height="6" rx="3" fill="#DFE7EB"/>
                    </g>
                    <g class="de-scan"><circle cx="146" cy="70" r="24" fill="rgba(255,255,255,.7)" stroke="url(#deg1-{{ $type }})" stroke-width="7"/><path d="M164 88l18 18" stroke="#279CA4" stroke-width="8" stroke-linecap="round"/></g>
                    @break
                @case('people')
                    <g class="de-bob">
                        <circle cx="86" cy="62" r="16" fill="url(#deg1-{{ $type }})"/><path d="M58 112c0-18 12-28 28-28s28 10 28 28z" fill="url(#deg1-{{ $type }})"/>
                        <circle cx="142" cy="68" r="13" fill="#BBDCE0"/><path d="M120 112c0-15 9-24 22-24s22 9 22 24z" fill="#BBDCE0"/>
                    </g>
                    <path d="M170 34v16M162 42h16" stroke="#4DCCD5" stroke-width="4" stroke-linecap="round" class="de-plus"/>
                    @break
                @case('chart')
                    <g>
                        <rect x="56" y="84" width="22" height="32" rx="5" fill="#BBDCE0" class="de-bar" style="--i:0"/>
                        <rect x="88" y="62" width="22" height="54" rx="5" fill="url(#deg1-{{ $type }})" class="de-bar" style="--i:1"/>
                        <rect x="120" y="74" width="22" height="42" rx="5" fill="#A0CDD4" class="de-bar" style="--i:2"/>
                        <rect x="152" y="48" width="22" height="68" rx="5" fill="url(#deg2-{{ $type }})" stroke="#BBDCE0" class="de-bar" style="--i:3"/>
                    </g>
                    @break
                @default {{-- inbox / loan --}}
                    <g class="de-bob">
                        <path d="M56 78l18-30h72l18 30v34a8 8 0 01-8 8H64a8 8 0 01-8-8z" fill="url(#deg2-{{ $type }})" stroke="#BBDCE0" stroke-width="2"/>
                        <path d="M56 78h32c3 10 9 14 22 14s19-4 22-14h32" fill="#EAEFF1" stroke="#BBDCE0" stroke-width="2"/>
                    </g>
                    <g class="de-fly"><rect x="94" y="22" width="32" height="40" rx="5" fill="url(#deg1-{{ $type }})"/><rect x="100" y="30" width="20" height="4" rx="2" fill="#fff" opacity=".85"/><rect x="100" y="38" width="14" height="4" rx="2" fill="#fff" opacity=".6"/></g>
            @endswitch
            <path class="de-sp de-sp1" d="M36 40l2.4 6.6L45 49l-6.6 2.4L36 58l-2.4-6.6L27 49l6.6-2.4z" fill="#4DCCD5"/>
            <path class="de-sp de-sp2" d="M190 96l2 5.4 5.4 2-5.4 2-2 5.4-2-5.4-5.4-2 5.4-2z" fill="#A0CDD4"/>
            <circle class="de-sp de-sp3" cx="184" cy="34" r="3.5" fill="#BBDCE0"/>
        </svg>
    </div>
    <h4>{{ $title }}</h4>
    @if($text)<p>{{ $text }}</p>@endif
    @isset($href)
        <a href="{{ $href }}" class="btn-solid de-cta">{{ $cta ?? 'Lihat katalog' }}
            <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    @endisset
</div>
