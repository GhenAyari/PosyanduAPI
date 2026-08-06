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
        Schema::create('formulir_identifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandus')->cascadeOnDelete();
            $table->foreignId('kader_id')->constrained('users')->cascadeOnDelete();
            $table->enum('bidang', ['pendidikan', 'pekerjaan_umum', 'perumahan_rakyat', 'trantibumlinmas', 'sosial']);
            $table->string('sub_bidang');
            $table->json('data_formulir'); // Disimpan dalam format JSON agar fleksibel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulir_identifikasi');
    }
};
