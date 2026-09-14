<?php

namespace Database\Seeders;

use App\Models\SheetConfig;
use Illuminate\Database\Seeder;

class SheetConfigSeeder extends Seeder
{
    public function run(): void
    {
        $spreadsheetId = '1gfXbVDS367atZP8H2UcC4hXH_5nkG928BsXxJj0PLrE';

        $sheets = [
            ['gid' => '1958663128', 'bulan' => 7, 'tahun' => 2026],
            ['gid' => '2061254802', 'bulan' => 8, 'tahun' => 2026],
        ];

        foreach ($sheets as $sheet) {
            SheetConfig::updateOrCreate(
                ['gid' => $sheet['gid']],
                [...$sheet, 'spreadsheet_id' => $spreadsheetId]
            );
        }
    }
}
