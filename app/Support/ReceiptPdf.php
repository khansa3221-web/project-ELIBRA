<?php

namespace App\Support;

/** Menggambar Bukti Peminjaman (A4) dari array data yang disiapkan ReceiptController::data(). */
class ReceiptPdf
{
    private const NAVY = '#12294B';
    private const TEAL = '#17707A';
    private const GOLD = '#C99A4B';
    private const GRAY = '#5B6B7E';
    private const LINE = '#D9E1E6';
    private const SOFT = '#F2F6F7';

    public static function render(array $d): string
    {
        $p = new SimplePdf();
        $L = 45;                       // margin kiri
        $R = SimplePdf::W - 45;        // batas kanan
        $cw = $R - $L;

        // Header
        $p->rect(0, 0, SimplePdf::W, 112, self::NAVY);
        $p->rect(0, 112, SimplePdf::W, 4, self::GOLD);
        $p->text($L, 56, 'E-LIBRA', 26, true, '#FFFFFF');
        $p->text($L, 76, 'Perpustakaan Digital - SMK Negeri 5 Surakarta', 10, false, '#C9D6E3');
        $p->text($R, 54, 'BUKTI PEMINJAMAN BUKU', 15, true, '#FFFFFF', 'R');
        $p->text($R, 72, 'Dokumen resmi peminjaman perpustakaan', 9, false, '#C9D6E3', 'R');

        // Nomor transaksi + status
        $p->text($L, 152, 'NOMOR TRANSAKSI', 8, true, self::GRAY);
        $p->text($L, 178, $d['trx'], 22, true, self::NAVY);

        $badge = self::statusColor($d['status_key']);
        $bw = max(96, $p->width(strtoupper($d['status']), 10, true) + 28);
        $p->rect($R - $bw, 150, $bw, 30, $badge);
        $p->text($R - $bw / 2, 169, strtoupper($d['status']), 10, true, '#FFFFFF', 'C');
        $p->text($R, 144, 'STATUS', 8, true, self::GRAY, 'R');

        $p->line($L, 198, $R, 198, self::LINE, 1);

        $y = 214;
        $y = self::section($p, $L, $cw, $y, 'DATA PEMINJAM', [
            ['Nama Siswa', $d['nama']],
            ['NIS', $d['nis']],
            ['Kelas', $d['kelas']],
        ]);
        $y = self::section($p, $L, $cw, $y + 6, 'DATA BUKU', array_values(array_filter([
            ['Judul Buku', $d['judul']],
            ['Penulis', $d['penulis']],
            ['Kategori', $d['kategori']],
            $d['isbn'] !== '-' ? ['ISBN', $d['isbn']] : null,
        ])));
        $rows = [
            ['Tanggal Pinjam', $d['tgl_pinjam']],
            ['Lama Peminjaman', $d['lama']],
            ['Tanggal Pengembalian', $d['tgl_kembali']],
            ['Dikembalikan Pada', $d['dikembalikan']],
            ['Status', $d['status']],
        ];
        if ($d['denda'] > 0) {
            $rows[] = ['Denda', 'Rp'.number_format($d['denda'], 0, ',', '.')];
        }
        $y = self::section($p, $L, $cw, $y + 6, 'DETAIL PEMINJAMAN', $rows);

        // Ketentuan
        $y += 10;
        $notes = [
            'Tunjukkan bukti ini kepada petugas saat mengambil dan mengembalikan buku.',
            'Kembalikan buku paling lambat pada Tanggal Pengembalian di atas.',
            'Keterlambatan dikenakan denda Rp500 per hari. Jaga buku dari kerusakan dan kehilangan.',
        ];
        $h = 30 + count($notes) * 14;
        $p->rect($L, $y, $cw, $h, self::SOFT);
        $p->rect($L, $y, 4, $h, self::TEAL);
        $p->text($L + 16, $y + 19, 'KETENTUAN', 8, true, self::TEAL);
        $ny = $y + 36;
        foreach ($notes as $i => $n) {
            $p->text($L + 16, $ny, ($i + 1).'. '.$n, 9, false, self::NAVY);
            $ny += 14;
        }
        $y += $h + 26;

        // Tanda tangan
        $p->text($L + 60, $y, 'Peminjam', 9, false, self::GRAY, 'C');
        $p->text($R - 60, $y, 'Petugas Perpustakaan', 9, false, self::GRAY, 'C');
        $p->line($L, $y + 50, $L + 120, $y + 50, self::NAVY, 0.8);
        $p->line($R - 120, $y + 50, $R, $y + 50, self::NAVY, 0.8);
        $p->text($L + 60, $y + 64, $d['nama'], 9, true, self::NAVY, 'C');

        // Footer
        $p->line($L, 790, $R, 790, self::LINE, 1);
        $p->text(SimplePdf::W / 2, 806, 'Dicetak otomatis oleh sistem E-LIBRA pada '.$d['dicetak'], 8, false, self::GRAY, 'C');
        $p->rect(0, 836, SimplePdf::W, 6, self::NAVY);

        return $p->output();
    }

    private static function section(SimplePdf $p, float $L, float $cw, float $y, string $title, array $rows): float
    {
        $p->text($L, $y, $title, 9, true, self::TEAL);
        $p->line($L, $y + 6, $L + $cw, $y + 6, self::TEAL, 1.2);
        $y += 24;
        foreach ($rows as [$label, $value]) {
            $lines = $p->wrap((string) $value, $cw - 170, 11, true);
            $p->text($L, $y, $label, 10, false, self::GRAY);
            foreach ($lines as $i => $line) {
                $p->text($L + 170, $y + $i * 14, $line, 11, true, self::NAVY);
            }
            $y += max(1, count($lines)) * 14 + 6;
            $p->line($L, $y - 14, $L + $cw, $y - 14, self::LINE, 0.5);
        }

        return $y;
    }

    private static function statusColor(string $status): string
    {
        return match ($status) {
            'returned' => '#2F8F67',
            'late', 'rejected' => '#C0392B',
            'pending' => '#C99A4B',
            default => '#17707A',
        };
    }
}
