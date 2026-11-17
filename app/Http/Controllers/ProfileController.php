<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// Profil untuk siswa dan admin. Selalu memakai akun yang sedang login (tidak ada parameter id),
// jadi pengguna tidak bisa melihat atau mengubah profil orang lain.
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
            'email'    => Str::lower(trim((string) $request->input('email'))),
        ]);

        $rules = [
            'name'     => 'required|string|min:3|max:100',
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=100,min_height=100',
        ];
        if ($user->isSiswa()) {
            $rules['kelas'] = 'required|string|max:50';
        }

        $data = $request->validate($rules, [
            'required'          => ':attribute wajib diisi.',
            'min'               => ':attribute minimal :min karakter.',
            'max'               => ':attribute terlalu panjang.',
            'username.regex'    => 'Username hanya boleh huruf kecil, angka, titik, strip, dan garis bawah.',
            'username.unique'   => 'Username ini sudah dipakai.',
            'email.unique'      => 'Email ini sudah dipakai akun lain.',
            'email.email'       => 'Format email tidak valid.',
            'photo.image'       => 'Foto harus berupa gambar.',
            'photo.mimes'       => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max'         => 'Ukuran foto maksimal 2 MB.',
            'photo.dimensions'  => 'Foto terlalu kecil (minimal 100 x 100 piksel).',
            'photo.uploaded'    => 'Foto gagal diunggah. Coba file lain (maksimal 2 MB).',
        ], [
            'name' => 'Nama lengkap', 'username' => 'Username', 'email' => 'Email', 'kelas' => 'Kelas', 'photo' => 'Foto',
        ]);

        // Hanya field ini yang boleh diubah. Role dan NIS tidak pernah diambil dari input.
        $user->name     = $data['name'];
        $user->username = $data['username'];
        $user->email    = $data['email'];
        if ($user->isSiswa()) {
            $user->class = $data['kelas'];
        }

        if ($request->boolean('remove_photo')) {
            $this->deletePhoto($user->photo);
            $user->photo = null;
        }
        if ($request->hasFile('photo')) {
            $this->deletePhoto($user->photo);
            $user->photo = $this->storePhoto($request->file('photo'));
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    private function storePhoto(UploadedFile $file): string
    {
        $dir = public_path('uploads/avatars');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = Str::random(32).'.'.($file->guessExtension() ?: 'jpg');
        $file->move($dir, $name);

        return 'uploads/avatars/'.$name;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/avatars/') && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
