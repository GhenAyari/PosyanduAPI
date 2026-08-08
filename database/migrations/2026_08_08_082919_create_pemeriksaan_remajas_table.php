<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_remaja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remaja_id')->constrained('warga_remaja')->cascadeOnDelete();
            $table->foreignId('kader_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_periksa');

            // Kolom sesuai dengan form React-mu
            $table->integer('umur_tahun');
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 2);
            $table->string('tekanan_darah')->nullable(); // Disimpan sebagai string, misal: "110/70"
            $table->string('status_imt')->nullable(); // Hasil kalkulator otomatis

            $table->json('dokumentasi_foto')->nullable();
            $table->enum('status_form', ['draft', 'final'])->default('final');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_remaja');
    }
};
