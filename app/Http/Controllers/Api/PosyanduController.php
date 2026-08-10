<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Posyandu; // PENTING: Gunakan Model agar bisa pakai relasi jadwal
use App\Models\Jadwal;   // PENTING: Import model Jadwal
use Illuminate\Support\Facades\Storage;

class PosyanduController extends Controller
{
    // ==========================================================
    // 1. UNTUK HALAMAN PUBLIK (BERANDA)
    // ==========================================================
    public function index()
    {
        // Menggunakan Model Posyandu dan menambahkan ->with('jadwal')
        $posyandus = Posyandu::with('jadwal')->get();

        if ($posyandus->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Posyandu tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Data Profil 9 Posyandu berhasil diambil',
            'data' => $posyandus
        ]);
    }

    // ==========================================================
    // 2. UNTUK DASBOR KETUA/KADER (Ambil Data Posyandunya Saja)
    // ==========================================================
    public function getMe(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Menggunakan Model Posyandu dan menambahkan ->with('jadwal')
        $posyandu = Posyandu::with('jadwal')->where('id', $posyanduId)->first();

        return response()->json([
            'status' => 'sukses',
            'data' => $posyandu
        ]);
    }

    // ==========================================================
    // 3. UNTUK DASBOR KETUA/KADER (Simpan Pembaruan Profil & Jadwal)
    // ==========================================================
    public function updateMe(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Ambil semua data teks KECUALI foto dan keterangan_waktu
        // (karena keterangan_waktu akan disimpan di tabel terpisah)
        $updateData = $request->except(['foto', 'keterangan_waktu']);

        // Tangani unggah foto jika kader memasukkan gambar baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $path = $file->store('profil_posyandu', 'public');
            $updateData['foto'] = $path;
        }

        // 1. Update ke tabel posyandus
        Posyandu::where('id', $posyanduId)->update($updateData);

        // 2. Update atau Buat data Jadwal di tabel 'jadwal'
        // (Hanya jalan jika form yang disubmit mengirim parameter keterangan_waktu)
        if ($request->has('keterangan_waktu')) {
            Jadwal::updateOrCreate(
                ['posyandu_id' => $posyanduId], // Cari berdasarkan ID Posyandu
                ['keterangan_waktu' => $request->keterangan_waktu] // Update waktu
            );
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Profil Posyandu dan Jadwal berhasil diperbarui!'
        ]);
    }
}
