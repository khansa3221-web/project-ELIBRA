<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pembaca CSV sederhana. Otomatis mengenali pemisah "," atau ";"
 * (Excel versi Indonesia biasanya memakai ";") dan membuang BOM.
 */
class Csv
{
    /** @return array<int, array<string,string>> baris dengan kunci = nama kolom (huruf kecil) */
    public static function read(string $path): array
    {
        $raw = (string) file_get_contents($path);
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $firstLine = strtok($raw, "\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        $header = null;
        $rows = [];
        while (($cols = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            if (count(array_filter($cols, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // baris kosong
            }
            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim((string) $h)), $cols);
                continue;
            }
            $row = [];
            foreach ($header as $i => $name) {
                $row[$name] = trim((string) ($cols[$i] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /** Ambil nilai pertama yang terisi dari beberapa kemungkinan nama kolom. */
    public static function pick(array $row, array $names): string
    {
        foreach ($names as $n) {
            if (isset($row[$n]) && $row[$n] !== '') {
                return $row[$n];
            }
        }

        return '';
    }

    public static function download(string $filename, array $header, array $examples): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $examples) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, $header, ';', '"', '');
            foreach ($examples as $line) {
                fputcsv($out, $line, ';', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
