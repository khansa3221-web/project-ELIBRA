<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private function redirectByRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role === 'siswa') {
            return redirect()->route('student.dashboard');
        }

        // Role tidak dikenal: keluarkan dengan pesan jelas, jangan biarkan redirect berputar
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['login' => 'Akun ini tidak memiliki peran yang valid. Hubungi petugas.']);
    }

    // Daftar NIS yang boleh mendaftar dibaca dari database/seeders/nis-terdaftar.json
    // (tanpa migrasi). Untuk menambah siswa, tambahkan baris "NIS": "NAMA" di file itu.
    private function nisTerdaftar(string $nis): bool
    {
        $file = database_path('seeders/nis-terdaftar.json');
        $daftar = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        return is_array($daftar) && array_key_exists(trim($nis), $daftar);
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($data['login']);

        // Batasi percobaan: maks. 5 kali gagal per menit untuk kombinasi akun + alamat IP
        $key = 'login:'.strtolower($login).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam '.$detik.' detik.'])
                ->onlyInput('login');
        }

        $user = User::where('email', strtolower($login))->first();

        if (! $user) {
            $user = User::where('nis', $login)->first();
        }

        // Login memakai nama hanya jika namanya unik (nama bisa kembar antar siswa)
        if (! $user) {
            $cocok = User::whereRaw('LOWER(name) = ?', [strtolower($login)])->limit(2)->get();
            $user = $cocok->count() === 1 ? $cocok->first() : null;
        }

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            return back()
                ->withErrors(['login' => 'Kredensial tidak cocok.'])
                ->onlyInput('login');
        }

        RateLimiter::clear($key);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function register(Request $request)
    {
        // Rapikan input sebelum validasi agar unik-cek tidak lolos karena huruf besar/spasi
        $request->merge([
            'name'  => trim((string) $request->input('name')),
            'kelas' => trim((string) $request->input('kelas')),
            'nis'   => trim((string) $request->input('nis')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name'     => 'required|string|min:3|max:100',
            'kelas'    => 'required|string|max:50',
            'nis'      => ['bail', 'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9.\-\/]+$/',
                           function (string $attribute, mixed $value, \Closure $fail) {
                               if (! $this->nisTerdaftar((string) $value)) {
                                   $fail('NIS "'.$value.'" tidak ada dalam daftar siswa. Periksa kembali NIS-mu atau hubungi petugas perpustakaan.');
                               }
                           },
                           'unique:users,nis'],
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:100|confirmed',
        ], [
            'required'            => ':attribute wajib diisi.',
            'email'               => 'Format email tidak valid.',
            'min'                 => ':attribute minimal :min karakter.',
            'max'                 => ':attribute terlalu panjang.',
            'nis.unique'          => 'NIS ini sudah terdaftar. Silakan masuk atau hubungi petugas.',
            'email.unique'        => 'Email ini sudah terdaftar. Silakan masuk.',
            'nis.regex'           => 'NIS hanya boleh berisi huruf, angka, titik, strip, atau garis miring.',
            'password.confirmed'  => 'Konfirmasi kata sandi tidak cocok.',
        ], [
            'name' => 'Nama lengkap', 'kelas' => 'Kelas', 'nis' => 'NIS',
            'email' => 'Email', 'password' => 'Kata sandi',
        ]);

        // Role TIDAK diambil dari input: pendaftaran publik selalu siswa.
        // Password di-hash otomatis oleh cast 'hashed' pada model User.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'siswa',
            'nis'      => $data['nis'],
            'class'    => $data['kelas'],
        ]);

        // Setelah daftar langsung masuk (tanpa login ulang) ke dashboard siswa
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('success', 'Registrasi berhasil! Selamat datang di E-LIBRA, '.$user->name.'.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}