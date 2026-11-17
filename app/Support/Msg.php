<?php

namespace App\Support;

class Msg
{
    /** Pesan validasi berbahasa Indonesia. */
    public static function id(): array
    {
        return [
            'required'   => ':attribute wajib diisi.',
            'unique'     => ':attribute sudah dipakai.',
            'integer'    => ':attribute harus berupa angka.',
            'min'        => [
                'numeric' => ':attribute minimal :min.',
                'string'  => ':attribute minimal :min karakter.',
            ],
            'max'        => [
                'numeric' => ':attribute maksimal :max.',
                'string'  => ':attribute maksimal :max karakter.',
                'file'    => ':attribute maksimal :max KB.',
            ],
            'email'      => 'Format :attribute tidak valid.',
            'exists'     => ':attribute tidak valid.',
            'in'         => ':attribute tidak valid.',
            'file'       => ':attribute harus berupa berkas.',
            'extensions' => ':attribute harus berformat CSV.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'title' => 'Judul', 'author' => 'Penulis', 'category_id' => 'Kategori',
            'new_category' => 'Kategori baru', 'isbn' => 'ISBN', 'shelf' => 'Rak',
            'language' => 'Bahasa', 'description' => 'Deskripsi', 'total_stock' => 'Total stok',
            'cover_gradient' => 'Warna sampul', 'name' => 'Nama', 'nis' => 'NIS',
            'class' => 'Kelas', 'email' => 'Email', 'password' => 'Kata sandi', 'file' => 'Berkas',
        ];
    }
}
