<?php

namespace App\Support;

// Mengecilkan gambar unggahan (cover/foto profil) agar halaman tidak memuat file besar.
// Memakai ekstensi GD bila tersedia; bila tidak ada atau gagal, file asli dibiarkan (aman).
class ImageShrink
{
    public static function shrink(string $file, int $maxWidth, int $quality = 82): void
    {
        try {
            if (! function_exists('imagecreatetruecolor') || ! is_file($file)) {
                return;
            }
            $info = @getimagesize($file);
            if (! $info || $info[0] <= $maxWidth) {
                return; // sudah cukup kecil
            }
            [$w, $h, $type] = $info;

            $src = match ($type) {
                IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file) : false,
                IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file) : false,
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
                default        => false,
            };
            if (! $src) {
                return;
            }

            $nh  = (int) round($h * $maxWidth / $w);
            $dst = imagecreatetruecolor($maxWidth, $nh);
            if ($type !== IMAGETYPE_JPEG) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $nh, $w, $h);

            $ok = match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dst, $file, $quality),
                IMAGETYPE_PNG  => imagepng($dst, $file, 7),
                IMAGETYPE_WEBP => imagewebp($dst, $file, $quality),
                default        => false,
            };
            unset($src, $dst);
        } catch (\Throwable $e) {
            report($e); // jangan sampai unggahan gagal hanya karena pengecilan gambar
        }
    }
}
