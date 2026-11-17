<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Bu Sari',
            'email'    => 'sari@elibra.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'class'    => null,
        ]);

        // Siswa
        User::create([
            'name'     => 'Khansa Qatrunnada',
            'email'    => 'khansa@elibra.test',
            'password' => Hash::make('password'),
            'role'     => 'siswa',
            'nis'      => '12345',
            'class'    => 'XII RPL 2',
        ]);

        $categories = [
            ['name' => 'Fiksi', 'slug' => 'fiksi'],
            ['name' => 'Sains', 'slug' => 'sains'],
            ['name' => 'Pelajaran', 'slug' => 'pelajaran'],
            ['name' => 'Sejarah', 'slug' => 'sejarah'],
            ['name' => 'Biografi', 'slug' => 'biografi'],
        ];
        foreach ($categories as $c) Category::create($c);

        $books = [
            ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'cat' => 'fiksi', 'gradient' => 'mc1', 'stock' => 6],
            ['title' => 'Fisika Dasar', 'author' => 'Halliday', 'cat' => 'sains', 'gradient' => 'mc2', 'stock' => 3],
            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya A.T.', 'cat' => 'fiksi', 'gradient' => 'mc3', 'stock' => 4],
            ['title' => 'Matematika XII', 'author' => 'Kemendikbud', 'cat' => 'pelajaran', 'gradient' => 'mc4', 'stock' => 12],
            ['title' => 'Sejarah Nusantara', 'author' => 'Tim Penulis', 'cat' => 'sejarah', 'gradient' => 'mc5', 'stock' => 5],
            ['title' => 'Biologi Sel', 'author' => 'Campbell', 'cat' => 'sains', 'gradient' => 'mc6', 'stock' => 8],
        ];

        foreach ($books as $b) {
            $category = Category::where('slug', $b['cat'])->first();
            Book::create([
                'category_id'     => $category->id,
                'title'           => $b['title'],
                'slug'            => \Illuminate\Support\Str::slug($b['title']),
                'author'          => $b['author'],
                'isbn'            => '978-' . rand(1000000000, 9999999999),
                'shelf'           => 'Rak ' . strtoupper(substr($b['cat'], 0, 1)),
                'language'        => 'Indonesia',
                'description'     => 'Deskripsi singkat ' . $b['title'] . '.',
                'total_stock'     => $b['stock'],
                'available_stock' => max(0, $b['stock'] - rand(0, 3)),
                'cover_gradient'  => $b['gradient'],
            ]);
        }
    }
}