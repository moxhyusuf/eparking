<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role', 'asc')->get();
        return view('user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'absen'    => 'required|string|unique:user,absen',
            'username' => 'required|string|unique:user,username',
            'role'     => 'required|in:admin,jukir',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nama'     => $request->nama,
            'absen'    => $request->absen,
            'username' => $request->username,
            'role'     => $request->role,
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'absen'    => ['required', 'string', Rule::unique('user', 'absen')->ignore($user->id)],
            'username' => ['required', 'string', Rule::unique('user', 'username')->ignore($user->id)],
            'role'     => 'required|in:admin,jukir',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('nama', 'absen', 'username', 'role');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
