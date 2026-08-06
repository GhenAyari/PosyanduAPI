<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Posyandu
        $posyanduId = DB::table('posyandus')->insertGetId([
            'nama' => 'Posyandu Loa Duri Ulu',
            'alamat' => 'Jl. Kesehatan No. 1, Desa Loa Duri Ulu',
            'no_telepon' => '081234567890',
            'latitude' => -0.589123,
            'longitude' => 117.123456,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Buat Akun Kader (Petugas)
        DB::table('users')->insert([
            [
                'posyandu_id' => $posyanduId,
                'name' => 'Kader Melati',
                'username' => 'kader_melati',
                'email' => 'kader@posyandu.com',
                'password' => Hash::make('kader123'), // Password untuk login
                'role' => 'kader',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // 3. Buat Akun Warga
            [
                'posyandu_id' => $posyanduId,
                'name' => 'Bapak Budi Warga',
                'username' => 'budi_warga',
                'email' => 'budi@warga.com',
                'password' => Hash::make('warga123'), // Password untuk login
                'role' => 'warga',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
