<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalitikController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // =======================================================
        // 1. TREN KEHADIRAN BULANAN (6 BULAN TERAKHIR)
        // =======================================================
        $tren = [];
        $maxTren = 1; // Mencegah pembagian dengan nol di Frontend
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;
            $namaBulan = $date->translatedFormat('M');

            $cBalita = DB::table('pemeriksaan_balita')->whereMonth('tanggal_periksa', $m)->whereYear('tanggal_periksa', $y)->count();
            $cRemaja = DB::table('pemeriksaan_remaja')->whereMonth('tanggal_periksa', $m)->whereYear('tanggal_periksa', $y)->count();
            $cHamil  = DB::table('pemeriksaan_hamil')->whereMonth('tanggal_periksa', $m)->whereYear('tanggal_periksa', $y)->count();
            $cLansia = DB::table('pemeriksaan_lansia')->whereMonth('tanggal_periksa', $m)->whereYear('tanggal_periksa', $y)->count();

            $total = $cBalita + $cRemaja + $cHamil + $cLansia;
            if ($total > $maxTren) $maxTren = $total;

            $tren[] = [
                'bulan' => $namaBulan,
                'total' => $total
            ];
        }

        // =======================================================
        // 2. CAPAIAN PER BIDANG (Relatif terhadap yang tertinggi)
        // =======================================================
        $bidangList = ['kesehatan', 'pendidikan', 'pekerjaan_umum', 'perumahan_rakyat', 'trantibumlinmas', 'sosial'];
        $capaianRaw = [];
        $maxCapaian = 1;

        foreach ($bidangList as $b) {
            if ($b == 'kesehatan') {
                $tot = DB::table('pemeriksaan_balita')->count() + DB::table('pemeriksaan_remaja')->count() + DB::table('pemeriksaan_hamil')->count() + DB::table('pemeriksaan_lansia')->count();
            } else {
                $tot = DB::table('pengaduan_masyarakat')->where('bidang', $b)->count() + DB::table('formulir_identifikasi')->where('bidang', $b)->count();
            }
            $capaianRaw[$b] = $tot;
            if ($tot > $maxCapaian) $maxCapaian = $tot;
        }

        $capaianPersen = [];
        foreach ($capaianRaw as $b => $tot) {
            // Diubah jadi persen relatif agar bar grafiknya terlihat penuh & proporsional
            $capaianPersen[$b] = round(($tot / $maxCapaian) * 100);
        }

        // =======================================================
        // 3. KEAKTIFAN WARGA PER POSYANDU (Bulan Ini)
        // =======================================================
        $namaPosyandu = [1=>'Melati', 2=>'Rukun Lestari', 3=>'Mawar', 4=>'Bina Putra', 5=>'Nusa Indah', 6=>'Cempaka', 7=>'Tunas Mulya', 8=>'Surya', 9=>'Terkini'];
        $kehadiranPosyandu = [];

        foreach ($namaPosyandu as $id => $nama) {
            // Hitung Total Warga Unik di Posyandu ini
            $kelIds = DB::table('warga_keluarga')->where('posyandu_id', $id)->pluck('id');
            if ($kelIds->isEmpty()) $kelIds = [0];

            $totWarga = DB::table('warga_anak')->whereIn('keluarga_id', $kelIds)->count() +
                DB::table('warga_remaja')->whereIn('keluarga_id', $kelIds)->count() +
                DB::table('warga_dewasa')->whereIn('keluarga_id', $kelIds)->count();

            // Hitung Pemeriksaan Bulan Ini oleh Kader di Posyandu ini
            $kaderIds = DB::table('users')->where('posyandu_id', $id)->pluck('id');
            if ($kaderIds->isEmpty()) $kaderIds = [0];

            $totHadir = DB::table('pemeriksaan_balita')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count() +
                DB::table('pemeriksaan_remaja')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count() +
                DB::table('pemeriksaan_hamil')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count() +
                DB::table('pemeriksaan_lansia')->whereIn('kader_id', $kaderIds)->whereMonth('tanggal_periksa', $bulanIni)->whereYear('tanggal_periksa', $tahunIni)->count();

            $persen = $totWarga > 0 ? round(($totHadir / $totWarga) * 100) : 0;
            if ($persen > 100) $persen = 100;

            $kehadiranPosyandu[] = [
                'nama' => $nama,
                'persen' => $persen
            ];
        }

        // Urutkan Posyandu dari Persentase Kehadiran Tertinggi ke Terendah (Ranking)
        usort($kehadiranPosyandu, function($a, $b) {
            return $b['persen'] <=> $a['persen'];
        });

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'tren' => $tren,
                'max_tren' => $maxTren,
                'capaian' => $capaianPersen,
                'posyandu' => $kehadiranPosyandu
            ]
        ], 200);
    }
}
