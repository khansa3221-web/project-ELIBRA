<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Support\Tambah30Buku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** 8 buku tanpa cover dibuang dari daftar; yang sudah telanjur ada dihapus hanya bila belum bercover & belum pernah dipinjam. */
class HapusBukuTanpaCoverTest extends TestCase
{
    use RefreshDatabase, Concerns;

    public function test_daftar_hanya_berisi_buku_bercover_dan_tanpa_8_judul_yang_dibuang(): void
    {
        $daftar = json_decode(file_get_contents(database_path('seeders/daftar-30-buku.json')), true);
        $peta = json_decode(file_get_contents(database_path('seeders/cover-map.json')), true);

        $this->assertCount(29, $daftar);
        foreach ($daftar as $d) {
            $this->assertNotContains($d['title'], Tambah30Buku::DIHAPUS);
            $this->assertArrayHasKey($d['title'], $peta, $d['title']);
            $this->assertFileExists(public_path($peta[$d['title']]), $d['title']);
        }
    }

    public function test_buku_tanpa_cover_dihapus_tapi_yang_pernah_dipinjam_dan_yang_bercover_aman(): void
    {
        $hapus1 = $this->bikinBuku('Negeri Di Ujung Tanduk');
        $dipinjam = $this->bikinBuku('Harga Sebuah Percaya');
        $hapus2 = $this->bikinBuku('Jo & Kas');
        $bercover = $this->bikinBuku('Normal People');   // ada di cover-map.json
        $lain = $this->bikinBuku('Buku Lain Milik Sekolah');

        Loan::create([
            'user_id' => $this->bikinUser()->id, 'book_id' => $dipinjam->id,
            'pickup_date' => now()->toDateString(), 'duration_days' => 7, 'status' => 'returned',
        ]);

        Tambah30Buku::jalankan(true);

        $this->assertNull(Book::find($hapus1->id));
        $this->assertNull(Book::find($hapus2->id));
        $this->assertNotNull(Book::find($dipinjam->id), 'punya riwayat pinjam: tidak boleh dihapus');
        $this->assertNotNull(Book::find($bercover->id));
        $this->assertNotNull(Book::find($lain->id));
        $this->assertSame(1, Book::where('title', 'Normal People')->count(), 'tidak boleh menggandakan');
    }
}
