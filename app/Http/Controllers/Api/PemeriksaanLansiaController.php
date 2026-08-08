<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemeriksaanLansia;
use App\Models\WargaDewasa;

class PemeriksaanLansiaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pemeriksaan_id' => 'nullable|integer',
            'lansia_id'          => 'nullable|string',
            'nama_lansia_baru'   => 'nullable|string',
            'jenis_kelamin_baru' => 'nullable|in:L,P',
            'tanggal_periksa'    => 'required|date',
            'berat_badan'        => 'required|numeric',
            'tinggi_badan'       => 'required|numeric',
            'lingkar_pinggang'   => 'nullable|numeric',
            'tekanan_darah'      => 'nullable|string',
            'tensi'              => 'nullable|in:Rendah,Normal,Tinggi',
            'gula_darah'         => 'nullable|integer',
            'nadi'               => 'nullable|integer',
            'status_imt'         => 'nullable|string',
            'status_form'        => 'required|in:draft,final',
            'dokumentasi_foto'   => 'nullable|array|max:5',
            'dokumentasi_foto.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $lansiaId = $request->lansia_id;

        // LOGIKA AUTO-DAFTAR LANSIA BARU
        if (!$lansiaId || $lansiaId === 'baru' || $lansiaId === 'null') {
            // Beri default umur lansia sekitar 60 tahun yang lalu dari sekarang
            $tanggalLahirPerkiraan = date('Y-m-d', strtotime('-60 years'));

            $lansiaBaru = WargaDewasa::create([
                'nama_lengkap'  => $request->nama_lansia_baru,
                'jenis_kelamin' => $request->jenis_kelamin_baru,
                'tanggal_lahir' => $tanggalLahirPerkiraan,
                'keluarga_id'   => null,
            ]);
            $lansiaId = $lansiaBaru->id;
        }

        // Proses unggah foto
        $fotoPaths = [];
        if ($request->hasFile('dokumentasi_foto')) {
            foreach ($request->file('dokumentasi_foto') as $file) {
                $fotoPaths[] = $file->store('dokumentasi_kegiatan', 'public');
            }
        }

        // Simpan data pemeriksaan
        $pemeriksaan = PemeriksaanRemaja::updateOrCreate(
            ['id' => $request->pemeriksaan_id],
            [
            'lansia_id'        => $lansiaId,
            'kader_id'         => $request->user()->id,
            'tanggal_periksa'  => $request->tanggal_periksa,
            'berat_badan'      => $request->berat_badan,
            'tinggi_badan'     => $request->tinggi_badan,
            'lingkar_pinggang' => $request->lingkar_pinggang,
            'tekanan_darah'    => $request->tekanan_darah,
            'tensi'            => $request->tensi,
            'gula_darah'       => $request->gula_darah,
            'nadi'             => $request->nadi,
            'status_imt'       => $request->status_imt,
            'status_form'      => $request->status_form,
            'dokumentasi_foto' => count($fotoPaths) > 0 ? $fotoPaths : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => $request->status_form === 'draft' ? 'Draf Lansia disimpan.' : 'Data Lansia berhasil disimpan.',
            'data'   => $pemeriksaan
        ], 201);
    }
}
