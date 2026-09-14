<?php

namespace App\Services;

use App\Models\Jukir;
use App\Models\Setoran;
use App\Models\SheetConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SetoranImportService
{
    private const DATA_START_ROW = 5;
    private const DAY_START_COL = 5;

    public function importAll(): string
    {
        $sheets = SheetConfig::all();

        foreach ($sheets as $sheet) {
            $this->importSheet($sheet->gid, $sheet->tahun, $sheet->bulan, $sheet->spreadsheet_id);
        }
        return 'Import selesai';
    }

    public function importSheet(string|int $gid, int $year, int $month, string $spreadsheetId): void
    {
        $rows = $this->fetchCsv($gid, $spreadsheetId);

        foreach (array_slice($rows, self::DATA_START_ROW) as $row) {
            $noAbsen = trim($row[1] ?? '');
            $nama = trim($row[0] ?? '');

            if ($noAbsen === '' || $nama === '') {
                continue;
            }

            $jukir = Jukir::updateOrCreate(
                ['no_absen' => $noAbsen],
                [
                    'nama' => $nama,
                    'koordinator_wasda' => trim($row[2] ?? ''),
                    'sub_koordinator_wasda' => trim($row[3] ?? ''),
                    'lokasi' => trim($row[4] ?? ''),
                ]
            );

            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $r2Col = self::DAY_START_COL + ($day - 1) * 2;
                $r4Col = $r2Col + 1;

                if (!array_key_exists($r4Col, $row)) {
                    break;
                }

                Setoran::updateOrCreate(
                    [
                        'jukir_id' => $jukir->id,
                        'tanggal' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                    ],
                    [
                        'r2' => $this->parseRupiah($row[$r2Col] ?? ''),
                        'r4' => $this->parseRupiah($row[$r4Col] ?? ''),
                    ]
                );
            }
        }
    }

    private function fetchCsv(string|int $gid, string $spreadsheetId): array
    {
        $url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
            $spreadsheetId,
            $gid
        );

        $csv = Http::throw()->get($url)->body();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $rows = [];
        while (($line = fgetcsv($stream)) !== false) {
            $rows[] = $line;
        }
        fclose($stream);

        return $rows;
    }

    private function parseRupiah(string $value): int
    {
        $value = str_replace(['Rp', '.', ' '], '', $value);
        return is_numeric($value) ? (int) $value : 0;
    }
}
