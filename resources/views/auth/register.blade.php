@extends('layouts.app')
@section('title', 'Daftar Akun — E-LIBRA')
@section('content')

<div class="login-split dreamy">
  <div class="login-left">
    <a href="{{ route('landing') }}" class="brand">
      <div class="mark"><svg class="ic" width="17" height="17" viewBox="0 0 24 24" style="color:#fff"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
      E-LIBRA
    </a>

    <div class="login-form-wrap">
      <div class="k">DAFTAR AKUN SISWA</div>
      <h2>Buat akun baru</h2>
      <p class="sub">Daftar sebagai siswa untuk mulai mengajukan peminjaman buku.</p>

      <form method="POST" action="{{ route('register.post') }}">
        @csrf
        @include('partials.flash')
        <div class="form-label">Nama Lengkap</div>
        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
               class="form-field" style="width:100%;font:inherit;color:inherit;border:1.5px solid var(--pink-150);" placeholder="Nama lengkap">

        <div class="form-label">Kelas</div>
        <input type="text" name="kelas" value="{{ old('kelas') }}" required
               class="form-field" style="width:100%;font:inherit;color:inherit;border:1.5px solid var(--pink-150);" placeholder="Contoh: XII RPL 2">

        <div class="form-label">NIS</div>
        <input type="text" name="nis" value="{{ old('nis') }}" required autocomplete="username"
               class="form-field" style="width:100%;font:inherit;color:inherit;border:1.5px solid var(--pink-150);" placeholder="Nomor induk siswa">

        <div class="form-label">Email</div>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
               class="form-field" style="width:100%;font:inherit;color:inherit;border:1.5px solid var(--pink-150);" placeholder="nama@email.com">

        <div class="form-label">Kata Sandi</div>
        <div class="form-field" style="width:100%;">
          <input type="password" name="password" id="reg-password" required minlength="8" autocomplete="new-password"
                 style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;" placeholder="Minimal 8 karakter">
          <svg id="reg-password-icon" class="ic" width="15" height="15" viewBox="0 0 24 24" style="color:var(--ink-soft2);cursor:pointer;" onclick="(function(){
              var i=document.getElementById('reg-password');
              var show=i.type==='password';
              i.type= show ? 'text' : 'password';
              document.getElementById('reg-password-icon').innerHTML = show
                  ? '<path d=\'M17.94 17.94A10.94 10.94 0 0112 20c-7 0-10-8-10-8a18.4 18.4 0 015.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 10 8 10 8a18.5 18.5 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24\'></path><path d=\'M1 1l22 22\'></path>'
                  : '<path d=\'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z\'></path><circle cx=\'12\' cy=\'12\' r=\'3\'></circle>';
          })()"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>

        <div class="pw-meter" data-s="0" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
        <div class="pw-hint" aria-live="polite"></div>

        <div class="form-label">Konfirmasi Kata Sandi</div>
        <div class="form-field" style="width:100%;">
          <input type="password" name="password_confirmation" id="reg-password-confirm" required minlength="8" autocomplete="new-password"
                 style="border:none;background:none;outline:none;flex:1;font-family:inherit;font-size:13px;" placeholder="Ulangi kata sandi">
          <svg id="reg-password-confirm-icon" class="ic" width="15" height="15" viewBox="0 0 24 24" style="color:var(--ink-soft2);cursor:pointer;" onclick="(function(){
              var i=document.getElementById('reg-password-confirm');
              var show=i.type==='password';
              i.type= show ? 'text' : 'password';
              document.getElementById('reg-password-confirm-icon').innerHTML = show
                  ? '<path d=\'M17.94 17.94A10.94 10.94 0 0112 20c-7 0-10-8-10-8a18.4 18.4 0 015.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 10 8 10 8a18.5 18.5 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24\'></path><path d=\'M1 1l22 22\'></path>'
                  : '<path d=\'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z\'></path><circle cx=\'12\' cy=\'12\' r=\'3\'></circle>';
          })()"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>

        <div class="pw-hint" id="pw-match" aria-live="polite"></div>

        <button type="submit" class="btn-primary-lg" style="width:100%;">
          Daftar Sekarang <svg class="ic" width="14" height="14" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </form>

      <p class="signup-note" style="margin-top:24px;">Sudah punya akun? <a href="{{ route('login') }}"><b>Masuk di sini</b></a></p>
    </div>
  </div>

  <div class="login-right">
    @include('partials.library-scene')
  </div>
</div>

@endsection
