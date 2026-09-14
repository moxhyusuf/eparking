<?php

namespace App\Http\Controllers;

use App\Models\SheetConfig;
use App\Services\SetoranImportService;
use Illuminate\Http\Request;

class SheetConfigController extends Controller
{
    public function index()
    {
        $configs = SheetConfig::orderBy('tahun')->orderBy('bulan')->get();
        return view('config.index', compact('configs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
            'gid'            => 'required|string|unique:sheet_config,gid',
            'bulan'          => 'required|integer|between:1,12',
            'tahun'          => 'required|integer|min:2020',
        ]);

        SheetConfig::create($request->only('spreadsheet_id', 'gid', 'bulan', 'tahun'));

        return back()->with('success', 'Konfigurasi berhasil ditambahkan.');
    }

    public function update(Request $request, SheetConfig $config)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
            'gid'            => 'required|string|unique:sheet_config,gid,' . $config->id,
            'bulan'          => 'required|integer|between:1,12',
            'tahun'          => 'required|integer|min:2020',
        ]);

        $config->update($request->only('spreadsheet_id', 'gid', 'bulan', 'tahun'));

        return back()->with('success', 'Konfigurasi berhasil diupdate.');
    }

    public function destroy(SheetConfig $config)
    {
        $config->delete();
        return back()->with('success', 'Konfigurasi berhasil dihapus.');
    }

    public function sync(SetoranImportService $service)
    {
        try {
            $service->importAll();
            return back()->with('success', 'Sync berhasil. Data setoran telah diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sync gagal: ' . $e->getMessage());
        }
    }
}
