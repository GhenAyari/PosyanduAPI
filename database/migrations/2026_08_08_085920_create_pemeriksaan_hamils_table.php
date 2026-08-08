<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_hamil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ibu_id')->constrained('warga_dewasa')->cascadeOnDelete();
            $table->foreignId('kader_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_periksa');

            // Sesuai form React Ibu Hamil
            $table->integer('usia_kehamilan_minggu');
            $table->decimal('berat_badan', 5, 2);
            $table->decimal('tinggi_badan', 5, 2);
            $table->string('tekanan_darah')->nullable();
            $table->decimal('lingkar_perut', 5, 2)->nullable();
            $table->decimal('lingkar_lengan', 5, 2)->nullable();

            // Dropdown Ya/Tidak
            $table->enum('status_kek', ['Ya', 'Tidak'])->default('Tidak');
            $table->enum('anemia', ['Ya', 'Tidak'])->default('Tidak');

            $table->string('status_imt')->nullable();
            $table->json('dokumentasi_foto')->nullable();
            $table->enum('status_form', ['draft', 'final'])->default('final');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_hamil');
    }
};
