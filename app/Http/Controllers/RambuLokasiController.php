<?php

namespace App\Http\Controllers;

use App\Models\RambuLokasi;
use App\Models\LokasiParkir;
use App\Models\ZonaParkir;
use Illuminate\Http\Request;

class RambuLokasiController extends Controller
{
    public function index()
    {
        $rambus = RambuLokasi::orderBy('jenis_rambu')->get();
        $lokasis = LokasiParkir::all();
        $zonas   = ZonaParkir::all();
        return view('rambu-lokasi.index', compact('lokasis', 'rambus', 'zonas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'keterangan' => 'nullable|string|max:255',
            'jenis_rambu' => 'required|in:parkir,parkir_motor,parkir_mobil,parkir_disabilitas,dilarang_parkir',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
        ]);

        RambuLokasi::create($data);

        return redirect()->route('rambu-lokasi.index')->with('success', 'Rambu berhasil ditambahkan.');
    }

    public function update(Request $request, RambuLokasi $rambuLokasi)
    {
        $data = $request->validate([
            'keterangan' => 'nullable|string|max:255',
            'jenis_rambu' => 'required|in:parkir,parkir_motor,parkir_mobil,parkir_disabilitas,dilarang_parkir',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
        ]);

        $rambuLokasi->update($data);

        return redirect()->route('rambu-lokasi.index')->with('success', 'Rambu berhasil diperbarui.');
    }

    public function destroy(RambuLokasi $rambuLokasi)
    {
        $rambuLokasi->delete();

        return redirect()->route('rambu-lokasi.index')->with('success', 'Rambu berhasil dihapus.');
    }
}
