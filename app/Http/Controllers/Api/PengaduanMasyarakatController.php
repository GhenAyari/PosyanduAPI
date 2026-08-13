<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengaduanMasyarakat;

class PengaduanMasyarakatController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bidang'         => 'required|in:pendidikan,pekerjaan_umum,perumahan_rakyat,trantibumlinmas,sosial',
            'nama_pelapor'   => 'required|string',
            'jenis_kelamin'  => 'required|in:L,P',
            'nik'            => 'required|string|size:16',
            'no_hp'          => 'nullable|string',
            'alamat'         => 'required|string',
            'isi_keluhan'    => 'required|string',
            'lokasi_masalah' => 'nullable|string',
            'lampiran'       => 'nullable|array|max:3',
            'lampiran.*'     => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:2048'
        ]);

        $posyanduId = $request->user()->posyandu_id;

        $lampiranPaths = [];
        // PERBAIKAN: Tangkap file lampiran langsung
        if ($request->file('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $lampiranPaths[] = $file->store('pengaduan_lampiran', 'public');
            }
        }

        $pengaduan = PengaduanMasyarakat::create([
            'posyandu_id'    => $posyanduId,
            'bidang'         => $request->bidang,
            'nama_pelapor'   => $request->nama_pelapor,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'nik'            => $request->nik,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'isi_keluhan'    => $request->isi_keluhan,
            'lokasi_masalah' => $request->lokasi_masalah,

            // PERBAIKAN: Wajib dibungkus json_encode!
            'lampiran'       => count($lampiranPaths) > 0 ? json_encode($lampiranPaths) : null,
            'status'         => 'menunggu'
        ]);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Pengaduan berhasil dikirim dan menunggu tindak lanjut.',
            'data'   => $pengaduan
        ], 201);
    }
    public function index(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Ambil data pengaduan khusus posyandu ini, urutkan dari yang terbaru
        $pengaduan = PengaduanMasyarakat::where('posyandu_id', $posyanduId)->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $pengaduan
        ]);
    }
    // KHUSUS SUPERADMIN: Mengambil pengaduan berdasarkan Posyandu
    public function getAllForAdmin(Request $request)
    {
        $query = \App\Models\PengaduanMasyarakat::query();

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

    // KHUSUS SUPERADMIN: Mengubah status pengaduan
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai'
        ]);

        $pengaduan = \App\Models\PengaduanMasyarakat::findOrFail($id);
        $pengaduan->update(['status' => $request->status]);

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Status pengaduan berhasil diperbarui.',
            'data'   => $pengaduan
        ]);
    }
    // KHUSUS SUPERADMIN: Mengambil waktu terakhir ada pergerakan data di tiap posyandu
    // KHUSUS SUPERADMIN: Mengambil waktu terakhir ada pergerakan data di tiap posyandu
    // KHUSUS SUPERADMIN: Mengambil waktu terakhir ada pergerakan data di tiap posyandu
    public function getLatestUpdateTiapPosyandu()
    {
        // 1. Ambil update terakhir dari tabel Pengaduan
        $pengaduanUpdates = \App\Models\PengaduanMasyarakat::selectRaw('posyandu_id, MAX(created_at) as last_update')
            ->groupBy('posyandu_id')
            ->pluck('last_update', 'posyandu_id')
            ->toArray();

        // 2. Ambil update terakhir dari tabel Formulir Identifikasi
        $formulirUpdates = \App\Models\FormulirIdentifikasi::selectRaw('posyandu_id, MAX(created_at) as last_update')
            ->groupBy('posyandu_id')
            ->pluck('last_update', 'posyandu_id')
            ->toArray();

        // 3. Gabungkan semua ID Posyandu yang punya data
        $posyanduIds = collect(array_keys($pengaduanUpdates))
            ->merge(array_keys($formulirUpdates))
            ->unique();

        $finalUpdates = [];

        // 4. Bandingkan waktunya dengan logika IF yang pasti (tanpa fungsi max yang labil)
        foreach ($posyanduIds as $id) {
            $waktuPengaduan = $pengaduanUpdates[$id] ?? null;
            $waktuFormulir  = $formulirUpdates[$id] ?? null;

            if ($waktuPengaduan && $waktuFormulir) {
                // Jika keduanya ada, pilih yang paling baru
                $finalUpdates[$id] = $waktuPengaduan > $waktuFormulir ? $waktuPengaduan : $waktuFormulir;
            } else {
                // Jika salah satu kosong, ambil yang ada isinya
                $finalUpdates[$id] = $waktuPengaduan ? $waktuPengaduan : $waktuFormulir;
            }
        }

        return response()->json([
            'status' => 'sukses',
            'data'   => $finalUpdates
        ]);
    }
    // KHUSUS SUPERADMIN: Mengambil statistik laporan terbanyak
    public function getStatistik()
    {
        // Menghitung jumlah pengaduan per bidang
        $pengaduan = \App\Models\PengaduanMasyarakat::select('bidang', \DB::raw('count(*) as total'))
            ->groupBy('bidang')
            ->orderByDesc('total')
            ->get();

        // Menghitung jumlah formulir per sub_bidang
        $formulir = \App\Models\FormulirIdentifikasi::select('bidang', 'sub_bidang', \DB::raw('count(*) as total'))
            ->groupBy('bidang', 'sub_bidang')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => [
                'pengaduan' => $pengaduan,
                'formulir'  => $formulir
            ]
        ]);
    }
    // KHUSUS SUPERADMIN: Menghapus Pengaduan
    public function destroyForAdmin($id)
    {
        $pengaduan = \App\Models\PengaduanMasyarakat::findOrFail($id);

        // Proteksi di backend: pastikan statusnya selesai
        if ($pengaduan->status !== 'selesai') {
            return response()->json([
                'status' => 'gagal',
                'pesan'  => 'Pengaduan belum selesai, tidak dapat dihapus.'
            ], 403);
        }

        $pengaduan->delete();

        return response()->json([
            'status' => 'sukses',
            'pesan'  => 'Data pengaduan berhasil dihapus.'
        ]);
    }
}
