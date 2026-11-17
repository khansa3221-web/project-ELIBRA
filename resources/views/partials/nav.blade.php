<div class="nav">
    <a href="{{ route('landing') }}" class="brand">
        <div class="mark">
            <svg class="ic" width="17" height="17" viewBox="0 0 24 24" style="color:#fff">
                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
        </div>
        E-LIBRA
    </a>
    <div class="nav-links">
        <a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'active' : '' }}">Beranda</a>
        <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog*') ? 'active' : '' }}">Katalog Buku</a>
    </div>
    <div class="nav-cta">
        @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}" class="btn-solid">
                Dashboard
                <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout btn-logout-top"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg> <span>Logout</span></button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
            <a href="{{ route('register') }}" class="btn-solid">
                Daftar Akun
                <svg class="ic" width="13" height="13" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        @endauth
    </div>
</div>