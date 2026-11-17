<div class="section">
    <div class="catalog-preview">
        <div class="cp-text">
            <div class="k">KATALOG DIGITAL</div>
            <h2>Telusuri ribuan judul tanpa harus ke rak buku</h2>
            <p>Filter berdasarkan kategori, cek sisa stok, dan lihat lokasi rak — semua sebelum siswa datang ke perpustakaan.</p>
            <div class="cp-list">
                <div class="cp-item">
                    <div class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                    <span>Filter kategori, pengarang, dan status ketersediaan</span>
                </div>
                <div class="cp-item">
                    <div class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                    <span>Informasi lokasi rak untuk mempercepat pencarian fisik</span>
                </div>
                <div class="cp-item">
                    <div class="ic"><svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                    <span>Ajukan peminjaman langsung dari halaman detail buku</span>
                </div>
            </div>
        </div>
        <div class="mini-catalog">
            <div class="mini-search">
                <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                Cari judul, penulis, ISBN...
            </div>
            <div class="mini-grid">
                @foreach($featuredBooks->take(6) as $book)
                    <div>
                        @include('partials.cover', ['book' => $book, 'size' => 'mini'])
                        <div class="mini-cap">Tersedia · {{ $book->available_stock }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>