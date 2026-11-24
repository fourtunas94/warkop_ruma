<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Admin Manajemen',
                'password'     => bcrypt('admin123'),
                'role'         => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'nama_lengkap' => 'Kasir Utama',
                'password'     => bcrypt('kasir123'),
                'role'         => 'kasir',
            ]
        );
    }
}
