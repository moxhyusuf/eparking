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
            $jukir = Jukir::where('no_absen', Auth::user()->absen)->firstOrFail();
            return redirect()->route('monitoring.show', $jukir->id);
        }

        $jukirs = Jukir::orderBy('id')->get();

        $bulanTersedia = Jukir::selectRaw('DISTINCT bulan, tahun')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->map(fn($j) => [
                'value' => $j->bulan,
                'label' => \Carbon\Carbon::createFromDate($j->tahun, $j->bulan, 1)->translatedFormat('F Y'),
                'tahun' => $j->tahun,
            ]);

        return view('monitoring.index', compact('jukirs', 'bulanTersedia'));
    }

    public function show(Request $request, $id)
    {
        // Jukir hanya boleh lihat data diri sendiri
        if (Auth::user()->role === 'jukir') {
            $jukir = Jukir::where('no_absen', Auth::user()->absen)->firstOrFail();
            if ($jukir->id != $id) {
                return redirect()->route('monitoring.show', $jukir->id);
            }
        }

        $jukir = Jukir::findOrFail($id);

        $setorans = Setoran::where('jukir_id', $id)
            ->orderBy('tanggal')
            ->get();

        $totalR2         = $setorans->sum('r2');
        $totalR4         = $setorans->sum('r4');
        $totalPendapatan = $totalR2 + $totalR4;

        return view('monitoring.show', compact(
            'jukir',
            'setorans',
            'totalR2',
            'totalR4',
            'totalPendapatan'
        ));
    }
}
