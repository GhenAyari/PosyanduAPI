<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WargaKeluarga;
use App\Models\WargaAnak;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WargaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi data dari React
        $request->validate([
            'nama_lengkap' => 'required|string',
            'nik'          => 'required|string|size:16|unique:warga_keluarga,nik_kepala_keluarga',
            'no_kk'        => 'required|string|size:16',
            'no_hp'        => 'nullable|string',
            'anak'         => 'nullable|array', // Data anak berbentuk array dari React
        ]);

        // Mulai Transaksi (Jika di tengah jalan error, semua data batal disimpan)
        DB::beginTransaction();

        try {
            // 2. Buat Akun Login untuk Warga (di tabel users)
            $user = User::create([
                'name'     => $request->nama_lengkap,
                'username' => $request->nik, // NIK jadi username
                'password' => Hash::make($request->nik), // NIK jadi password awal
                'role'     => 'warga',
                'posyandu' => $request->user()->posyandu // Ambil nama posyandu dari Kader yang sedang login
            ]);

            // 3. Buat Data Kepala Keluarga
            $keluarga = WargaKeluarga::create([
                'posyandu_id'          => 1, // SEMENTARA: hardcode 1, nanti sesuaikan dengan ID posyandu kader
                'user_id'              => $user->id, // Sambungkan ke akun yang baru dibuat
                'nama_kepala_keluarga' => $request->nama_lengkap,
                'no_kk'                => $request->no_kk,
                'nik_kepala_keluarga'  => $request->nik,
                'no_hp'                => $request->no_hp,
            ]);

            // 4. Looping & Simpan Data Anak (Jika ada)
            if ($request->has('anak') && count($request->anak) > 0) {
                foreach ($request->anak as $dataAnak) {
                    WargaAnak::create([
                        'keluarga_id'   => $keluarga->id,
                        'nama_anak'     => $dataAnak['nama'],
                        'tanggal_lahir' => $dataAnak['tanggal_lahir'],
                        'jenis_kelamin' => $dataAnak['jenis_kelamin'] ?? 'L', // Default L jika frontend tidak mengirim
                    ]);
                }
            }

            // Sahkan semua proses!
            DB::commit();

            return response()->json([
                'status' => 'sukses',
                'pesan'  => 'Akun keluarga berhasil dibuat.',
                'data'   => $keluarga->load('anak')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Batal simpan semua jika terjadi error
            return response()->json([
                'status' => 'gagal',
                'pesan'  => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        // Mengambil semua data keluarga beserta jumlah anaknya dari database
        // latest() agar data yang baru ditambahkan muncul paling atas
        $warga = WargaKeluarga::withCount('anak')->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $warga
        ]);
    }
    public function resetPassword($id)
    {
        // 1. Cari data keluarga berdasarkan ID
        $keluarga = WargaKeluarga::find($id);

        if (!$keluarga) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Data keluarga tidak ditemukan.'], 404);
        }

        // 2. Pastikan warga tersebut sudah dibuatkan akun login
        if (!$keluarga->user_id) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Keluarga ini belum memiliki akun login.'], 400);
        }

        // 3. Cari akun User-nya dan reset password menjadi NIK kepala keluarga
        $user = User::find($keluarga->user_id);
        if ($user) {
            $user->password = Hash::make($keluarga->nik_kepala_keluarga);
            $user->save();

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Password berhasil dikembalikan ke NIK asli.'
            ]);
        }

        return response()->json(['status' => 'gagal', 'pesan' => 'Akun pengguna tidak ditemukan.'], 404);
    }
}
