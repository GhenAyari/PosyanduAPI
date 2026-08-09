<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormulirIdentifikasi;

class FormulirIdentifikasiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bidang'             => 'required|in:pendidikan,pekerjaan_umum,perumahan_rakyat,trantibumlinmas,sosial',
            'sub_bidang'         => 'required|string',
            'data_formulir'      => 'required|json', // React harus mengirim object JSON yg di-stringify
            'dokumentasi_foto'   => 'nullable|array',
            'dokumentasi_foto.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $posyanduId = $request->user()->posyandu_id;

        // Proses unggah foto (jika ada)
        $fotoPaths = [];
        if ($request->hasFile('dokumentasi_foto')) {
            foreach ($request->file('dokumentasi_foto') as $file) {
                $fotoPaths[] = $file->store('formulir_foto', 'public');
            }
        }

        $formulir = \App\Models\FormulirIdentifikasi::create([
            'posyandu_id'      => $posyanduId,
            'kader_id'         => $request->user()->id,
            'bidang'           => $request->bidang,
            'sub_bidang'       => $request->sub_bidang,
            'data_formulir'    => json_decode($request->data_formulir, true),
            'dokumentasi_foto' => count($fotoPaths) > 0 ? $fotoPaths : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Formulir Identifikasi berhasil disimpan.',
            'data'   => $formulir
        ], 201);
    }
    public function index(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Ambil data formulir khusus posyandu ini, urutkan dari yang terbaru
        $formulir = \App\Models\FormulirIdentifikasi::where('posyandu_id', $posyanduId)->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $formulir
        ]);
    }
    // KHUSUS SUPERADMIN: Mengambil formulir berdasarkan Posyandu
    public function getAllForAdmin(Request $request)
    {
        $query = \App\Models\FormulirIdentifikasi::query();

        // Filter jika Admin Desa ingin melihat posyandu tertentu
        if ($request->has('posyandu_id')) {
            $query->where('posyandu_id', $request->posyandu_id);
        }

        $data = $query->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $data
        ]);
    }
    // KHUSUS SUPERADMIN: Menghapus Formulir
    public function destroyForAdmin($id)
    {
        $formulir = \App\Models\FormulirIdentifikasi::findOrFail($id);
        $formulir->delete();

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Data formulir berhasil dihapus.'
        ]);
    }
}
