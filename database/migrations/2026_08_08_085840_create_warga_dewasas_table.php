<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga_dewasa', function (Blueprint $table) {
            $table->id();
            // nullable() agar bisa auto-daftar tanpa harus punya KK di sistem
            $table->foreignId('keluarga_id')->nullable()->constrained('warga_keluarga')->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga_dewasa');
    }
};
