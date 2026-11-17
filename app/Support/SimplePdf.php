<?php

namespace App\Support;

/**
 * Pembuat PDF satu halaman tanpa library tambahan (font bawaan Helvetica).
 * Koordinat memakai satuan poin dengan titik nol di KIRI-ATAS halaman.
 */
class SimplePdf
{
    public const W = 595.28;
    public const H = 841.89;

    private string $content = '';

    // Lebar huruf Helvetica per 1000 unit untuk karakter ASCII 32..126
    private const REG = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
    ];

    // Lebar huruf Helvetica-Bold
    private const BOLD = [
        278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 333, 333, 584, 584, 584, 611,
        975, 722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 333, 278, 333, 584, 556,
        333, 556, 611, 556, 611, 556, 333, 611, 611, 278, 278, 556, 278, 889, 611, 611,
        611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500, 389, 280, 389, 584,
    ];

    private static function enc(string $s): string
    {
        $out = @mb_convert_encoding($s, 'Windows-1252', 'UTF-8');

        return is_string($out) ? $out : $s;
    }

    private static function esc(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $s);
    }

    private static function num(float $v): string
    {
        return number_format($v, 2, '.', '');
    }

    private static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return self::num(hexdec(substr($hex, 0, 2)) / 255).' '
            .self::num(hexdec(substr($hex, 2, 2)) / 255).' '
            .self::num(hexdec(substr($hex, 4, 2)) / 255);
    }

    // Lebar teks (poin) pada ukuran huruf tertentu
    public function width(string $text, float $size, bool $bold = false): float
    {
        $table = $bold ? self::BOLD : self::REG;
        $sum = 0;
        foreach (str_split(self::enc($text)) as $ch) {
            $o = ord($ch);
            $sum += ($o >= 32 && $o <= 126) ? $table[$o - 32] : 556;
        }

        return $sum * $size / 1000;
    }

    // Pecah teks menjadi beberapa baris agar muat dalam $maxWidth
    public function wrap(string $text, float $maxWidth, float $size, bool $bold = false): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $try = $line === '' ? $word : $line.' '.$word;
            if ($line !== '' && $this->width($try, $size, $bold) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }

    public function rect(float $x, float $y, float $w, float $h, string $fill): self
    {
        $this->content .= 'q '.self::rgb($fill).' rg '.self::num($x).' '.self::num(self::H - $y - $h).' '
            .self::num($w).' '.self::num($h)." re f Q\n";

        return $this;
    }

    public function strokeRect(float $x, float $y, float $w, float $h, string $color, float $lineWidth = 0.8): self
    {
        $this->content .= 'q '.self::rgb($color).' RG '.self::num($lineWidth).' w '.self::num($x).' '.self::num(self::H - $y - $h).' '
            .self::num($w).' '.self::num($h)." re S Q\n";

        return $this;
    }

    public function line(float $x1, float $y1, float $x2, float $y2, string $color, float $lineWidth = 0.8): self
    {
        $this->content .= 'q '.self::rgb($color).' RG '.self::num($lineWidth).' w '.self::num($x1).' '.self::num(self::H - $y1)
            .' m '.self::num($x2).' '.self::num(self::H - $y2)." l S Q\n";

        return $this;
    }

    // $y adalah garis dasar teks (dari atas halaman). $align: L, R, atau C terhadap $x.
    public function text(float $x, float $y, string $text, float $size = 10, bool $bold = false, string $color = '#000000', string $align = 'L'): self
    {
        $w = $this->width($text, $size, $bold);
        if ($align === 'R') {
            $x -= $w;
        } elseif ($align === 'C') {
            $x -= $w / 2;
        }
        $this->content .= 'BT /'.($bold ? 'F2' : 'F1').' '.self::num($size).' Tf '.self::rgb($color).' rg '
            .self::num($x).' '.self::num(self::H - $y).' Td ('.self::esc(self::enc($text)).") Tj ET\n";

        return $this;
    }

    public function output(): string
    {
        $content = $this->content;
        $objs = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::num(self::W).' '.self::num(self::H).'] '
                .'/Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            6 => '<< /Length '.strlen($content).' >>'."\nstream\n".$content."endstream",
        ];

        $out = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $n => $body) {
            $offsets[$n] = strlen($out);
            $out .= $n." 0 obj\n".$body."\nendobj\n";
        }
        $xref = strlen($out);
        $out .= "xref\n0 ".(count($objs) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $out .= sprintf('%010d 00000 n ', $off)."\n";
        }
        $out .= "trailer\n<< /Size ".(count($objs) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";

        return $out;
    }
}
