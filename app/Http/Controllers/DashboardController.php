<?php

namespace App\Http\Controllers;

use App\Models\Jukir;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Jukir::selectRaw('tahun, bulan')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        // fallback: jika belum ada data, gunakan bulan ini
        $defaultBulan = $periodes->isNotEmpty()
            ? sprintf('%04d-%02d', $periodes->first()->tahun, $periodes->first()->bulan)
            : now()->format('Y-m');

        $bulan = $request->bulan ?? $defaultBulan;
        [$tahun, $bln] = explode('-', $bulan);

        if (Auth::user()->role === 'jukir') {
            return $this->dashboardJukir($bulan, (int) $tahun, (int) $bln, $periodes);
        }

        return $this->dashboardAdmin($bulan, (int) $tahun, (int) $bln, $periodes);
    }

    private function dashboardAdmin(string $bulan, int $tahun, int $bln, $periodes)
    {
        $totalJukir = Jukir::where('tahun', $tahun)->where('bulan', $bln)->count();

        $totalPendapatanTahun = Setoran::whereYear('tanggal', $tahun)->sum(\DB::raw('r2 + r4'));

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
            $chartR2[] = (int) ($harian[$d]->r2 ?? 0);
            $chartR4[] = (int) ($harian[$d]->r4 ?? 0);
        }

        $perJukir = Jukir::where('jukir.tahun', $tahun)
            ->where('jukir.bulan', $bln)
            ->selectRaw('
                jukir.id,
                jukir.nama,
                COALESCE(SUM(s.r2), 0) as r2,
                COALESCE(SUM(s.r4), 0) as r4,
                COALESCE(SUM(s.r2 + s.r4), 0) as total
            ')
            ->leftJoin('setoran as s', function ($join) use ($tahun, $bln) {
                $join->on('s.jukir_id', '=', 'jukir.id')
                    ->whereYear('s.tanggal', $tahun)
                    ->whereMonth('s.tanggal', $bln);
            })
            ->groupBy('jukir.id', 'jukir.nama')
            ->orderByDesc('total')
            ->get();

        $jukirLabels = $perJukir->pluck('nama')->toArray();
        $jukirR2     = $perJukir->pluck('r2')->map(fn($v) => (int) $v)->toArray();
        $jukirR4     = $perJukir->pluck('r4')->map(fn($v) => (int) $v)->toArray();

        return view('dashboard.index', compact(
            'periodes', 'bulan', 'totalJukir',
            'totalR2', 'totalR4',
            'totalPendapatanTahun',
            'labels', 'chartR2', 'chartR4',
            'jukirLabels', 'jukirR2', 'jukirR4',
        ));
    }

    private function dashboardJukir(string $bulan, int $tahun, int $bln, $periodes)
    {
        $jukir = Jukir::where('no_absen', Auth::user()->absen)
            ->where('tahun', $tahun)
            ->where('bulan', $bln)
            ->firstOrFail();

        $setorans = Setoran::where('jukir_id', $jukir->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->orderBy('tanggal')
            ->get();

        $totalR2 = (int) $setorans->sum('r2');
        $totalR4 = (int) $setorans->sum('r4');

        $daysInMonth = Carbon::create($tahun, $bln, 1)->daysInMonth;
        $labels = $chartR2 = $chartR4 = [];
        $keyed  = $setorans->keyBy(fn($s) => Carbon::parse($s->tanggal)->day);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[]  = $d;
            $chartR2[] = (int) ($keyed[$d]->r2 ?? 0);
            $chartR4[] = (int) ($keyed[$d]->r4 ?? 0);
        }

        return view('dashboard.index', compact(
            'periodes', 'bulan', 'jukir', 'setorans',
            'totalR2', 'totalR4',
            'labels', 'chartR2', 'chartR4',
        ));
    }
}
