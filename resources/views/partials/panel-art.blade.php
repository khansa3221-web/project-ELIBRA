{{--
    Ornamen header panel: kartu kaca melayang + ikon sesuai halaman. Murni dekorasi (aria-hidden), disembunyikan di layar kecil.
    Dipilih otomatis dari nama route; halaman tanpa pemetaan memakai ikon buku.
--}}
@php
    $r = request()->route()?->getName() ?? '';
    $map = [
        'student.dashboard'     => ['book',   'Siap baca'],
        'student.loans'         => ['loan',   'Pinjamanmu'],
        'student.history'       => ['clock',  'Riwayat'],
        'student.notifications' => ['bell',   'Kabar baru'],
        'admin.dashboard'       => ['chart',  'Ringkasan'],
        'profile.show'          => ['people', 'Profil'],
        'admin.books'           => ['book',   'Rak buku'],
        'admin.books.create'    => ['plus',   'Koleksi'],
        'admin.books.import'    => ['upload', 'Impor CSV'],
        'admin.books.edit'      => ['book',   'Ubah data'],
        'admin.submissions'     => ['check',  'Verifikasi'],
        'admin.loans'           => ['loan',   'Sirkulasi'],
        'admin.reports'         => ['chart',  'Laporan'],
        'admin.members'         => ['people', 'Anggota'],
    ];
    [$ico, $cap] = $map[$r] ?? ['book', 'E-LIBRA'];
    $d = [
        'book'   => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
        'loan'   => '<path d="M3 12a9 9 0 109-9 9 9 0 00-6.7 3L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'bell'   => '<path d="M6 8a6 6 0 1112 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 20a2 2 0 004 0"/>',
        'chart'  => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
        'plus'   => '<path d="M12 5v14M5 12h14"/>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 20h16"/>',
        'check'  => '<path d="M5 12l5 5L20 6"/>',
        'people' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-4 3-6 6.5-6s6.5 2 6.5 6"/><path d="M16 4.5a3.5 3.5 0 010 7M18 14c2.5.6 3.5 2.6 3.5 6"/>',
    ];
@endphp
<div class="panel-art" aria-hidden="true">
    <i class="pa-ring"></i>
    <span class="pa-card pa-c1"><svg class="ic" viewBox="0 0 24 24" width="26" height="26">{!! $d[$ico] !!}</svg></span>
    <span class="pa-card pa-c2"><b>{{ $cap }}</b><em></em><em></em></span>
    <svg class="pa-sp pa-s1" viewBox="0 0 24 24"><path d="M12 1l2.4 8.6L23 12l-8.6 2.4L12 23l-2.4-8.6L1 12l8.6-2.4z"/></svg>
    <svg class="pa-sp pa-s2" viewBox="0 0 24 24"><path d="M12 1l2.4 8.6L23 12l-8.6 2.4L12 23l-2.4-8.6L1 12l8.6-2.4z"/></svg>
</div>
