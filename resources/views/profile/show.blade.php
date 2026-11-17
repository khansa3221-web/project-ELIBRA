@extends('layouts.panel')
@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')
@section('subheading', $user->isAdmin() ? 'Data akun petugas perpustakaan.' : 'Informasi akun keanggotaan perpustakaanmu.')
@section('panel')
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-grid">
    @csrf
    @method('PUT')

    <div class="panel profile-card">
        <div id="avatar-preview">@include('partials.avatar', ['user' => $user, 'class' => 'pf-avatar'])</div>
        <h2>{{ $user->name }}</h2>
        <div class="pf-role">{{ $user->isAdmin() ? 'Admin / Petugas' : 'Siswa' }}</div>
        <dl class="pf-meta">
            <div><dt>Username</dt><dd>{{ $user->username ?? '-' }}</dd></div>
            @if($user->isSiswa())
                <div><dt>NIS</dt><dd>{{ $user->nis ?? '-' }}</dd></div>
                <div><dt>Kelas</dt><dd>{{ $user->class ?? '-' }}</dd></div>
            @else
                <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt>Peran</dt><dd>Admin</dd></div>
                <div><dt>Akun dibuat</dt><dd>{{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}</dd></div>
            @endif
        </dl>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Edit Profil</h3></div>

        @if($errors->any())
            <div class="alert alert-error" style="margin:0 0 16px;">Periksa kembali isian di bawah ini.</div>
        @endif

        <div class="form-grid">
            <div class="span2">
                <div class="form-label" style="margin-top:0;">Foto Profil (JPG/PNG/WEBP, maks 2 MB)</div>
                <input type="file" name="photo" id="photo-input" class="input" accept="image/jpeg,image/png,image/webp">
                @error('photo')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
                @if($user->photo)
                    <label style="margin-top:10px;display:flex;gap:8px;align-items:center;font-size:12.5px;">
                        <input type="checkbox" name="remove_photo" value="1"> Hapus foto saat ini
                    </label>
                @endif
            </div>

            <div class="span2">
                <div class="form-label">Nama Lengkap *</div>
                <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
            </div>

            <div>
                <div class="form-label">Username *</div>
                <input type="text" name="username" class="input" value="{{ old('username', $user->username) }}" required autocomplete="username">
                @error('username')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
            </div>
            <div>
                <div class="form-label">Email *</div>
                <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
            </div>

            @if($user->isSiswa())
                <div>
                    <div class="form-label">NIS</div>
                    <input type="text" class="input" value="{{ $user->nis }}" disabled>
                    <div class="hint">NIS tidak dapat diubah. Hubungi petugas bila ada kesalahan.</div>
                </div>
                <div>
                    <div class="form-label">Kelas *</div>
                    <input type="text" name="kelas" class="input" value="{{ old('kelas', $user->class) }}" required>
                    @error('kelas')<div class="hint" style="color:#B3261E;font-weight:700;">{{ $message }}</div>@enderror
                </div>
            @endif
        </div>

        <div style="display:flex;gap:12px;margin-top:26px;flex-wrap:wrap;">
            <button type="submit" class="btn-solid" style="border:none;cursor:pointer;font-family:inherit;">Simpan Perubahan</button>
        </div>
    </div>
</form>
<script>
document.getElementById('photo-input')?.addEventListener('change', function () {
    var f = this.files && this.files[0];
    if (!f) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024) {
        alert('Foto harus JPG, PNG, atau WEBP dan maksimal 2 MB.');
        this.value = '';
        return;
    }
    var box = document.querySelector('#avatar-preview .pf-avatar');
    box.textContent = '';
    var img = document.createElement('img');
    img.alt = 'Pratinjau foto'; img.src = URL.createObjectURL(f);
    box.appendChild(img);
});
</script>
@endsection
