<aside class="app-side">
    <div class="brand">
        <div class="mark">
            <svg class="ic" width="17" height="17" viewBox="0 0 24 24" style="color:#fff">
                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
        </div>
        E-LIBRA
    </div>
    <nav class="app-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></span>
            Dashboard
        </a>
        <a href="{{ route('admin.books') }}" class="{{ request()->routeIs('admin.books*') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></span>
            Data Buku
        </a>
        <a href="{{ route('admin.submissions') }}" class="{{ request()->routeIs('admin.submissions') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
            Pengajuan Peminjaman
        </a>
        <a href="{{ route('admin.loans') }}" class="{{ request()->routeIs('admin.loans*') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9.7 9.7 0 00-6.7 2.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg></span>
            Peminjaman
        </a>
        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span>
            Laporan
        </a>
        <a href="{{ route('admin.members') }}" class="{{ request()->routeIs('admin.members') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span>
            Anggota
        </a>
    </nav>
    <div class="app-side-bottom">
        <a href="{{ route('profile.show') }}" class="app-user {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
            @include('partials.avatar', ['user' => auth()->user()])
            <div>
                <div class="n">{{ auth()->user()->name }}</div>
                <div class="r">Admin · Profil Saya</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar (Logout)</button>
        </form>
    </div>
</aside>