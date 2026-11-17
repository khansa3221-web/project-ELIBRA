<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;

/** Pembantu pembuat data uji (dipakai di beberapa file tes). */
trait Concerns
{
    protected function bikinUser(string $role = 'siswa', array $extra = []): User
    {
        static $n = 0;
        $n++;

        return User::create(array_merge([
            'name'     => ucfirst($role).' Uji '.$n,
            'email'    => $role.$n.'@elibra.test',
            'password' => 'rahasia123',
            'role'     => $role,
            'nis'      => $role === 'siswa' ? 'NIS'.$n : null,
            'class'    => $role === 'siswa' ? 'XI RPL 1' : null,
        ], $extra));
    }

    protected function bikinBuku(string $judul = 'Buku Uji', int $total = 3, ?int $tersedia = null): Book
    {
        static $m = 0;
        $m++;
        $kategori = Category::firstOrCreate(['slug' => 'uji'], ['name' => 'Uji']);

        return Book::create([
            'category_id'     => $kategori->id,
            'title'           => $judul,
            'slug'            => 'buku-uji-'.$m,
            'author'          => 'Penulis Uji',
            'total_stock'     => $total,
            'available_stock' => $tersedia ?? $total,
        ]);
    }
}
