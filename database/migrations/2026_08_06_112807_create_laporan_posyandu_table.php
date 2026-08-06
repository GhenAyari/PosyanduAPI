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
        Schema::create('laporan_posyandu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandus')->cascadeOnDelete();
            $table->enum('jenis_laporan', ['bulanan_puskesmas', 'bulanan_desa', 'triwulan']);
            $table->tinyInteger('bulan');
            $table->year('tahun');
            $table->json('data_rekap');
            $table->enum('status', ['draf', 'siap_dilaporkan', 'terkirim'])->default('draf');
            $table->foreignId('dikoreksi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikoreksi_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_posyandu');
    }
};
