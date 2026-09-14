<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Jukir;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? '07';
        $tahun = $request->tahun ?? '2026';
        $jukirId = $request->jukir_id;
        $koordinator = $request->koordinator;

        $query = Setoran::with('jukir')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($jukirId, fn($q) => $q->where('jukir_id', $jukirId))
            ->when($koordinator, fn($q) => $q->whereHas('jukir', fn($q) =>
                $q->where('koordinator_wasda', $koordinator)
            ))
            ->orderBy('tanggal');

        $setorans = $query->get();

        $jukirList       = Jukir::orderBy('nama')->get(['id', 'nama', 'no_absen']);
        $koordinatorList = Jukir::distinct()->whereNotNull('koordinator_wasda')
                            ->orderBy('koordinator_wasda')
                            ->pluck('koordinator_wasda');

        $totalR2 = $setorans->sum('r2');
        $totalR4 = $setorans->sum('r4');

        return view('laporan.index', compact(
            'setorans', 'jukirList', 'koordinatorList',
            'bulan', 'tahun', 'jukirId', 'koordinator',
            'totalR2', 'totalR4'
        ));
    }

    public function export(Request $request)
    {
        $params = [
            'bulan'       => $request->bulan ?? '07',
            'tahun'       => $request->tahun ?? '2026',
            'jukir_id'    => $request->jukir_id,
            'koordinator' => $request->koordinator,
        ];

        $filename = 'laporan_setoran_' . $params['tahun']
                  . '_' . str_pad($params['bulan'], 2, '0', STR_PAD_LEFT)
                  . '.xlsx';

        return Excel::download(new LaporanExport($params), $filename);
    }
}
