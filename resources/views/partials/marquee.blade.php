{{-- Pita kategori berjalan. Variabel: $categories (koleksi Category), boleh kosong. --}}
@php
    $items = ($categories ?? collect())->pluck('name')->all();
    if (!count($items)) {
        $items = ['Novel', 'Pelajaran', 'Referensi', 'Sains', 'Sejarah', 'Teknologi', 'Komik', 'Kamus'];
    }
@endphp
<div class="marquee" aria-label="Kategori buku">
    <div class="marquee-label">KATEGORI</div>
    <div class="marquee-view">
        <div class="marquee-track" aria-hidden="true">
            @for($copy = 0; $copy < 2; $copy++)
                <div class="marquee-set">
                    @foreach($items as $name)
                        <span class="mq-item">
                            <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                            {{ $name }}
                        </span>
                        <span class="mq-dot"></span>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>
</div>
