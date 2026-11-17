@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="board" style="gap:0;">
    <div class="webframe">
        <div class="browser-bar"><div class="tl"><span></span><span></span><span></span></div>
            <div class="urlbox">.../dashboard/profil</div>
        </div>

        <div class="app-shell">
            @include('partials.student-sidebar')

            <main class="app-main">
                <div class="app-topbar">
                    <div>
                        <h1>Profil Saya</h1>
                        <p>Informasi akun keanggotaan perpustakaanmu.</p>
                    </div>
                </div>

                <div class="panel" style="max-width:520px;">
                    <div class="panel-head"><h3>Data Diri</h3></div>
                    <div style="display:flex;flex-direction:column;gap:16px;padding:4px 0;">
                        <div>
                            <div class="form-label">Nama</div>
                            <div class="form-field" style="pointer-events:none;">{{ auth()->user()->name }}</div>
                        </div>
                        <div>
                            <div class="form-label">Email</div>
                            <div class="form-field" style="pointer-events:none;">{{ auth()->user()->email }}</div>
                        </div>
                        <div>
                            <div class="form-label">NIS</div>
                            <div class="form-field" style="pointer-events:none;">{{ auth()->user()->nis ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="form-label">Kelas</div>
                            <div class="form-field" style="pointer-events:none;">{{ auth()->user()->class ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <p style="font-size:12px;color:var(--ink-soft);margin-top:14px;">
                    Untuk mengubah data profil, hubungi petugas perpustakaan.
                </p>
            </main>
        </div>
    </div>
</div>
@endsection
