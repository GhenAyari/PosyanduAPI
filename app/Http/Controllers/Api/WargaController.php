<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WargaKeluarga;
use App\Models\WargaAnak;
use App\Models\WargaRemaja;
use App\Models\WargaDewasa; // PENTING UNTUK IBU & LANSIA
use App\Models\Posyandu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WargaController extends Controller
{
    private function getPosyanduId()
    {
        $user = auth()->user();
        if (in_array($user->role, ['ketua', 'kader'])) {
            return $user->posyandu_id;
        }
        return null;
    }

    public function store(Request $request)
    {
        // 1. VALIDASI DIPERBARUI: Tangkap status pernikahan dan istri
        $request->validate([
            'nama_lengkap'      => 'required|string',
            'nik'               => 'required|string|size:16|unique:warga_keluarga,nik_kepala_keluarga',
            'no_kk'             => 'required|string|size:16',
            'no_hp'             => 'nullable|string',
            'status_pernikahan' => 'required|in:Menikah,Duda',
            'nama_istri'        => 'required_if:status_pernikahan,Menikah|string|nullable',
            'anak'              => 'nullable|array',
        ]);

        $posyanduId = $this->getPosyanduId();

        if (!$posyanduId && auth()->user()->role !== 'superadmin') {
            return response()->json(['status' => 'gagal', 'pesan' => 'Akun Anda tidak terikat pada Posyandu manapun.'], 403);
        }

        DB::beginTransaction();

        try {
            // 2. Buat Akun Login untuk Warga
            $user = User::create([
                'name'        => $request->nama_lengkap,
                'username'    => $request->nik,
                'password'    => $request->nik,
                'role'        => 'warga',
                'posyandu_id' => $posyanduId
            ]);

            // 3. Buat Data Kepala Keluarga
            $keluarga = WargaKeluarga::create([
                'posyandu_id'          => $posyanduId,
                'user_id'              => $user->id,
                'nama_kepala_keluarga' => $request->nama_lengkap,
                'no_kk'                => $request->no_kk,
                'nik_kepala_keluarga'  => $request->nik,
                'no_hp'                => $request->no_hp,
            ]);

            // 4. OTOMATIS: Masukkan Suami ke Daftar Dewasa (Untuk Lansia)
            WargaDewasa::create([
                'nama_lengkap'  => $request->nama_lengkap,
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => date('Y-m-d', strtotime('-30 years')), // Perkiraan Umur 30
                'keluarga_id'   => $keluarga->id,
            ]);

            // 5. OTOMATIS: Jika Menikah, Masukkan Istri ke Daftar Dewasa (Untuk Hamil & Lansia)
            if ($request->status_pernikahan === 'Menikah' && !empty($request->nama_istri)) {
                WargaDewasa::create([
                    'nama_lengkap'  => $request->nama_istri,
                    'jenis_kelamin' => 'P',
                    'tanggal_lahir' => date('Y-m-d', strtotime('-25 years')), // Perkiraan Umur 25
                    'keluarga_id'   => $keluarga->id,
                ]);
            }

            // 6. Masukkan Anak (Jika ada)
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
        $query = WargaKeluarga::withCount('anak')->latest();
        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->where('posyandu_id', $posyanduId);
        }
        return response()->json(['status' => 'sukses', 'data' => $query->get()]);
    }

    public function resetPassword($id)
    {
        $keluarga = WargaKeluarga::find($id);
        if (!$keluarga) return response()->json(['status' => 'gagal', 'pesan' => 'Data keluarga tidak ditemukan.'], 404);

        $posyanduId = $this->getPosyanduId();
        if ($posyanduId && $keluarga->posyandu_id != $posyanduId) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Akses ditolak.'], 403);
        }

        if (!$keluarga->user_id) return response()->json(['status' => 'gagal', 'pesan' => 'Belum ada akun login.'], 400);

        $user = User::find($keluarga->user_id);
        if ($user) {
            $user->password = Hash::make($keluarga->nik_kepala_keluarga);
            $user->save();
            return response()->json(['status' => 'sukses', 'pesan' => 'Password kembali ke NIK asli.']);
        }
        return response()->json(['status' => 'gagal', 'pesan' => 'Akun tidak ditemukan.'], 404);
    }

    public function getListAnak()
    {
        $table = (new WargaAnak)->getTable();
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
            ->where("$table.jenis_kelamin", 'P'); // HANYA TARIK PEREMPUAN
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
        $query = WargaDewasa::select("$table.id", "$table.nama_lengkap", "$table.jenis_kelamin"); // TARIK LAKI & PEREMPUAN
        $posyanduId = $this->getPosyanduId();
        if ($posyanduId) {
            $query->join($keluargaTable, "$table.keluarga_id", '=', "$keluargaTable.id")
                ->where("$keluargaTable.posyandu_id", $posyanduId);
        }
        return response()->json(['status' => 'sukses', 'data' => $query->distinct()->get()]);
    }
}
