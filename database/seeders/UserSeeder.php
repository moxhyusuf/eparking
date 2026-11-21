<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(['username' => 'admin'], [
            'nama'     => 'Administrator',
            'absen'    => '0',
            'role'     => 'admin',
            'password' => Hash::make('123456'),
        ]);
    }
}
