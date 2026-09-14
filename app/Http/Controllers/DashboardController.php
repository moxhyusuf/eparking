<?php

namespace App\Http\Controllers;

use App\Models\Jukir;
use App\Models\Setoran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? '2026-07';
        [$tahun, $bln] = explode('-', $bulan);

        if (Auth::user()->role === 'jukir') {
            return $this->dashboardJukir($bulan, $tahun, $bln);
        }

        return $this->dashboardAdmin($bulan, $tahun, $bln);
    }

  private function dashboardAdmin(string $bulan, string $tahun, string $bln)
{
    $totalJukir = Jukir::count();
    $totalUser  = User::count();

    $setoranBulan = Setoran::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);

    $totalR2 = (clone $setoranBulan)->sum('r2');
    $totalR4 = (clone $setoranBulan)->sum('r4');

    $harian = Setoran::whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $bln)
        ->selectRaw('DAY(tanggal) as hari, SUM(r2) as r2, SUM(r4) as r4')
        ->groupBy('hari')
        ->orderBy('hari')
        ->get()
        ->keyBy('hari');

    $daysInMonth = Carbon::create($tahun, $bln, 1)->daysInMonth;
    $labels = $chartR2 = $chartR4 = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $labels[]  = $d;
        $chartR2[] = $harian[$d]->r2 ?? 0;
        $chartR4[] = $harian[$d]->r4 ?? 0;
    }

    $perJukir = Setoran::whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $bln)
        ->selectRaw('jukir_id, SUM(r2) as r2, SUM(r4) as r4, SUM(r2+r4) as total')
        ->groupBy('jukir_id')
        ->orderByDesc('total')
        ->with('jukir')
        ->get();

    $jukirLabels = $perJukir->map(fn($s) => $s->jukir->nama ?? '-')->values()->toArray();
    $jukirR2     = $perJukir->pluck('r2')->toArray();
    $jukirR4     = $perJukir->pluck('r4')->toArray();

    return view('dashboard.index', compact(
        'bulan', 'totalJukir', 'totalUser',
        'totalR2', 'totalR4',
        'labels', 'chartR2', 'chartR4',
        'jukirLabels', 'jukirR2', 'jukirR4',
    ));
}

    private function dashboardJukir(string $bulan, string $tahun, string $bln)
    {
        $jukir = \App\Models\Jukir::where('no_absen', Auth::user()->absen)->firstOrFail();

        $setorans = Setoran::where('jukir_id', $jukir->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->orderBy('tanggal')
            ->get();

        $totalR2 = $setorans->sum('r2');
        $totalR4 = $setorans->sum('r4');

        $daysInMonth = Carbon::create($tahun, $bln, 1)->daysInMonth;
        $labels = $chartR2 = $chartR4 = [];
        $keyed = $setorans->keyBy(fn($s) => Carbon::parse($s->tanggal)->day);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[]  = $d;
            $chartR2[] = $keyed[$d]->r2 ?? 0;
            $chartR4[] = $keyed[$d]->r4 ?? 0;
        }

        return view('dashboard.index', compact(
            'bulan', 'jukir', 'setorans',
            'totalR2', 'totalR4',
            'labels', 'chartR2', 'chartR4'
        ));
    }
}
