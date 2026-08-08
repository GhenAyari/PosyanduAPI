<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WargaKeluarga;
use App\Models\WargaAnak;
use App\Models\WargaRemaja;
use App\Models\WargaDewasa;
use App\Models\Posyandu; // Panggil model Posyandu
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WargaController extends Controller
{
    /**
     * Helper untuk mendapatkan ID Posyandu dari User yang sedang login.
     * Jika Superadmin/Puskesmas, kembalikan null (agar bisa lihat semua).
     */
    private function getPosyanduId()
    {
        $user = auth()->user();

        // Jika role nya ketua atau kader, langsung kembalikan ID-nya
        if (in_array($user->role, ['ketua', 'kader'])) {
            return $user->posyandu_id; // <-- JAUH LEBIH SIMPEL & CEPAT
        }

        return null;
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'nik'          => 'required|string|size:16|unique:warga_keluarga,nik_kepala_keluarga',
            'no_kk'        => 'required|string|size:16',
            'no_hp'        => 'nullable|string',
            'anak'         => 'nullable|array',
        ]);

        // Ambil ID Posyandu dari user yang login
        $posyanduId = $this->getPosyanduId();

        // Jika null (misal kader belum di-set posyandunya), batalkan
        if (!$posyanduId && auth()->user()->role !== 'superadmin') {
            return response()->json(['status' => 'gagal', 'pesan' => 'Akun Anda tidak terikat pada Posyandu manapun.'], 403);
        }

        DB::beginTransaction();

        try {
            // 2. Buat Akun Login untuk Warga
            $user = User::create([
                'name'     => $request->nama_lengkap,
                'username' => $request->nik,
                'password' => $request->nik, // Biarkan casts yang meng-hash
                'role'     => 'warga',
                'posyandu_id' => $posyanduId // <-- UBAH JADI posyandu_id
            ]);
            // 3. Buat Data Kepala Keluarga (Sudah otomatis terisi posyandu_id)
            $keluarga = WargaKeluarga::create([
                'posyandu_id'          => $posyanduId, // <-- TIDAK HARDCODE LAGI, OTOMATIS DARI USER LOGIN
                'user_id'              => $user->id,
                'nama_kepala_keluarga' => $request->nama_lengkap,
                'no_kk'                => $request->no_kk,
                'nik_kepala_keluarga'  => $request->nik,
                'no_hp'                => $request->no_hp,
            ]);

            if ($request->has('anak') && count($request->anak) > 0) {
                foreach ($request->anak as $dataAnak) {
                    WargaAnak::create([
                        'keluarga_id'   => $keluarga->id,
                        'nama_anak'     => $dataAnak['nama'],
                        'tanggal_lahir' => $dataAnak['tanggal_lahir'],
                        'jenis_kelamin' => $dataAnak['jenis_kelamin'] ?? 'L',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'sukses',
                'pesan'  => 'Akun keluarga berhasil dibuat.',
                'data'   => $keluarga->load('anak')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'gagal',
                'pesan'  => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        // Mulai query
        $query = WargaKeluarga::withCount('anak')->latest();

        // PANGGIL FILTER MULTI-TENANCY
        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->where('posyandu_id', $posyanduId); // Hanya tampilkan warga di posyandu dia
        }

        $warga = $query->get();

        return response()->json([
            'status' => 'sukses',
            'data'   => $warga
        ]);
    }

    public function resetPassword($id)
    {
        $keluarga = WargaKeluarga::find($id);

        if (!$keluarga) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Data keluarga tidak ditemukan.'], 404);
        }

        // TAMBAHAN KEAMANAN: Pastikan Kader tidak reset password warga dari posyandu lain
        $posyanduId = $this->getPosyanduId();
        if ($posyanduId && $keluarga->posyandu_id != $posyanduId) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Akses ditolak. Warga ini bukan dari Posyandu Anda.'], 403);
        }

        if (!$keluarga->user_id) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Keluarga ini belum memiliki akun login.'], 400);
        }

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

    public function getListAnak()
    {
        $table = (new WargaAnak)->getTable(); // Deteksi nama tabel otomatis
        $keluargaTable = (new WargaKeluarga)->getTable();

        $query = WargaAnak::select("$table.id", "$table.nama_anak", "$table.tanggal_lahir", "$table.jenis_kelamin");

        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->join($keluargaTable, "$table.keluarga_id", '=', "$keluargaTable.id")
                ->where("$keluargaTable.posyandu_id", $posyanduId);
        }

        return response()->json(['status' => 'sukses', 'data' => $query->distinct()->get()]);
    }

    public function getListRemaja()
    {
        $table = (new WargaRemaja)->getTable();
        $keluargaTable = (new WargaKeluarga)->getTable();

        $query = WargaRemaja::select("$table.id", "$table.nama_remaja", "$table.tanggal_lahir", "$table.jenis_kelamin");

        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->join($keluargaTable, "$table.keluarga_id", '=', "$keluargaTable.id")
                ->where("$keluargaTable.posyandu_id", $posyanduId);
        }

        return response()->json(['status' => 'sukses', 'data' => $query->distinct()->get()]);
    }

    public function getListIbu()
    {
        $table = (new WargaDewasa)->getTable();
        $keluargaTable = (new WargaKeluarga)->getTable();

        $query = WargaDewasa::select("$table.id", "$table.nama_lengkap", "$table.tanggal_lahir")
            ->where("$table.jenis_kelamin", 'P');

        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->join($keluargaTable, "$table.keluarga_id", '=', "$keluargaTable.id")
                ->where("$keluargaTable.posyandu_id", $posyanduId);
        }

        return response()->json(['status' => 'sukses', 'data' => $query->distinct()->get()]);
    }

    public function getListLansia()
    {
        $table = (new WargaDewasa)->getTable();
        $keluargaTable = (new WargaKeluarga)->getTable();

        $query = WargaDewasa::select("$table.id", "$table.nama_lengkap", "$table.jenis_kelamin");

        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->join($keluargaTable, "$table.keluarga_id", '=', "$keluargaTable.id")
                ->where("$keluargaTable.posyandu_id", $posyanduId);
        }

        return response()->json(['status' => 'sukses', 'data' => $query->distinct()->get()]);
    }
}
