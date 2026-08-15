<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapKegiatan;

class RekapKegiatanController extends Controller
{
    // === FUNGSI AMBIL DATA RIWAYAT (GET) ===
    public function index(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Tarik data terbaru berdasarkan posyandu yang sedang login
        $riwayat = RekapKegiatan::where('posyandu_id', $posyanduId)
            ->latest() // urutkan dari yang paling baru
            ->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $riwayat
        ], 200);
    }

    // === FUNGSI SIMPAN DATA (POST) ===
    public function store(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $data = $request->all();
        $data['posyandu_id'] = $posyanduId;

        $textFields = ['kd_kec', 'kd_desa', 'rt', 'no_posyandu', 'bulan_pendataan'];

        foreach ($data as $key => $value) {
            if (!in_array($key, $textFields) && $key !== 'posyandu_id') {
                $data[$key] = empty($value) ? 0 : (int) $value;
            }
        }

        $rekap = RekapKegiatan::create($data);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Data Hasil Kegiatan Posyandu berhasil disimpan!',
            'data'   => $rekap
        ], 201);
    }

    // === FUNGSI HAPUS DATA (DELETE) ===
    public function destroy(Request $request, $id)
    {
        $posyanduId = $request->user()->posyandu_id;

        $rekap = RekapKegiatan::where('id', $id)->where('posyandu_id', $posyanduId)->first();

        if (!$rekap) {
            return response()->json(['pesan' => 'Data tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        $rekap->delete();

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Data rekap berhasil dihapus.'
        ], 200);
    }
}
