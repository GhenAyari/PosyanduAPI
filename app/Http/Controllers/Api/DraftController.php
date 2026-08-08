<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemeriksaanBalita;
use App\Models\PemeriksaanRemaja;
use App\Models\PemeriksaanHamil;
use App\Models\PemeriksaanLansia;

class DraftController extends Controller
{
    public function getDrafts($kelompok)
    {
        $data = [];

        // Mengambil data berdasarkan parameter kelompok di URL
        if ($kelompok === 'balita') {
            $data = PemeriksaanBalita::where('status_form', 'draft')->get();
        } elseif ($kelompok === 'remaja') {
            $data = PemeriksaanRemaja::where('status_form', 'draft')->get();
        } elseif ($kelompok === 'hamil') {
            $data = PemeriksaanHamil::where('status_form', 'draft')->get();
        } elseif ($kelompok === 'lansia') {
            $data = PemeriksaanLansia::where('status_form', 'draft')->get();
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $data
        ]);
    }
}
