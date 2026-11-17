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
        <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></span>
            Dashboard
        </a>
        <a href="{{ route('catalog') }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg></span>
            Katalog Buku
        </a>
        <a href="{{ route('student.loans') }}" class="{{ request()->routeIs('student.loans') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></span>
            Peminjaman Saya
        </a>
        <a href="{{ route('student.history') }}" class="{{ request()->routeIs('student.history') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9.7 9.7 0 00-6.7 2.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg></span>
            Riwayat
        </a>
        <a href="{{ route('student.notifications') }}" class="{{ request()->routeIs('student.notifications') ? 'active' : '' }}">
            <span class="ic"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"/></svg></span>
            Notifikasi
            @php $unread = auth()->user()->unreadNotificationCount(); @endphp
            @if($unread > 0)<span class="nav-badge">{{ $unread > 9 ? '9+' : $unread }}</span>@endif
        </a>
    </nav>
    <div class="app-side-bottom">
        <a href="{{ route('profile.show') }}" class="app-user {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
            @include('partials.avatar', ['user' => auth()->user()])
            <div>
                <div class="n">{{ auth()->user()->name }}</div>
                <div class="r">Siswa · Profil Saya</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar (Logout)</button>
        </form>
    </div>
</aside>