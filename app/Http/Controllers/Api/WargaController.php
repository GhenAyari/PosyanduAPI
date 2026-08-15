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
    // =========================================================================
    // FUNGSI KHUSUS WARGA: MENGAMBIL RAPOR KESEHATAN KELUARGA (REAL-TIME)
    // =========================================================================
    public function getRaporKeluarga(Request $request)
    {
        $user = $request->user();

        // Cari data keluarga berdasarkan akun Warga yang sedang login
        $keluarga = WargaKeluarga::where('user_id', $user->id)->first();

        if (!$keluarga) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Data keluarga tidak ditemukan.'], 404);
        }

        // 1. Tarik Data Anak beserta Riwayat Pemeriksaan Balita
        $anakList = WargaAnak::where('keluarga_id', $keluarga->id)->get()->map(function($anak) {
            $riwayat = DB::table('pemeriksaan_balita')
                ->where('anak_id', $anak->id)
                ->orderBy('tanggal_periksa', 'desc')
                ->get()
                ->map(function($periksa) {
                    return [
                        'bulan'  => date('M Y', strtotime($periksa->tanggal_periksa)),
                        'bb'     => $periksa->berat_badan . ' kg',
                        'tb'     => $periksa->tinggi_badan . ' cm',
                        'status' => $periksa->status_gizi ?: 'Normal'
                    ];
                });

            // Hitung usia dalam tahun (jika 0, tampilkan "Di bawah 1")
            $umur = date_diff(date_create($anak->tanggal_lahir), date_create('today'))->y;

            return [
                'nama'    => $anak->nama_anak,
                'gender'  => $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                'usia'    => $umur > 0 ? $umur . ' Tahun' : 'Di bawah 1 Tahun',
                'riwayat' => $riwayat
            ];
        });

        // 2. Tarik Data Lansia atau Ibu Hamil dari tabel Dewasa
        $dewasaList = WargaDewasa::where('keluarga_id', $keluarga->id)->get();
        $lansiaBumil = null;

        foreach ($dewasaList as $dewasa) {
            // Cek Riwayat Ibu Hamil (Hanya untuk Perempuan)
            if ($dewasa->jenis_kelamin == 'P') {
                $riwayatHamil = DB::table('pemeriksaan_hamil')->where('ibu_id', $dewasa->id)->orderBy('tanggal_periksa', 'desc')->get();
                if ($riwayatHamil->count() > 0) {
                    $lansiaBumil = [
                        'nama'    => $dewasa->nama_lengkap,
                        'jenis'   => 'bumil',
                        'riwayat' => $riwayatHamil->map(function($r) {
                            return [
                                'bulan'  => date('M Y', strtotime($r->tanggal_periksa)),
                                'ukuran' => $r->lingkar_lengan . ' cm', // LILA
                                'tensi'  => $r->tekanan_darah ?: '-',
                                'status' => $r->status_imt ?: 'Normal'
                            ];
                        })
                    ];
                    break; // Jika ketemu hamil, jadikan prioritas
                }
            }

            // Cek Riwayat Lansia
            $riwayatLansia = DB::table('pemeriksaan_lansia')->where('lansia_id', $dewasa->id)->orderBy('tanggal_periksa', 'desc')->get();
            if ($riwayatLansia->count() > 0) {
                $lansiaBumil = [
                    'nama'    => $dewasa->nama_lengkap,
                    'jenis'   => 'lansia',
                    'riwayat' => $riwayatLansia->map(function($r) {
                        return [
                            'bulan'  => date('M Y', strtotime($r->tanggal_periksa)),
                            'ukuran' => $r->berat_badan . ' kg', // BB
                            'tensi'  => $r->tekanan_darah ?: '-',
                            'status' => $r->status_imt ?: 'Normal'
                        ];
                    })
                ];
                break;
            }
        }

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'anak' => $anakList,
                'anggotaLansiaBumil' => $lansiaBumil
            ]
        ], 200);
    }
}
