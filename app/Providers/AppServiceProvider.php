<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Perbaikan error Windows "rename(): Access is denied" pada cache tampilan
        $this->app->singleton('files', fn () => new \App\Support\SafeFilesystem);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Support\Tambah30Buku::jalankan();

        // Email atur ulang kata sandi berbahasa Indonesia
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi E-LIBRA')
                ->greeting('Halo, '.$notifiable->name.'!')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun E-LIBRA kamu.')
                ->action('Atur Ulang Kata Sandi', $url)
                ->line('Link ini berlaku 60 menit. Jika bukan kamu yang meminta, abaikan email ini; kata sandimu tidak berubah.');
        });
    }
}
