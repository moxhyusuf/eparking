<?php

namespace App\Http\Controllers;

use App\Models\ZonaParkir;
use App\Models\RambuLokasi;
use App\Models\LokasiParkir;

use Illuminate\Http\Request;


class ZonaParkirController extends Controller
{
    public function index()
    {
        $zonas = ZonaParkir::orderBy('nama')->get();
        $lokasis = LokasiParkir::all();
        $rambus  = RambuLokasi::all();
        return view('zona-parkir.index', compact('lokasis', 'rambus', 'zonas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'       => 'nullable|string|max:255',
            'status'     => 'required|in:boleh,dilarang',
            'koordinat'  => 'required|json',
        ]);

        $data['koordinat'] = json_decode($data['koordinat'], true);

        ZonaParkir::create($data);

        return redirect()->route('zona-parkir.index')->with('success', 'Zona berhasil ditambahkan.');
    }

    public function update(Request $request, ZonaParkir $zonaParkir)
    {
        $data = $request->validate([
            'nama'       => 'nullable|string|max:255',
            'status'     => 'required|in:boleh,dilarang',
            'koordinat'  => 'required|json',
        ]);

        $data['koordinat'] = json_decode($data['koordinat'], true);

        $zonaParkir->update($data);

        return redirect()->route('zona-parkir.index')->with('success', 'Zona berhasil diperbarui.');
    }

    public function destroy(ZonaParkir $zonaParkir)
    {
        $zonaParkir->delete();

        return redirect()->route('zona-parkir.index')->with('success', 'Zona berhasil dihapus.');
    }
}
