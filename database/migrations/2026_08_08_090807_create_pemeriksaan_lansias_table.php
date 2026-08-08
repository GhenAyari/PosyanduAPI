<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_lansia', function (Blueprint $table) {
            $table->id();
            // Disambungkan ke tabel master Warga Dewasa
            $table->foreignId('lansia_id')->constrained('warga_dewasa')->cascadeOnDelete();
            $table->foreignId('kader_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_periksa');

            // Kolom Pemeriksaan sesuai gambar
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 2);
            $table->decimal('lingkar_pinggang', 5, 2)->nullable();
            $table->string('tekanan_darah')->nullable(); // misal: 130/85
            $table->enum('tensi', ['Rendah', 'Normal', 'Tinggi'])->default('Normal');
            $table->integer('gula_darah')->nullable();
            $table->integer('nadi')->nullable();

            $table->string('status_imt')->nullable();
            $table->json('dokumentasi_foto')->nullable();
            $table->enum('status_form', ['draft', 'final'])->default('final');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_lansia');
    }
};
