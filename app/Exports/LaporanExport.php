<?php

namespace App\Exports;

use App\Models\Setoran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromArray, WithEvents, WithTitle
{
    protected array $params;
    protected int $totalRows = 0;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Laporan Setoran';
    }

    public function array(): array
    {
        $bulan = $this->params['bulan'] ?? now()->format('m');
        $tahun = $this->params['tahun'] ?? now()->year;
        $jukirId = $this->params['jukir_id'] ?? null;
        $koordinator = $this->params['koordinator'] ?? null;

        $query = Setoran::with('jukir')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($jukirId, fn($q) => $q->where('jukir_id', $jukirId))
            ->when($koordinator, fn($q) => $q->whereHas('jukir', fn($q) =>
                $q->where('koordinator_wasda', $koordinator)
            ))
            ->orderBy('tanggal');

        $setorans = $query->get();

        $data = [
            ['LAPORAN SETORAN PARKIR'],
            ['Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y')],
            [''],
            [
                'No',
                'Tanggal',
                'No Absen',
                'Nama Jukir',
                'Lokasi',
                'Koordinator Wasda',
                'Setoran R2 (Rp)',
                'Setoran R4 (Rp)',
                'Total (Rp)'
            ]
        ];

        $totalR2 = 0;
        $totalR4 = 0;

        foreach ($setorans as $index => $item) {
            $r2 = $item->r2 ?? 0;
            $r4 = $item->r4 ?? 0;
            $totalRow = $r2 + $r4;

            $totalR2 += $r2;
            $totalR4 += $r4;

            $data[] = [
                $index + 1,
                Carbon::parse($item->tanggal)->format('d/m/Y'),
                $item->jukir->no_absen ?? '-',
                $item->jukir->nama ?? '-',
                $item->jukir->lokasi ?? '-',
                $item->jukir->koordinator_wasda ?? '-',
                $r2,
                $r4,
                $totalRow,
            ];
        }

        // Simpan jumlah baris data untuk styling AfterSheet
        $this->totalRows = count($setorans);

        // Baris Total
        $data[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            $totalR2,
            $totalR4,
            $totalR2 + $totalR4
        ];

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge Title Header
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');

                // Style Title
                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $startRowHeader = 4;
                $startRowData = 5;
                $lastDataRow = $startRowData + $this->totalRows - 1;
                $totalRowIndex = $lastDataRow + 1;

                // Merge Footer Label "TOTAL"
                if ($this->totalRows > 0) {
                    $sheet->mergeCells("A{$totalRowIndex}:F{$totalRowIndex}");
                }

                // Table Header Style
                $sheet->getStyle("A{$startRowHeader}:I{$startRowHeader}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Border Style untuk Seluruh Tabel
                $sheet->getStyle("A{$startRowHeader}:I{$totalRowIndex}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Formatting Nominal Rupiah / Angka
                $sheet->getStyle("G{$startRowData}:I{$totalRowIndex}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Alignment Kolom Data
                $sheet->getStyle("A{$startRowData}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$startRowData}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$startRowData}:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style Baris Total
                $sheet->getStyle("A{$totalRowIndex}:I{$totalRowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$totalRowIndex}:I{$totalRowIndex}")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2'],
                ]);

                // Auto Size Width Kolom
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
