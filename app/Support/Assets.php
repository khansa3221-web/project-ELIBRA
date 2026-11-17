<?php

namespace App\Support;

// Pemuat aset: URL berversi (?v=waktu-ubah-file) agar browser boleh menyimpan lama tanpa risiko file basi,
// dan memakai bundle gabungan (public/build) bila ada. Bundle dibuat dengan: node build-aset.mjs
class Assets
{
    // URUTAN PENTING (cascade CSS). Sama dengan urutan <link> sebelumnya.
    public const CSS = ['css/app.css', 'css/animations.css', 'css/extras.css', 'css/polish.css', 'css/dreamy.css', 'css/motion-boost.css', 'css/theme.css', 'css/perf.css'];
    public const JS  = ['js/animations.js', 'js/extras.js', 'js/polish.js', 'js/dreamy.js', 'js/motion-boost.js'];

    private static ?bool $bundled = null;

    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }

    // Bundle dipakai jika ada dan (saat APP_DEBUG=true) tidak lebih tua dari file sumbernya,
    // sehingga mengedit css/js sumber langsung terlihat saat pengembangan.
    public static function bundled(): bool
    {
        if (self::$bundled !== null) {
            return self::$bundled;
        }
        $css = public_path('build/elibra.min.css');
        $js  = public_path('build/elibra.min.js');
        if (! is_file($css) || ! is_file($js)) {
            return self::$bundled = false;
        }
        if (config('app.debug')) {
            $baru = max(array_map(fn ($f) => is_file(public_path($f)) ? filemtime(public_path($f)) : 0, array_merge(self::CSS, self::JS)));
            if ($baru > min(filemtime($css), filemtime($js))) {
                return self::$bundled = false;
            }
        }

        return self::$bundled = true;
    }

    // Isi file kecil disisipkan langsung di halaman (menghemat satu request yang memblokir tampilan).
    public static function inline(string $path): string
    {
        static $memo = [];
        $file = public_path($path);
        if (! isset($memo[$path])) {
            $memo[$path] = is_file($file) ? (string) file_get_contents($file) : '';
        }

        return $memo[$path];
    }
}
