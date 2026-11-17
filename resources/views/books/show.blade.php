@extends('layouts.app')
@section('title', $book->title)

@section('content')
<div class="site-page">
        @include('partials.nav')

        <div class="detail-wrap">
            @include('partials.cover', ['book' => $book, 'size' => 'detail'])

            <div class="detail-info">
                <div class="k">
                    {{ strtoupper($book->category->name) }} ·
                    {{ $book->available_stock > 0 ? 'TERSEDIA' : 'TIDAK TERSEDIA' }}
                </div>
                <h1>{{ $book->title }}</h1>
                <div class="author">{{ $book->author }}</div>

                <div class="detail-meta">
                    <div class="dm"><div class="l">PENERBIT</div><div class="v">{{ $book->publisher ?? '-' }}</div></div>
                    <div class="dm"><div class="l">TAHUN</div><div class="v">{{ $book->year ?? '-' }}</div></div>
                    <div class="dm"><div class="l">ISBN</div><div class="v">{{ $book->isbn ?? '-' }}</div></div>
                    <div class="dm"><div class="l">RAK</div><div class="v">{{ $book->shelf ?? '-' }}</div></div>
                    <div class="dm"><div class="l">STOK</div><div class="v">{{ $book->available_stock }} dari {{ $book->total_stock }} eksemplar</div></div>
                    <div class="dm"><div class="l">BAHASA</div><div class="v">{{ $book->language }}</div></div>
                </div>

                <p class="detail-desc">{{ $book->description }}</p>

            </div>

            <div class="borrow-panel">
                <div class="panel">
                    @auth
                        @if(auth()->user()->isSiswa())
                            <form method="POST" action="{{ route('books.borrow', $book) }}">
                                @csrf
                                <div class="avail-line">
                                    @if($book->available_stock > 0)
                                        <svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                        Tersedia untuk dipinjam
                                    @else
                                        <span style="color:var(--pink-700);">Stok habis</span>
                                    @endif
                                </div>

                                @if($errors->any())
                                    <div style="color:var(--pink-700);font-size:11.5px;font-weight:700;margin-bottom:12px;">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <div class="form-label">Tanggal Ambil</div>
                                <div class="form-field">
                                    <input type="date" name="pickup_date" required min="{{ now()->toDateString() }}"
                                           value="{{ now()->toDateString() }}"
                                           style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;">
                                </div>

                                <div class="form-label">Lama Peminjaman (hari)</div>
                                <div class="form-field">
                                    <input type="number" name="duration_days" min="1" max="30" value="7" required
                                           style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;">
                                </div>

                                <div class="form-label">Catatan (opsional)</div>
                                <div class="form-field" style="align-items:flex-start;height:64px;">
                                    <textarea name="note" placeholder="Untuk tugas Bahasa Indonesia..."
                                              style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:12.5px;resize:none;height:100%;"></textarea>
                                </div>

                                <button type="submit" class="btn-primary-lg" style="border:none;cursor:pointer;width:100%;font-family:inherit;">
                                    Ajukan Peminjaman
                                    <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </button>
                            </form>
                        @else
                            <p style="font-size:12px;color:var(--ink-soft);margin:0;">Login sebagai siswa untuk mengajukan peminjaman.</p>
                        @endif
                    @else
                        <div class="avail-line">
                            <svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                            @if($book->available_stock > 0)Tersedia untuk dipinjam@else<span style="color:var(--pink-700);">Stok habis</span>@endif
                        </div>
                        <a href="{{ route('login') }}" class="btn-primary-lg" style="text-decoration:none;display:flex;">Login untuk meminjam</a>
                    @endauth
                </div>
            </div>
        </div>

        @if($related->count())
            <div class="section">
                <div class="section-head" style="margin-bottom:26px;">
                    <div class="k">BUKU TERKAIT</div>
                    <h2 style="font-size:24px;">Judul lain dari kategori {{ $book->category->name }}</h2>
                </div>
                <div class="kat-grid kat-related">
                    @foreach($related as $b)
                        <a href="{{ route('books.show', $b) }}" style="text-decoration:none;color:inherit;">
                            <div class="kb-card">
                                @include('partials.cover', ['book' => $b, 'size' => 'card'])
                                <div class="kb-body">
                                    <div class="kb-a">{{ $b->author }}</div>
                                    <div class="kb-foot">
                                        <span class="kb-stock">Tersedia · {{ $b->available_stock }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
</div>
@endsection
