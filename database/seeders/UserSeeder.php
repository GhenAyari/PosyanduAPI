<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Superadmin (Perangkat Desa)
        User::updateOrCreate(
            ['username'  => 'admin.desa'], // Yang dicari
            [ // Yang diperbarui / dibuat
                'name'      => 'Admin Desa Loa Duri Ulu',
                'role'      => 'superadmin',
                'posyandu'  => null,
                'password'  => Hash::make('password123'),
            ]
        );

        // 2. Akun Petugas Puskesmas
        User::updateOrCreate(
            ['username'  => 'petugas.puskesmas'],
            [
                'name'      => 'Bidan Fitri',
                'role'      => 'puskesmas',
                'posyandu'  => null,
                'password'  => Hash::make('password123'),
            ]
        );

        // 3. Akun Ketua Posyandu
        User::updateOrCreate(
            ['username'  => 'ketua.melati'],
            [
                'name'      => 'Ibu Fatmawati',
                'role'      => 'ketua',
                'posyandu'  => 'Melati',
                'password'  => Hash::make('password123'),
            ]
        );

        // 4. Akun Kader Posyandu
        User::updateOrCreate(
            ['username'  => 'kader.melati'],
            [
                'name'      => 'Kader Siti',
                'role'      => 'kader',
                'posyandu'  => 'Melati',
                'password'  => Hash::make('password123'),
            ]
        );

        // 5. Akun Warga (Password menggunakan simulasi NIK)
        User::updateOrCreate(
            ['username'  => 'warga.budi'],
            [
                'name'      => 'Budi Santoso',
                'role'      => 'warga',
                'posyandu'  => 'Melati',
                'password'  => Hash::make('6472010101900001'), // Contoh NIK
            ]
        );
    }
}
