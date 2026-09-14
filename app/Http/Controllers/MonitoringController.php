<?php

namespace App\Http\Controllers;

use App\Models\Jukir;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role === 'jukir') {
            return $this->show($request);
        }

        $jukirs = Jukir::orderBy('id')->get();
        return view('monitoring.index', compact('jukirs'));
    }


    public function show(Request $request)
    {
        $jukir = Jukir::where('no_absen', Auth::user()->absen)->firstOrFail();

        $bulan = $request->bulan ?? '2026-07';
        [$tahun, $bln] = explode('-', $bulan);

        $setorans = Setoran::where('jukir_id', $jukir->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->orderBy('tanggal')
            ->get();

        $totalR2 = $setorans->sum('r2');
        $totalR4 = $setorans->sum('r4');


        return view('monitoring.detail', compact('jukir', 'setorans', 'bulan', 'totalR2', 'totalR4'));
    }

    public function detail(Request $request, $id)
    {

        if (Auth::user()->role === 'jukir') {
            return redirect()->route('monitoring.index');
        }

        $jukir = Jukir::findOrFail($id);

        $bulan = $request->bulan ?? '2026-07';
        [$tahun, $bln] = explode('-', $bulan);

        $setorans = Setoran::where('jukir_id', $id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->orderBy('tanggal')
            ->get();

        $totalR2 = $setorans->sum('r2');
        $totalR4 = $setorans->sum('r4');

        return view('monitoring.detail', compact('jukir', 'setorans', 'bulan', 'totalR2', 'totalR4'));
    }
}
