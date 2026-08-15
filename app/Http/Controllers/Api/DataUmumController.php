<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataUmum;

class DataUmumController extends Controller
{
    // Ambil riwayat data
    public function index(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $riwayat = DataUmum::where('posyandu_id', $posyanduId)->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $riwayat
        ], 200);
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $data = $request->all();
        $data['posyandu_id'] = $posyanduId;

        // Daftar kolom yang berupa teks
        $textFields = ['nama_posyandu', 'rukun_warga', 'desa', 'kecamatan', 'tahun', 'bulan'];

        // Jika kolom angka kosong, ubah jadi 0
        foreach ($data as $key => $value) {
            if (!in_array($key, $textFields) && $key !== 'posyandu_id') {
                $data[$key] = empty($value) ? 0 : (int) $value;
            }
        }

        $dataUmum = DataUmum::create($data);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Pencatatan Data Umum berhasil disimpan!',
            'data'   => $dataUmum
        ], 201);
    }

    // Hapus data
    public function destroy(Request $request, $id)
    {
        $posyanduId = $request->user()->posyandu_id;
        $dataUmum = DataUmum::where('id', $id)->where('posyandu_id', $posyanduId)->first();

        if (!$dataUmum) {
            return response()->json(['pesan' => 'Data tidak ditemukan.'], 404);
        }

        $dataUmum->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Data berhasil dihapus.'], 200);
    }
}
