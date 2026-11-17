<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Katalog: jumlah yang ditampilkan/total sesuai database dan pagination tidak menduplikasi atau menghilangkan buku. */
class CatalogPaginationTest extends TestCase
{
    use RefreshDatabase, Concerns;

    private function isiBuku(int $jumlah): void
    {
        for ($i = 1; $i <= $jumlah; $i++) {
            $this->bikinBuku('Buku Katalog '.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        }
        // semua dibuat pada detik yang sama, seperti 30 buku yang ditambahkan sekaligus
        Book::query()->update(['created_at' => '2026-11-08 13:00:00', 'updated_at' => '2026-11-08 13:00:00']);
    }

    public function test_halaman_pertama_menampilkan_rentang_dan_total_sesuai_database(): void
    {
        $this->isiBuku(51);
        $this->assertSame(51, Book::count());

        $this->get(route('catalog'))->assertOk()
            ->assertSee('Menampilkan 1–30 dari 51 buku')
            ->assertSee('51 judul tersedia');
    }

    public function test_halaman_kedua_menampilkan_31_sampai_51_bukan_21_dari_51(): void
    {
        $this->isiBuku(51);

        $this->get(route('catalog', ['page' => 2]))->assertOk()
            ->assertSee('Menampilkan 31–51 dari 51 buku')
            ->assertDontSee('Menampilkan 21 dari 51');
    }

    public function test_semua_urutan_tidak_menduplikasi_atau_menghilangkan_buku_antar_halaman(): void
    {
        $this->isiBuku(51);

        foreach (['popular', 'title', 'newest', 'stock'] as $urutan) {
            $id = [];
            foreach ([1, 2] as $hal) {
                $res = $this->get(route('catalog', ['sort' => $urutan, 'page' => $hal]))->assertOk();
                foreach ($res->viewData('books')->items() as $b) {
                    $id[] = $b->id;
                }
            }
            $this->assertCount(51, $id, "urutan $urutan: jumlah buku di semua halaman");
            $this->assertCount(51, array_unique($id), "urutan $urutan: ada buku ganda antar halaman");
        }
    }

    public function test_halaman_di_luar_rentang_diarahkan_ke_halaman_terakhir(): void
    {
        $this->isiBuku(51);

        $this->get(route('catalog', ['page' => 99]))->assertRedirect(route('catalog', ['page' => 2]));
    }

    public function test_pencarian_menampilkan_jumlah_hasil_sesuai_data(): void
    {
        $this->isiBuku(51);
        $this->bikinBuku('Judul Langka Unik');

        $this->get(route('catalog', ['q' => 'Langka']))->assertOk()
            ->assertSee('Menampilkan 1–1 dari 1 buku');
        $this->get(route('catalog', ['q' => 'tidak-ada-judul-ini']))->assertOk()
            ->assertSee('Tidak ada buku yang cocok')
            ->assertDontSee('Menampilkan');
    }
}
