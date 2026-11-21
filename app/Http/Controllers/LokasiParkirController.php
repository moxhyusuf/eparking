<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\RambuLokasi;
use App\Models\ZonaParkir;
use Illuminate\Http\Request;

class LokasiParkirController extends Controller
{
    public function index()
    {
        $lokasis = LokasiParkir::orderBy('nama_jukir')->get();
        $rambus  = RambuLokasi::all();
        $zonas   = ZonaParkir::all();
        return view('lokasi-parkir.index', compact('lokasis', 'rambus', 'zonas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_jukir'  => 'required|string|max:255',
            'nama_lokasi' => 'nullable|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ]);

        $lokasi = LokasiParkir::create($data);

        return redirect()->route('lokasi-parkir.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function update(Request $request, LokasiParkir $lokasiParkir)
    {
        $data = $request->validate([
            'nama_jukir'  => 'required|string|max:255',
            'nama_lokasi' => 'nullable|string|max:255',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ]);

        $lokasiParkir->update($data);

        return redirect()->route('lokasi-parkir.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(LokasiParkir $lokasiParkir)
    {
        $lokasiParkir->delete();

        return redirect()->route('lokasi-parkir.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
