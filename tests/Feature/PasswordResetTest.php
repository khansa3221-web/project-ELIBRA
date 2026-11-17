<?php

namespace Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase, Concerns;

    public function test_login_punya_tautan_lupa_kata_sandi_dan_halamannya_terbuka(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('password.request'), false)->assertSee('Lupa kata sandi?');
        $this->get('/lupa-kata-sandi')->assertOk()->assertSee('Lupa Kata Sandi?');
    }

    public function test_link_reset_terkirim_lewat_email_atau_nis(): void
    {
        Notification::fake();
        $u = $this->bikinUser('siswa', ['nis' => '77001', 'email' => 'lupa@elibra.test']);

        $this->post('/lupa-kata-sandi', ['identifier' => 'lupa@elibra.test'])->assertSessionHas('success');
        Notification::assertSentTo($u, ResetPassword::class);

        Notification::fake();
        $this->post('/lupa-kata-sandi', ['identifier' => '77001'])->assertSessionHas('success');
        Notification::assertSentTo($u, ResetPassword::class);
    }

    public function test_akun_tak_dikenal_dapat_jawaban_sama_dan_tidak_ada_email(): void
    {
        Notification::fake();
        $this->post('/lupa-kata-sandi', ['identifier' => 'tidak-ada@x.test'])->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_atur_ulang_kata_sandi_dengan_token_valid(): void
    {
        $u = $this->bikinUser('siswa', ['email' => 'reset@elibra.test']);
        $token = Password::broker()->createToken($u);

        $this->get(route('password.reset', ['token' => $token, 'email' => $u->email]))->assertOk()->assertSee('Atur kata sandi baru');
        $this->post('/atur-ulang-kata-sandi', [
            'token' => $token, 'email' => $u->email, 'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('sandibaru123', $u->fresh()->password));
        $this->post('/login', ['login' => $u->email, 'password' => 'sandibaru123'])->assertRedirect(route('student.dashboard'));
    }

    public function test_token_salah_ditolak_dan_sandi_tidak_berubah(): void
    {
        $u = $this->bikinUser('siswa', ['email' => 'reset2@elibra.test']);
        $lama = $u->password;

        $this->post('/atur-ulang-kata-sandi', [
            'token' => 'palsu', 'email' => $u->email, 'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertSessionHasErrors('email');
        $this->assertSame($lama, $u->fresh()->password);
    }
}
