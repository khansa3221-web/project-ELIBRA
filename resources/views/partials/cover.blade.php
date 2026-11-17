@props(['book', 'size' => 'card'])
{{-- Cover buku portrait 3:4. Gambar asli bila ada; jika tidak: tulisan "Cover Belum Tersedia" (tanpa cover palsu). --}}
<div class="bk-cover bk-{{ $size }}">
    @if($book->cover_url)
        <img src="{{ $book->cover_url }}" alt="Cover buku {{ $book->title }}" loading="lazy" decoding="async">
    @else
        <div class="bk-empty" role="img" aria-label="Cover belum tersedia untuk {{ $book->title }}">
            <svg class="ic" width="26" height="26" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            <span>Cover Belum Tersedia</span>
        </div>
    @endif
</div>
