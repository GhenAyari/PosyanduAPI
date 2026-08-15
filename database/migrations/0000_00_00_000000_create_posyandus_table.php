<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posyandus', function (Blueprint $table) {
            $table->id();
            // Data Dasar
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('kontak_darurat')->nullable();

            // === BARU: Identitas Wilayah (Sesuai Lembar SIP) ===
            $table->string('kd_kecamatan')->nullable();
            $table->string('kd_desa')->nullable();
            $table->string('rukun_tetangga')->nullable();
            $table->string('nomor_posyandu')->nullable();

            // Lokasi & Foto
            $table->text('link_gmaps')->nullable(); // Menggantikan latitude & longitude
            $table->string('foto')->nullable();

            // === Profil Posyandu ===
            $table->string('strata')->nullable()->default('Purnama');
            $table->string('program_paud')->nullable()->default('Tidak'); // Baru (SIP)
            $table->string('program_bkb')->nullable()->default('Tidak'); // Baru (SIP)
            $table->string('program_terintegrasi')->nullable(); // Tetap dipertahankan (untuk program Lainnya)

            $table->string('pj_umum')->nullable();
            $table->string('pj_operasional')->nullable();
            $table->string('ketua_pelaksana')->nullable();
            $table->string('sekretaris')->nullable();
            $table->string('bendahara')->nullable();
            $table->integer('jml_kader_aktif')->default(0);
            $table->integer('jml_kader_tidak_aktif')->default(0);

            $table->string('petugas_kb')->nullable();
            $table->string('medis_paramedis')->nullable(); // Baru (SIP)
            $table->string('bidan_desa')->nullable();
            $table->text('keterangan_profil')->nullable(); // Baru (SIP)

            // === Data Sarana Posyandu ===
            $table->string('tempat_pelayanan')->nullable()->default('Gedung Sendiri');
            $table->string('timbangan')->nullable()->default('Tersedia'); // Tetap dipertahankan

            // Rincian Timbangan (Baru - Sesuai Kolom 8,9,10,11 di SIP)
            $table->integer('jml_dacin')->default(0);
            $table->integer('timbangan_bayi')->default(0);
            $table->integer('timbangan_balita')->default(0);
            $table->integer('timbangan_ibu')->default(0);

            $table->string('buku_kia')->nullable()->default('Tersedia');
            $table->string('formulir_sip')->nullable()->default('Tersedia');
            $table->string('blanko_skdn')->nullable()->default('Tersedia');
            $table->string('buku_catatan_keuangan')->nullable()->default('Tersedia'); // Baru (SIP)

            $table->string('alat_peraga_penyuluhan')->nullable()->default('Tersedia'); // Baru (SIP)
            $table->string('ape')->nullable()->default('Tersedia'); // Alat Permainan Edukasi

            $table->string('sarana_lain')->nullable();
            $table->text('keterangan_sarana')->nullable(); // Baru (SIP)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posyandus');
    }
};
