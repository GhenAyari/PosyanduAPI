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

        // 1. Kumpulkan ID Kader
        $kaderIds = DB::table('users')->where('posyandu_id', $posyanduId)->pluck('id');
        // Pastikan tidak error jika array kosong
        if ($kaderIds->isEmpty()) $kaderIds = [0];

        // 2. Hitung Pemeriksaan Kesehatan Bulan Ini
        $periksaBalita = DB::table('pemeriksaan_balita')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaRemaja = DB::table('pemeriksaan_remaja')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaHamil  = DB::table('pemeriksaan_hamil')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();
        $periksaLansia = DB::table('pemeriksaan_lansia')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();

        // Total Warga Sasaran
        $totalBalita = DB::table('warga_anak')->count() ?: 1;
        $totalRemaja = DB::table('warga_remaja')->count() ?: 1;
        $totalHamil  = DB::table('warga_dewasa')->count() ?: 1;
        $totalLansia = DB::table('warga_dewasa')->count() ?: 1;

        $totalPemeriksaan = $periksaBalita + $periksaRemaja + $periksaHamil + $periksaLansia;
        $totalWarga = $totalBalita + $totalRemaja + $totalHamil + $totalLansia;

        // Hitung persentase kehadiran (mencegah pembagian dengan 0)
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
