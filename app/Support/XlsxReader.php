<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

// Pembaca .xlsx sederhana (tanpa paket tambahan): mengembalikan baris-baris sheet sebagai array string.
class XlsxReader
{
    public static function rows(string $path, int $sheetIndex = 1): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif. Simpan file Excel sebagai CSV lalu impor CSV-nya.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak bisa dibuka (rusak atau bukan .xlsx).');
        }

        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sst = new SimpleXMLElement($xml);
            foreach ($sst->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } else {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $shared[] = $text;
            }
        }

        $sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml");
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException("Sheet nomor {$sheetIndex} tidak ditemukan.");
        }

        $sheet = new SimpleXMLElement($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $col = self::columnIndex((string) $c['r']);
                $type = (string) $c['t'];
                if ($type === 's') {
                    $val = $shared[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string) $c->is->t;
                } else {
                    $val = (string) $c->v;
                }
                $cells[$col] = trim($val);
            }
            if ($cells) {
                $max = max(array_keys($cells));
                $line = [];
                for ($i = 0; $i <= $max; $i++) {
                    $line[] = $cells[$i] ?? '';
                }
                $rows[] = $line;
            }
        }

        return $rows;
    }

    private static function columnIndex(string $ref): int
    {
        preg_match('/^[A-Z]+/i', $ref, $m);
        $letters = strtoupper($m[0] ?? 'A');
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return $n - 1;
    }
}
