<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PencatatanKegiatan;

class PencatatanKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $riwayat = PencatatanKegiatan::where('posyandu_id', $posyanduId)->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $riwayat
        ], 200);
    }

    public function store(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $data = $request->all();
        $data['posyandu_id'] = $posyanduId;

        $textFields = ['nama_posyandu', 'ketua_pelaksana', 'signature_data'];

        // Ubah string kosong jadi 0 untuk input angka
        foreach ($data as $key => $value) {
            if (!in_array($key, $textFields) && $key !== 'posyandu_id') {
                $data[$key] = empty($value) ? 0 : (int) $value;
            }
        }

        $pencatatan = PencatatanKegiatan::create($data);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Pencatatan Kegiatan 13 Poin berhasil disimpan!',
            'data'   => $pencatatan
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $posyanduId = $request->user()->posyandu_id;
        $pencatatan = PencatatanKegiatan::where('id', $id)->where('posyandu_id', $posyanduId)->first();

        if (!$pencatatan) return response()->json(['pesan' => 'Data tidak ditemukan.'], 404);

        $pencatatan->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Data berhasil dihapus.'], 200);
    }
}
