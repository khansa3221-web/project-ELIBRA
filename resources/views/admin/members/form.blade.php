@extends('layouts.panel')
@section('title', $member->exists ? 'Ubah Anggota' : 'Tambah Anggota')
@section('heading', ($member->exists ? 'Ubah Anggota' : 'Tambah Anggota'))
@section('subheading', ($member->exists ? $member->name : 'Daftarkan siswa sebagai anggota perpustakaan.'))
@section('action')
    <a href="{{ route('admin.members.index') }}" class="btn-line">← Kembali</a>
@endsection
@section('panel')
<div class="panel">
    <form method="POST" action="{{ $member->exists ? route('admin.members.update', $member) : route('admin.members.store') }}">
        @csrf
        @if($member->exists) @method('PUT') @endif
        <div class="fgrid">
            <div class="full">
                <div class="form-label">Nama Lengkap *</div>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" class="inp" required>
            </div>
            <div>
                <div class="form-label">NIS *</div>
                <input type="text" name="nis" value="{{ old('nis', $member->nis) }}" class="inp" required>
                <div class="fhint">Dipakai siswa untuk login.</div>
            </div>
            <div>
                <div class="form-label">Kelas *</div>
                <input type="text" name="class" value="{{ old('class', $member->class) }}" class="inp" placeholder="Mis. X RPL 1" required>
            </div>
            <div>
                <div class="form-label">Email (opsional)</div>
                <input type="email" name="email"
                       value="{{ old('email', str_ends_with((string) $member->email, '@siswa.elibra.test') ? '' : $member->email) }}" class="inp">
            </div>
            <div>
                <div class="form-label">{{ $member->exists ? 'Kata sandi baru (kosongkan jika tidak diganti)' : 'Kata sandi awal' }}</div>
                <input type="text" name="password" value="{{ old('password') }}" class="inp" autocomplete="off">
                @unless($member->exists)<div class="fhint">Jika kosong, kata sandi awal = NIS.</div>@endunless
            </div>
        </div>
        <div style="margin-top:22px;display:flex;gap:10px;">
            <button type="submit" class="btn-solid">{{ $member->exists ? 'Simpan Perubahan' : 'Simpan Anggota' }}</button>
            <a href="{{ route('admin.members.index') }}" class="btn-line">Batal</a>
        </div>
    </form>
</div>
@endsection
