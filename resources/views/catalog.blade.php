@extends('layouts.app')
@section('title', 'Katalog Buku')

@section('content')
<div class="site-page">
        @include('partials.nav')
        <div class="kat-hero">
            @include('partials.floaters', ['tone' => 'light', 'count' => 6])
            <h1>Katalog Buku Perpustakaan</h1>
            <p>{{ number_format(\App\Models\Book::count()) }} judul tersedia — cari berdasarkan judul, penulis, atau kategori.</p>
            <form method="GET" action="{{ route('catalog') }}">
                @foreach(['category','sort','available_only'] as $keep)
                    @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
                @endforeach
                <div class="kat-searchbar">
                    <div class="kat-search-input">
                        <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul, penulis, atau ISBN..."
                               style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;color:inherit;">
                    </div>
                    <button type="submit" class="kat-search-btn">
                        <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="kat-body">
            <aside class="kat-side">
                <div class="sh">Kategori <a href="{{ route('catalog') }}" style="color:var(--pink-600);font-weight:700;font-size:11px;">Reset</a></div>
                <div class="filter-cat">
                    <a href="{{ route('catalog', request()->only('q','sort','available_only')) }}" style="text-decoration:none;">
                        <label class="{{ !request('category') ? 'active' : '' }}">
                            <div class="checkbox {{ !request('category') ? 'on' : '' }}">
                                @if(!request('category'))
                                    <svg width="10" height="10" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                                @endif
                            </div>
                            Semua Kategori
                        </label>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('catalog', array_merge(request()->only('q','sort','available_only'), ['category' => $cat->slug])) }}" style="text-decoration:none;">
                            <label class="{{ request('category') === $cat->slug ? 'active' : '' }}">
                                <div class="checkbox {{ request('category') === $cat->slug ? 'on' : '' }}">
                                    @if(request('category') === $cat->slug)
                                        <svg width="10" height="10" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                                    @endif
                                </div>
                                {{ $cat->name }}
                            </label>
                        </a>
                    @endforeach
                </div>
            </aside>

            <main class="kat-main">
                <div class="kat-toolbar">
                    <div class="res">
                        @if($books->total() > 0)
                            Menampilkan {{ number_format($books->firstItem()) }}–{{ number_format($books->lastItem()) }} dari {{ number_format($books->total()) }} buku
                        @else
                            Tidak ada buku yang cocok
                        @endif
                    </div>
                    <form method="GET" action="{{ route('catalog') }}" class="kat-filters">
                        @foreach(['q','category'] as $keep)
                            @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
                        @endforeach
                        <select name="sort" aria-label="Urutkan buku" onchange="this.form.submit()">
                            <option value="popular" @selected(request('sort','popular') === 'popular')>Terpopuler</option>
                            <option value="stock" @selected(request('sort') === 'stock')>Stok terbanyak</option>
                            <option value="title" @selected(request('sort') === 'title')>Judul A–Z</option>
                            <option value="newest" @selected(request('sort') === 'newest')>Terbaru</option>
                        </select>
                        <label class="chk"><input type="checkbox" name="available_only" value="1" @checked(request()->boolean('available_only')) onchange="this.form.submit()"> Hanya yang tersedia</label>
                        <button type="submit" class="kat-apply">Terapkan</button>
                    </form>
                </div>

                <div class="kat-grid">
                    @forelse($books as $book)
                        <a href="{{ route('books.show', $book) }}" style="text-decoration:none;color:inherit;">
                            <div class="kb-card">
                                @include('partials.cover', ['book' => $book, 'size' => 'card'])
                                <div class="kb-body">
                                    <div class="kb-a">{{ $book->author }}</div>
                                    <div class="kb-foot">
                                        <span class="kb-stock {{ $book->available_stock <= 2 ? 'low' : '' }}">
                                            @if($book->available_stock > 2)
                                                <svg class="ic" width="10" height="10" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                                Tersedia · {{ $book->available_stock }}
                                            @elseif($book->available_stock > 0)
                                                Sisa {{ $book->available_stock }}
                                            @else
                                                Tidak tersedia
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div style="grid-column:1/-1">@include('partials.empty', ['type'=>'search','title'=>'Buku tidak ditemukan','text'=>'Coba kata kunci lain atau atur ulang filter.','href'=>route('catalog'),'cta'=>'Reset pencarian'])</div>
                    @endforelse
                </div>

                <div class="pagination">
                    {{ $books->links('vendor.pagination.elibra') }}
                </div>
            </main>
        </div>
</div>
@endsection
