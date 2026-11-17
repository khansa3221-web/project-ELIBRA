@extends('layouts.app')
@section('title', 'Login')

@section('content')

        <div class="login-split dreamy">
            <div class="login-left">
                <div class="brand">
                    <div class="mark"><svg class="ic" width="17" height="17" viewBox="0 0 24 24" style="color:#fff"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
                    E-LIBRA
                </div>

                <form method="POST" action="{{ route('login.post') }}" class="login-form-wrap">
                    @csrf
                    <div class="k">MASUK</div>
                    <h2>Selamat datang kembali</h2>
                    <p class="sub">Masuk untuk mencari buku, mengajukan peminjaman, dan memantau peminjamanmu.</p>

                    @if(session('success'))
                        <div class="alert alert-success" style="margin:0 0 14px;">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div style="background:#EAEFF1;color:#2A4B84;padding:10px 14px;border-radius:10px;font-size:12px;font-weight:700;margin-bottom:14px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="form-label">Username / NIS / Email</div>
                    <div class="form-field">
                        <input type="text" name="login" value="{{ old('login') }}" required
                               style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;">
                        <svg class="ic" width="15" height="15" viewBox="0 0 24 24" style="color:var(--ink-soft2)"><path d="M20 21a8 8 0 10-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>

                    <div class="form-label">Kata Sandi</div>
                    <div class="form-field">
                        <input type="password" name="password" id="password-input" required
                               style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;">
                        <svg id="password-toggle-icon" class="ic" width="15" height="15" viewBox="0 0 24 24" style="color:var(--ink-soft2);cursor:pointer;" onclick="(function(){
                            var i=document.getElementById('password-input');
                            var show=i.type==='password';
                            i.type= show ? 'text' : 'password';
                            document.getElementById('password-toggle-icon').innerHTML = show
                                ? '<path d=\'M17.94 17.94A10.94 10.94 0 0112 20c-7 0-10-8-10-8a18.4 18.4 0 015.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 10 8 10 8a18.5 18.5 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24\'></path><path d=\'M1 1l22 22\'></path>'
                                : '<path d=\'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z\'></path><circle cx=\'12\' cy=\'12\' r=\'3\'></circle>';
                        })()"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>

                    <div class="form-row">
                        <label class="remember" style="cursor:pointer;">
                            <input type="checkbox" name="remember"> Ingat saya
                        </label>
                        <a href="{{ route('password.request') }}" style="font-size:12px;font-weight:700;color:var(--pink-700,#279CA4);">Lupa kata sandi?</a>
                    </div>
                    <button type="submit" class="btn-primary-lg" style="width:100%;border:none;cursor:pointer;font-family:inherit;">
                        Masuk ke Akun
                        <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="signup-note">Belum punya akun? <a href="{{ route('register') }}"><b>Daftar di sini</b></a></div>
                </form>
            </div>

            <div class="login-right">
                @include('partials.library-scene')
            </div>
</div>
@endsection
