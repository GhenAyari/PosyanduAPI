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
    public function getDrafts(Request $request, $kelompok)
    {
        $user = $request->user();

        // Ambil ID Posyandu dari user yang login (Kader / Ketua)
        $posyanduId = in_array($user->role, ['ketua', 'kader']) ? $user->posyandu_id : null;

        $data = [];

        // Pilih model berdasarkan parameter {kelompok} yang dikirim dari React
        switch ($kelompok) {
            case 'balita':
                $query = PemeriksaanBalita::where('status_form', 'draft');
                if ($posyanduId) {
                    $query->whereHas('kader', function($q) use ($posyanduId) {
                        $q->where('posyandu_id', $posyanduId);
                    });
                }
                // Jika ingin mengambil nama anak di draf, gunakan ->with('anak')
                $data = $query->get();
                break;

            case 'remaja':
                $query = PemeriksaanRemaja::where('status_form', 'draft');
                if ($posyanduId) {
                    $query->whereHas('kader', function($q) use ($posyanduId) {
                        $q->where('posyandu_id', $posyanduId);
                    });
                }
                $data = $query->get();
                break;

            case 'hamil':
            case 'ibu':
                $query = PemeriksaanHamil::where('status_form', 'draft');
                if ($posyanduId) {
                    $query->whereHas('kader', function($q) use ($posyanduId) {
                        $q->where('posyandu_id', $posyanduId);
                    });
                }
                $data = $query->get();
                break;

            case 'lansia':
                $query = PemeriksaanLansia::where('status_form', 'draft');
                if ($posyanduId) {
                    $query->whereHas('kader', function($q) use ($posyanduId) {
                        $q->where('posyandu_id', $posyanduId);
                    });
                }
                $data = $query->get();
                break;

            default:
                return response()->json(['status' => 'gagal', 'pesan' => 'Kelompok sasaran tidak valid'], 400);
        }

        return response()->json([
            'status' => 'sukses',
            'data' => $data
        ]);
    }
}
