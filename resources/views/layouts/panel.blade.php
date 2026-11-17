@extends('layouts.app')

@section('content')
<div class="app-shell">
    @include(auth()->user()->isAdmin() ? 'partials.admin-sidebar' : 'partials.student-sidebar')
    <main class="app-main">
        <div class="app-utility">
            <div class="crumbs">
                <a href="{{ route('landing') }}">E-LIBRA</a><span>/</span>
                <span>{{ auth()->user()->isAdmin() ? 'Admin' : 'Siswa' }}</span><span>/</span>
                <b>@yield('heading')</b>
            </div>
            <div class="app-utility-actions">
                <a href="{{ route('profile.show') }}" class="util-user" title="Profil Saya">
                    @include('partials.avatar', ['user' => auth()->user(), 'class' => 'av av-sm'])
                    <span>{{ \Illuminate\Support\Str::limit(auth()->user()->name, 22) }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout btn-logout-top"><svg class="ic" width="15" height="15" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg> <span>Logout</span></button>
                </form>
            </div>
        </div>
        <div class="app-topbar">
            @include('partials.panel-art')
            <div class="app-topbar-text">
                <h1>@yield('heading')</h1>
                <p>@yield('subheading')</p>
            </div>
            @yield('action')
        </div>
        @include('partials.flash')
        @yield('panel')
    </main>
</div>
@endsection
