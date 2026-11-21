<?php

namespace App\Services;

use App\Models\Jukir;
use App\Models\Setoran;
use App\Models\SheetConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetoranImportService
{
    /**
     * Mapping index kolom CSV dari Google Sheets (confirmed via tinker + excel):
     *
     *   [0] = NAMA JUKIR
     *   [1] = NO ABSEN
     *   [2] = KOORDINATOR WASDAL
     *   [3] = SUB KOORDINATOR WASDAL
     *   [4] = LOKASI
     *   [5] = R2 hari 1   <-- DAY_START_COL
     *   [6] = R4 hari 1
     *   [7] = R2 hari 2
     *   [8] = R4 hari 2
     *   ...
     *  [65] = R2 hari 31
     *  [66] = R4 hari 31
     *  [67] = TOTAL PENDAPATAN  <-- diabaikan, tidak disimpan ke DB
     *
     * Baris (0-based array):
     *   [0] = kosong
     *   [1] = BULAN | Juli 2026
     *   [2] = header kolom
     *   [3] = nomor hari
     *   [4] = R2 / R4
     *   [5+] = data jukir
     */
    private const DATA_START_ROW_INDEX = 5;

    private const COL_NAMA      = 0;
    private const COL_NO_ABSEN  = 1;
    private const COL_KOOR      = 2;
    private const COL_SUB_KOOR  = 3;
    private const COL_LOKASI    = 4;
    private const DAY_START_COL = 5;

    public function importAll(): string
    {
        $sheets = SheetConfig::all();
        $summary = [];

        foreach ($sheets as $sheet) {
            try {
                $result = $this->importSheet(
                    $sheet->gid,
                    $sheet->tahun,
                    $sheet->bulan,
                    $sheet->spreadsheet_id
                );
                $summary[] = sprintf(
                    '%02d-%04d: %d jukir, %d setoran',
                    $sheet->bulan,
                    $sheet->tahun,
                    $result['jukir'],
                    $result['setoran']
                );
            } catch (\Throwable $e) {
                Log::error('Import setoran gagal', [
                    'gid'   => $sheet->gid,
                    'tahun' => $sheet->tahun,
                    'bulan' => $sheet->bulan,
                    'error' => $e->getMessage(),
                ]);
                $summary[] = sprintf(
                    '%02d-%04d: GAGAL (%s)',
                    $sheet->bulan,
                    $sheet->tahun,
                    $e->getMessage()
                );
            }
        }

        return 'Import selesai. ' . implode(' | ', $summary);
    }

    /**
     * @return array{jukir: int, setoran: int}
     */
    public function importSheet(string|int $gid, int $year, int $month, string $spreadsheetId): array
    {
        $rows        = $this->fetchCsv($gid, $spreadsheetId);
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $jukirCount   = 0;
        $setoranCount = 0;

        // index kolom r4 hari terakhir yang valid, kolom setelahnya adalah
        // TOTAL PENDAPATAN dan harus diabaikan
        $lastValidCol = self::DAY_START_COL + ($daysInMonth - 1) * 2 + 1;

        foreach (array_slice($rows, self::DATA_START_ROW_INDEX) as $row) {
            $nama    = trim($row[self::COL_NAMA]     ?? '');
            $noAbsen = trim($row[self::COL_NO_ABSEN] ?? '');

            $koor    = trim($row[self::COL_KOOR]     ?? '');
            $subKoor = trim($row[self::COL_SUB_KOOR] ?? '');

            // baris valid wajib punya nama, koordinator, dan sub koordinator
            // baris tanpa ketiganya (misal baris rekap "Rp -") dilewati
            if ($nama === '' || $koor === '' || $subKoor === '') {
                continue;
            }

            $jukir = Jukir::updateOrCreate(
                [
                    'no_absen' => $noAbsen,
                    'tahun'    => $year,
                    'bulan'    => $month,
                ],
                [
                    'nama'                  => $nama,
                    'koordinator_wasda'     => $koor,
                    'sub_koordinator_wasda' => $subKoor,
                    'lokasi'                => trim($row[self::COL_LOKASI] ?? ''),
                ]
            );
            $jukirCount++;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $r2Col = self::DAY_START_COL + ($day - 1) * 2;
                $r4Col = $r2Col + 1;

                // stop kalau kolom sudah melewati batas hari terakhir
                // atau kolom belum ada di row (sheet lebih pendek dari daysInMonth)
                if ($r4Col > $lastValidCol || !array_key_exists($r2Col, $row)) {
                    break;
                }

                Setoran::updateOrCreate(
                    [
                        'jukir_id' => $jukir->id,
                        'tanggal'  => sprintf('%04d-%02d-%02d', $year, $month, $day),
                    ],
                    [
                        'r2' => $this->parseRupiah($row[$r2Col] ?? ''),
                        'r4' => $this->parseRupiah($row[$r4Col] ?? ''),
                    ]
                );
                $setoranCount++;
            }
        }

        return ['jukir' => $jukirCount, 'setoran' => $setoranCount];
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

    /**
     * Handle semua format rupiah: "Rp14.000", "Rp -", "Rp-", "-", "", angka mentah.
     */
    private function parseRupiah(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return 0;
        }

        $value = str_replace(['Rp', '.', ' ', ','], '', $value);
        $value = trim($value, " \t\n\r\0\x0B-");

        return is_numeric($value) ? (int) $value : 0;
    }
}
