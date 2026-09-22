<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            // Argumen 1: Kriteria Unik untuk Pencarian
            [
                'email' => 'superadmin@digimone.com',
            ],
            // Argumen 2: Nilai yang diisi HANYA jika record belum ditemukan
            [
                'name' => 'superadmin',
                'password' => 'superrahasia'
            ]
        );

        if (method_exists($superadmin, 'assignRole')) {
            $superadmin->assignRole('super_admin');
        }
    }
}
