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
        Schema::create('referensi_makanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_makanan');
            $table->integer('kalori_per_porsi');
            $table->foreignId('dibuat_oleh_posyandu')->nullable()->constrained('posyandus')->cascadeOnDelete();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referensi_makanan');
    }
};
