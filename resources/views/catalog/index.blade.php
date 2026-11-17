<div class="kat-grid">
      @forelse ($books as $book)
        <a href="{{ route('books.show', $book) }}" class="kb-card">
          <div class="kb-cover {{ $book->cover_theme }}">
            <span class="b">{{ $book->category->name }}</span>
            <span class="t">{{ $book->title }}</span>
          </div>
          <div class="kb-body">
            <div class="kb-a">{{ $book->author }}</div>
            <div class="kb-foot">
              <span class="kb-stock {{ !$book->isAvailable() ? 'low' : '' }}">
                <svg class="ic" width="10" height="10" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                {{ $book->isAvailable() ? 'Tersedia · '.$book->stock : 'Stok habis' }}
              </span>
              <span class="kb-btn">
                <svg class="ic" width="13" height="13" viewBox="0 0 24 24">
                  <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
              </span>
            </div>
          </div>
        </a>
      @empty
        <p>Tidak ada buku yang cocok dengan pencarianmu.</p>
      @endforelse
    </div>

  </div>
</div>
