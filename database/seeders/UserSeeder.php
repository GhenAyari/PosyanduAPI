<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Posyandu;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftar 9 Posyandu Tetap
        $daftarPosyandu = [
            'Melati',
            'Rukun Lestari',
            'Mawar',
            'Bina Putra',
            'Nusa Indah',
            'Cempaka',
            'Tunas Mulya',
            'Surya',
            'Terkini'
        ];

        $posyanduData = [];

        // Looping untuk membuat Posyandu dan Akun Pengelolanya secara otomatis
        foreach ($daftarPosyandu as $nama) {
            // Buat Posyandu
            $posyandu = Posyandu::updateOrCreate(
                ['nama' => $nama],
                ['alamat' => 'Desa Loa Duri Ulu'] // Alamat bawaan
            );
            $posyanduData[$nama] = $posyandu->id; // Simpan ID untuk referensi

            // Format username (menghapus spasi dan mengubah jadi huruf kecil)
            // Contoh: 'Rukun Lestari' menjadi 'rukunlestari'
            $usernameSuffix = strtolower(str_replace(' ', '', $nama));

            // Buat Akun Ketua otomatis
            User::updateOrCreate(
                ['username'  => 'ketua.' . $usernameSuffix],
                [
                    'name'        => 'Ketua ' . $nama,
                    'role'        => 'ketua',
                    'posyandu_id' => $posyandu->id,
                    'password'    => 'password123',
                ]
            );

            // Buat Akun Kader otomatis
            User::updateOrCreate(
                ['username'  => 'kader.' . $usernameSuffix],
                [
                    'name'        => 'Kader ' . $nama,
                    'role'        => 'kader',
                    'posyandu_id' => $posyandu->id,
                    'password'    => 'password123',
                ]
            );
        }

        // 2. Akun Superadmin (Perangkat Desa)
        User::updateOrCreate(
            ['username'  => 'admin.desa'],
            [
                'name'        => 'Admin Desa Loa Duri Ulu',
                'role'        => 'superadmin',
                'posyandu_id' => null,
                'password'    => 'password123',
            ]
        );

        // 3. Akun Petugas Puskesmas
        User::updateOrCreate(
            ['username'  => 'petugas.puskesmas'],
            [
                'name'        => 'Bidan Fitri',
                'role'        => 'puskesmas',
                'posyandu_id' => null,
                'password'    => 'password123',
            ]
        );

        // 4. Akun Warga Contoh (Terdaftar di Posyandu Melati)
        User::updateOrCreate(
            ['username'  => 'warga.budi'],
            [
                'name'        => 'Budi Santoso',
                'role'        => 'warga',
                'posyandu_id' => $posyanduData['Melati'], // Mengambil ID Melati dari array
                'password'    => '6472010101900001',
            ]
        );
    }
}
