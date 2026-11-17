<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseReadyTest extends TestCase
{
    use RefreshDatabase;

    public function test_skema_lengkap_halaman_publik_terbuka(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_skema_kurang_menampilkan_penjelasan_bukan_error_sql(): void
    {
        Schema::drop('user_notifications');

        $this->get('/login')->assertStatus(503)->assertSee('user_notifications')->assertSee('php artisan migrate');
    }
}
