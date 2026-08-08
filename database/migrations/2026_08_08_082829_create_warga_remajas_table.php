<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga_remaja', function (Blueprint $table) {
            $table->id();
            // Disambungkan ke KK (Keluarga)
            $table->foreignId('keluarga_id')->nullable()->constrained('warga_keluarga')->cascadeOnDelete();
            $table->string('nama_remaja');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga_remaja');
    }
};
