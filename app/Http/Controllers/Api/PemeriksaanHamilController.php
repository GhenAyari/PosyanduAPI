<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemeriksaanHamil;
use App\Models\WargaDewasa;

class PemeriksaanHamilController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pemeriksaan_id' => 'nullable|integer',
            'ibu_id'                  => 'nullable|string',
            'nama_ibu_baru'           => 'nullable|string',
            'tanggal_periksa'         => 'required|date',
            'usia_kehamilan_minggu'   => 'required|integer|min:1',
            'berat_badan'             => 'required|numeric',
            'tinggi_badan'            => 'required|numeric',
            'tekanan_darah'           => 'nullable|string',
            'lingkar_perut'           => 'nullable|numeric',
            'lingkar_lengan'          => 'nullable|numeric',
            'status_kek'              => 'required|in:Ya,Tidak',
            'anemia'                  => 'required|in:Ya,Tidak',
            'status_imt'              => 'nullable|string',
            'status_form'             => 'required|in:draft,final',
            'dokumentasi_foto'        => 'nullable|array|max:5',
            'dokumentasi_foto.*'      => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $ibuId = $request->ibu_id;

        // LOGIKA AUTO-DAFTAR: Jika ibu hamil belum ada di sistem
        if (!$ibuId || $ibuId === 'baru' || $ibuId === 'null') {
            // Karena UI tidak meminta umur, kita beri default perkiraan lahir 25 tahun lalu
            $tanggalLahirPerkiraan = date('Y-m-d', strtotime('-25 years'));

            $ibuBaru = WargaDewasa::create([
                'nama_lengkap'  => $request->nama_ibu_baru,
                'jenis_kelamin' => 'P', // Otomatis diset Perempuan
                'tanggal_lahir' => $tanggalLahirPerkiraan,
                'keluarga_id'   => null,
            ]);
            $ibuId = $ibuBaru->id;
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
            'ibu_id'                  => $ibuId,
            'kader_id'                => $request->user()->id,
            'tanggal_periksa'         => $request->tanggal_periksa,
            'usia_kehamilan_minggu'   => $request->usia_kehamilan_minggu,
            'berat_badan'             => $request->berat_badan,
            'tinggi_badan'            => $request->tinggi_badan,
            'tekanan_darah'           => $request->tekanan_darah,
            'lingkar_perut'           => $request->lingkar_perut,
            'lingkar_lengan'          => $request->lingkar_lengan,
            'status_kek'              => $request->status_kek,
            'anemia'                  => $request->anemia,
            'status_imt'              => $request->status_imt,
            'status_form'             => $request->status_form,
            'dokumentasi_foto'        => count($fotoPaths) > 0 ? $fotoPaths : null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => $request->status_form === 'draft' ? 'Draf Ibu Hamil disimpan.' : 'Data Ibu Hamil berhasil disimpan.',
            'data'   => $pemeriksaan
        ], 201);
    }
}
