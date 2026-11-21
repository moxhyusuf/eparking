<?php

namespace App\Http\Controllers;

use App\Models\Jukir;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        $users = User::where('role', 'jukir')
            ->with(['jukirs' => fn($q) => $q->where('bulan', $bulan)->where('tahun', $tahun)->orderBy('no_absen')])
            ->orderBy('nama')
            ->get();

        $unmapped = Jukir::whereNull('user_id')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('nama')
            ->get();

        return view('user.index', compact('users', 'unmapped', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'absen'    => 'required|string|max:255',
            'username' => 'required|string|unique:user,username|max:255',
            'role'     => 'required|in:admin,jukir',
            'password' => 'required|string|min:6',
        ]);

        User::create($data);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'absen'    => 'required|string|max:255',
            'username' => "required|string|unique:user,username,{$user->id}|max:255",
            'role'     => 'required|in:admin,jukir',
            'password' => 'nullable|string|min:6',
        ]);

        if (empty($data['password'])) unset($data['password']);

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:user,id',
            'jukir_ids'   => 'required|array|min:1',
            'jukir_ids.*' => 'exists:jukir,id',
        ]);

        Jukir::whereIn('id', $request->jukir_ids)
            ->update(['user_id' => $request->user_id]);

        return back()->with('success', count($request->jukir_ids) . ' jukir berhasil di-assign.');
    }

    public function detach(Jukir $jukir)
    {
        $jukir->update(['user_id' => null]);
        return back()->with('success', 'Mapping dicabut.');
    }
}
