<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'elibra:admin';

    protected $description = 'Buat akun admin baru, atau ganti password admin yang sudah ada';

    public function handle(): int
    {
        $name     = (string) $this->ask('Nama petugas');
        $email    = strtolower(trim((string) $this->ask('Email')));
        $password = (string) $this->secret('Kata sandi baru (minimal 8 karakter)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email',
                'password' => 'required|min:8',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['name' => $name, 'password' => $password, 'role' => 'admin']);
            $this->info("Password admin {$email} berhasil diganti.");
        } else {
            User::create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'admin']);
            $this->info("Akun admin {$email} berhasil dibuat.");
        }

        return self::SUCCESS;
    }
}
