<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // ... (kode atasnya biarkan sama)

        // 1. Kumpulkan ID Kader
        $kaderIds = DB::table('users')->where('posyandu_id', $posyanduId)->pluck('id');
        if ($kaderIds->isEmpty()) $kaderIds = [0];

        // --- TAMBAHAN BARU: Ambil ID Keluarga di Posyandu ini saja ---
        $keluargaIds = DB::table('warga_keluarga')->where('posyandu_id', $posyanduId)->pluck('id');
        if ($keluargaIds->isEmpty()) $keluargaIds = [0];

        // 2. Hitung Pemeriksaan Kesehatan Bulan Ini
        $periksaBalita = DB::table('pemeriksaan_balita')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaRemaja = DB::table('pemeriksaan_remaja')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaHamil  = DB::table('pemeriksaan_hamil')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaLansia = DB::table('pemeriksaan_lansia')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();

        // 3. PERBAIKAN: Total Target per Kelompok untuk Grafik (Boleh overlap)
        $totalBalita = DB::table('warga_anak')->whereIn('keluarga_id', $keluargaIds)->count();
        $totalRemaja = DB::table('warga_remaja')->whereIn('keluarga_id', $keluargaIds)->count();
        $totalHamil  = DB::table('warga_dewasa')->whereIn('keluarga_id', $keluargaIds)->where('jenis_kelamin', 'P')->count(); // Istri masuk sini
        $totalLansia = DB::table('warga_dewasa')->whereIn('keluarga_id', $keluargaIds)->count(); // Suami & Istri masuk sini

        // 4. TOTAL INDIVIDU UNIK UNTUK KOTAK BIRU (Menghindari Istri Dihitung 2x)
        $totalDewasaUnik = DB::table('warga_dewasa')->whereIn('keluarga_id', $keluargaIds)->count();
        $totalWarga = $totalBalita + $totalRemaja + $totalDewasaUnik;

        $totalPemeriksaan = $periksaBalita + $periksaRemaja + $periksaHamil + $periksaLansia;

        // Hitung persentase kehadiran
        $persentaseHadir = $totalWarga > 0 ? round(($totalPemeriksaan / $totalWarga) * 100) : 0;
        if ($persentaseHadir > 100) $persentaseHadir = 100;

        // 3. Hitung Pengaduan & Formulir 5 Bidang
        $pengaduanBaru = DB::table('pengaduan_masyarakat')
            ->where('posyandu_id', $posyanduId)
            ->where('status', 'menunggu')
            ->count();

        $bidangList = ['pendidikan', 'pekerjaan_umum', 'perumahan_rakyat', 'trantibumlinmas', 'sosial'];
        $lingkungan = [];
        foreach ($bidangList as $b) {
            $lingkungan[$b] = [
                'aduan' => DB::table('pengaduan_masyarakat')->where('posyandu_id', $posyanduId)->where('bidang', $b)->count(),
                'form'  => DB::table('formulir_identifikasi')->where('posyandu_id', $posyanduId)->where('bidang', $b)->count(),
            ];
        }

        // 4. Cek Status Rekap Register Bulanan (46 Kolom)
        $rekapBulanIni = DB::table('rekap_kegiatans')
            ->where('posyandu_id', $posyanduId)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->exists();

        // 5. AMBIL AKTIVITAS TERBARU (Sistem Pemantauan Terpadu)
        $aktivitas = collect();

        // Memantau Pemeriksaan Kesehatan
        $latestBalita = DB::table('pemeriksaan_balita')->whereIn('kader_id', $kaderIds)->latest('created_at')->first();
        if ($latestBalita) $aktivitas->push(['judul' => 'Data pemeriksaan kesehatan disimpan', 'waktu' => $latestBalita->created_at, 'warna' => '#0ea5e9']);

        // Memantau Pengaduan Masuk
        $latestPengaduan = DB::table('pengaduan_masyarakat')->where('posyandu_id', $posyanduId)->latest('created_at')->first();
        if ($latestPengaduan) $aktivitas->push(['judul' => 'Pengaduan masyarakat baru masuk', 'waktu' => $latestPengaduan->created_at, 'warna' => '#db2777']);

        // Memantau Formulir Baru
        $latestForm = DB::table('formulir_identifikasi')->where('posyandu_id', $posyanduId)->latest('created_at')->first();
        if ($latestForm) $aktivitas->push(['judul' => 'Formulir identifikasi desa ditambahkan', 'waktu' => $latestForm->created_at, 'warna' => '#f59e0b']);

        // --- TAMBAHAN BARU: Memantau 3 Laporan yang baru kita buat hari ini ---

        // Memantau Rekap 46 Kolom
        $latestRekap = DB::table('rekap_kegiatans')->where('posyandu_id', $posyanduId)->latest('created_at')->first();
        if ($latestRekap) $aktivitas->push(['judul' => 'Rekapitulasi Register Bulanan dibuat', 'waktu' => $latestRekap->created_at, 'warna' => '#8b5cf6']); // Warna Ungu

        // Memantau Pencatatan 13 Poin
        $latestPencatatan = DB::table('pencatatan_kegiatans')->where('posyandu_id', $posyanduId)->latest('created_at')->first();
        if ($latestPencatatan) $aktivitas->push(['judul' => 'Laporan Kegiatan 13 Poin disimpan', 'waktu' => $latestPencatatan->created_at, 'warna' => '#d946ef']); // Warna Pink

        // Memantau Data Umum Posyandu
        $latestDataUmum = DB::table('data_umums')->where('posyandu_id', $posyanduId)->latest('created_at')->first();
        if ($latestDataUmum) $aktivitas->push(['judul' => 'Data Umum Posyandu diperbarui', 'waktu' => $latestDataUmum->created_at, 'warna' => '#14b8a6']); // Warna Tosca

        // Urutkan semua aktivitas dari yang paling detik ini baru disimpan, lalu ambil 3 saja untuk ditampilkan
        $aktivitas = $aktivitas->sortByDesc('waktu')->take(3)->values();

        // ==========================================
        // KIRIM KE REACT
        // ==========================================
        return response()->json([
            'status' => 'sukses',
            'data' => [
                'top_stats' => [
                    'total_warga' => $totalWarga,
                    'kehadiran_persen' => $persentaseHadir,
                    'pengaduan_baru' => $pengaduanBaru,
                    'status_register' => $rekapBulanIni ? 'Selesai' : 'Kosong',
                ],
                'kesehatan' => [
                    'balita' => ['diperiksa' => $periksaBalita, 'total' => $totalBalita],
                    'remaja' => ['diperiksa' => $periksaRemaja, 'total' => $totalRemaja],
                    'hamil'  => ['diperiksa' => $periksaHamil, 'total' => $totalHamil],
                    'lansia' => ['diperiksa' => $periksaLansia, 'total' => $totalLansia],
                ],
                'lingkungan' => $lingkungan,
                'rekap_bulan_ini' => $rekapBulanIni,
                'aktivitas_terbaru' => $aktivitas
            ]
        ], 200);
    }
}
