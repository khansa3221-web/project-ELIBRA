<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'admin' ? 'admin.dashboard' : 'student.dashboard');
        }

        return view('auth.forgot');
    }

    // Kirim link reset. Boleh memakai email ATAU NIS. Jawaban selalu sama agar
    // orang luar tidak bisa menebak email/NIS mana yang terdaftar.
    public function sendLink(Request $request)
    {
        $data = $request->validate(['identifier' => 'required|string|max:255'], [
            'identifier.required' => 'Email atau NIS wajib diisi.',
        ]);
        $id = trim($data['identifier']);

        $user = User::where('email', strtolower($id))->first() ?? User::where('nis', $id)->first();
        if ($user && $user->email) {
            try {
                Password::broker()->sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                report($e); // SMTP belum diatur / gagal kirim: jangan bocorkan ke pengunjung
            }
        }

        return back()->with('success', 'Jika akun tersebut terdaftar, link atur ulang kata sandi sudah dikirim ke email akun. Link berlaku 60 menit.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|max:100|confirmed',
        ], [
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'required'           => ':attribute wajib diisi.',
        ], ['password' => 'Kata sandi', 'email' => 'Email']);

        $status = Password::broker()->reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Link tidak valid atau sudah kedaluwarsa. Minta link baru lewat "Lupa kata sandi".']);
    }
}
