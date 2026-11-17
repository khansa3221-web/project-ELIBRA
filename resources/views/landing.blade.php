@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<div class="site-page">
    @include('partials.nav')

    <div class="hero">
        @include('partials.floaters', ['tone' => 'light', 'count' => 10])
        <div class="hero-grid">
            <div>
                <div class="hero-badge">Perpustakaan digital sekolah</div>
                <h1>Pinjam buku sekolah <span class="accent">tanpa antre</span>, cek stok dari mana saja</h1>
                <p class="lead">Cari buku, ajukan peminjaman, lalu ambil di perpustakaan setelah disetujui petugas.</p>
                <div class="rotator" data-rotate="Discover Your Next Favorite Book|Explore Knowledge Without Limits|Read More, Learn More, Grow More|Your Digital Library Experience" aria-label="Discover Your Next Favorite Book">
                    <i class="rot-dot"></i><span class="rot-win" aria-hidden="true"><span class="rot-w in">Discover Your Next Favorite Book</span></span><i class="rot-caret" aria-hidden="true"></i>
                </div>
                <div class="hero-actions">
                    <a href="{{ route('catalog') }}" class="btn-solid">Mulai Cari Buku</a>
                    @guest
                        <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                    @endguest
                </div>
                <div class="hero-stats">
                    <div class="hstat"><div class="v">{{ number_format($stats['titles']) }}</div><div class="l">JUDUL BUKU</div></div>
                    <div class="hstat"><div class="v">{{ number_format($stats['members']) }}</div><div class="l">ANGGOTA</div></div>
                    <div class="hstat"><div class="v">{{ number_format($stats['loans']) }}</div><div class="l">PEMINJAMAN</div></div>
                </div>
            </div>
            <div class="mini-catalog">
                <div class="mini-grid">
                    @foreach($featuredBooks as $book)
                        <a href="{{ route('books.show', $book) }}">
                            @include('partials.cover', ['book' => $book, 'size' => 'mini'])
                            <div class="mini-cap">Tersedia · {{ $book->available_stock }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('partials.marquee', ['categories' => \App\Models\Category::orderBy('name')->get()])

    @if($categories->isNotEmpty())
    <div class="dl-sec">
        <div class="section-head">
            <div class="k">JELAJAHI</div>
            <h2>Temukan buku sesuai <span class="hl">minatmu</span></h2>
        </div>
        <div class="dl-cats">
            @foreach($categories as $cat)
                <a class="dl-cat" href="{{ route('catalog', ['category' => $cat->slug]) }}">
                    <div class="ico"><svg class="ic" width="20" height="20" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
                    <b>{{ $cat->name }}</b>
                    <span>{{ $cat->books_count }} buku</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($popularBooks->where('borrow_count', '>', 0)->isNotEmpty())
    <div class="dl-sec">
        <div class="section-head">
            <div class="k">TERPOPULER</div>
            <h2>Paling sering <span class="grad-anim">dipinjam</span></h2>
        </div>
        <div class="dl-pop">
            @foreach($popularBooks->where('borrow_count', '>', 0)->values() as $i => $book)
                <a href="{{ route('books.show', $book) }}">
                    <div class="dl-rank">{{ $i + 1 }}</div>
                    <div>
                        <div class="t">{{ $book->title }}</div>
                        <div class="a">{{ $book->author }}</div>
                        <span class="n">{{ $book->borrow_count }}× dipinjam</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @include('partials.features')

    <div class="section" style="padding-top:0;">
        <div class="steps-wrap">
            <div class="section-head" style="margin-bottom:44px;">
                <div class="k">CARA KERJA</div>
                <h2>Tiga langkah meminjam buku</h2>
            </div>
            <div class="steps-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="step-line" style="left:17%;right:17%;"></div>
                <div class="step">
                    <div class="sn">1</div>
                    <h4>Cari &amp; Pilih Buku</h4>
                    <p>Telusuri katalog berdasarkan judul, penulis, atau kategori.</p>
                </div>
                <div class="step">
                    <div class="sn">2</div>
                    <h4>Ajukan Peminjaman</h4>
                    <p>Pilih tanggal pinjam. Pengajuan dikirim ke petugas untuk diverifikasi.</p>
                </div>
                <div class="step">
                    <div class="sn">3</div>
                    <h4>Ambil di Perpustakaan</h4>
                    <p>Setelah disetujui, ambil buku sesuai tanggal yang kamu pilih.</p>
                </div>
            </div>
        </div>
    </div>

    @include('partials.cta-band')

    @include('partials.footer')
</div>
@endsection
