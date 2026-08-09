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
        Schema::create('pengaduan_masyarakat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandus')->cascadeOnDelete();
            $table->enum('bidang', ['pendidikan', 'pekerjaan_umum', 'perumahan_rakyat', 'trantibumlinmas', 'sosial']);
            $table->string('nama_pelapor');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('nik', 16);
            $table->string('no_hp')->nullable();
            $table->text('alamat');
            $table->text('isi_keluhan');
            $table->string('lokasi_masalah')->nullable();
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
