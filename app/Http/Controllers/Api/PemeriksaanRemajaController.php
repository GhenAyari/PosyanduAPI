<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PemeriksaanRemaja;
use App\Models\WargaRemaja;
use Illuminate\Support\Facades\Storage;

class PemeriksaanRemajaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pemeriksaan_id'     => 'nullable|integer',
            'remaja_id'          => 'required|string',
            'nama_remaja_baru'   => 'required_if:remaja_id,baru|string',
            'jenis_kelamin_baru' => 'required_if:remaja_id,baru|in:L,P',
            'tanggal_periksa'    => 'required|date',
            'umur_tahun'         => 'required|integer|min:0',
            'berat_badan'        => 'required|numeric',
            'tinggi_badan'       => 'required|numeric',
            'tekanan_darah'      => 'nullable|string',
            'status_imt'         => 'nullable|string',
            'status_form'        => 'required|in:draft,final',
            'dokumentasi_foto'   => 'nullable|array|max:5',
            'dokumentasi_foto.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $remajaId = $request->remaja_id;

        if (!$remajaId || $remajaId === 'baru' || $remajaId === 'null') {
            $tahunLahir = date('Y', strtotime($request->tanggal_periksa)) - $request->umur_tahun;
            $remajaBaru = \App\Models\WargaRemaja::create([
                'nama_remaja'   => $request->nama_remaja_baru,
                'jenis_kelamin' => $request->jenis_kelamin_baru,
                'tanggal_lahir' => $tahunLahir . '-01-01',
                'keluarga_id'   => null,
            ]);
            $remajaId = $remajaBaru->id;
        }

        $fotoPaths = [];
        if ($request->hasFile('dokumentasi_foto')) {
            foreach ($request->file('dokumentasi_foto') as $file) {
                $fotoPaths[] = $file->store('dokumentasi_kegiatan', 'public');
            }
        }

        $pemeriksaan = PemeriksaanRemaja::updateOrCreate(
            ['id' => $request->pemeriksaan_id],
            [
                'remaja_id'        => $remajaId,
                'kader_id'         => $request->user()->id,
                'tanggal_periksa'  => $request->tanggal_periksa,
                'umur_tahun'       => $request->umur_tahun,
                'berat_badan'      => $request->berat_badan,
                'tinggi_badan'     => $request->tinggi_badan,
                'tekanan_darah'    => $request->tekanan_darah,
                'status_imt'       => $request->status_imt,
                'status_form'      => $request->status_form,
                'dokumentasi_foto' => count($fotoPaths) > 0 ? $fotoPaths : null,
            ]
        );

        return response()->json([
            'status' => 'sukses',
            'pesan'  => $request->status_form === 'draft' ? 'Draf Remaja disimpan.' : 'Data pemeriksaan berhasil disimpan.',
            'data'   => $pemeriksaan
        ], 201);
    }

    // --- FUNGSI ADMIN ---
    public function getForAdmin(Request $request)
    {
        $posyanduId = $request->posyandu_id;
        $data = PemeriksaanRemaja::with('remaja')->whereHas('kader', function($query) use ($posyanduId) {
            $query->where('posyandu_id', $posyanduId);
        })->latest()->get();

        return response()->json(['status' => 'sukses', 'data' => $data]);
    }

    public function destroyForAdmin($id)
    {
        PemeriksaanRemaja::findOrFail($id)->delete();
        return response()->json(['status' => 'sukses', 'pesan' => 'Data berhasil dihapus.']);
    }
}
