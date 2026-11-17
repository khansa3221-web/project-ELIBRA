<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use Throwable;

/**
 * Filesystem yang tahan terhadap error "rename(): Access is denied (code: 5)" di Windows/XAMPP.
 * Laravel menulis cache tampilan (Blade) lewat file .tmp lalu rename(); di Windows, rename
 * sering gagal bila file tujuan sedang dikunci antivirus / proses lain.
 * Jika rename gagal, tulis langsung ke file tujuan sebagai cadangan.
 */
class SafeFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null)
    {
        try {
            parent::replace($path, $content, $mode);
            return;
        } catch (Throwable $e) {
            // lanjut ke cadangan di bawah
        }

        clearstatcache(true, $path);
        $realPath = realpath($path) ?: $path;

        // bersihkan sisa file .tmp yang gagal di-rename
        foreach (glob(dirname($realPath) . DIRECTORY_SEPARATOR . basename($realPath) . '*') ?: [] as $tmp) {
            if ($tmp !== $realPath && preg_match('/\.tmp$|[A-Za-z0-9]{6}$/', $tmp)) {
                @unlink($tmp);
            }
        }

        if (@file_put_contents($realPath, $content, LOCK_EX) === false
            && @file_put_contents($realPath, $content) === false) {
            // gagal total: lempar error aslinya supaya terlihat penyebabnya
            throw new \RuntimeException("Tidak bisa menulis file cache: {$realPath}");
        }

        if (! is_null($mode)) {
            @chmod($realPath, $mode);
        }
    }
}
