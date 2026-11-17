{{-- Panel ilustrasi kiri untuk login & registrasi. $title (array kata), $tagline --}}
@php
    $book = fn ($a, $b) => '<svg viewBox="0 0 64 80" aria-hidden="true"><rect x="6" y="4" width="50" height="72" rx="7" fill="'.$a.'"/><rect x="6" y="4" width="10" height="72" rx="5" fill="'.$b.'" opacity=".55"/><rect x="24" y="22" width="26" height="5" rx="2.5" fill="#fff" opacity=".85"/><rect x="24" y="33" width="18" height="4" rx="2" fill="#fff" opacity=".6"/><path d="M42 4v26l5-5 5 5V4z" fill="#fff" opacity=".9"/></svg>';
    $star = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c.7 6.2 3.8 9.3 12 12-8.2 2.7-11.3 5.8-12 12-.7-6.2-3.8-9.3-12-12 8.2-2.7 11.3-5.8 12-12z"/></svg>';
@endphp
<aside class="au-art" aria-hidden="false">
    <span class="au-blob b1"></span><span class="au-blob b2"></span><span class="au-blob b3"></span>
    <span class="au-ring r1"></span><span class="au-ring r2"></span>

    @foreach([['9%','14%','20px','0s'],['82%','10%','14px','-1.2s'],['90%','46%','22px','-2.1s'],['6%','58%','16px','-0.6s'],['18%','86%','20px','-1.8s'],['74%','82%','15px','-2.6s'],['48%','6%','12px','-3s']] as [$l,$t,$s,$d])
        <span class="au-spark" style="left:{{ $l }};top:{{ $t }};--s:{{ $s }};--d:{{ $d }}">{!! $star !!}</span>
    @endforeach

    <div class="au-scene" role="img" aria-label="Ilustrasi tumpukan buku perpustakaan digital">
        <span class="au-float f1">{!! $book('#E58AAB', '#C4527F') !!}</span>
        <span class="au-float f2">{!! $book('#2B2327', '#5B4B53') !!}</span>
        <span class="au-float f3">{!! $book('#FFFFFF', '#F3A9C3') !!}</span>
        <span class="au-float f4">{!! $book('#F7B9D0', '#E58AAB') !!}</span>
        <div class="au-stack"><span class="au-book"></span><span class="au-book"></span><span class="au-book"></span><span class="au-book"></span></div>
    </div>

    <div class="au-copy">
        <h1>@foreach($title as $i => $w)<span class="au-reveal"><span style="--i:{{ $i }}">{!! $w !!}</span></span> @endforeach</h1>
        <p class="au-up" style="--d:.7s">{{ $tagline }}</p>
        <div class="au-chips au-up" style="--d:.9s"><span>Katalog digital</span><span>Pinjam tanpa antre</span><span>E-book &amp; QR</span></div>
    </div>
</aside>
