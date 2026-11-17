<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'string', 'max:50'],
            'nis' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'kelas' => $data['kelas'],
            'nis' => $data['nis'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'siswa',
        ]);

        Auth::login($user);

        return redirect(RouteServiceProvider::redirectPath());
    }
}
