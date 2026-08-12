<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan_masyarakat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandus')->cascadeOnDelete();
            $table->enum('bidang', ['pendidikan', 'pekerjaan_umum', 'perumahan_rakyat', 'trantibumlinmas', 'sosial']);

            // === KOLOM DATA PELAPOR (LAMA) ===
            $table->string('nama_pelapor');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('nik', 16)->nullable(); // Dibuat nullable agar form aspirasi lebih fleksibel
            $table->string('no_hp')->nullable();
            $table->text('alamat');

            // === KOLOM ASPIRASI & KELUHAN (BARU & LAMA) ===
            $table->date('tanggal_penyampaian')->nullable(); // Baru
            $table->string('penerima_aspirasi')->nullable(); // Baru
            $table->string('jenis_aspirasi')->nullable();    // Baru (bisa untuk Jenis Pengaduan)
            $table->text('isi_keluhan');                     // Lama (sebagai Uraian Aspirasi)
            $table->string('lokasi_masalah')->nullable();    // Lama
            $table->string('urgensi')->nullable();           // Baru (Tinggi, Sedang, Rendah)
            $table->text('rekomendasi')->nullable();         // Baru
            $table->text('tindak_lanjut')->nullable();       // Baru (Untuk evaluasi nanti)

            // === STATUS & LAMPIRAN ===
            $table->enum('status', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
            $table->json('lampiran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_masyarakat');
    }
};
