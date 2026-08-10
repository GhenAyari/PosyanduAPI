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
        Schema::create('posyandus', function (Blueprint $table) {
            $table->id();
            // Data Dasar
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('kontak_darurat')->nullable();

            // Lokasi & Foto
            $table->text('link_gmaps')->nullable(); // Menggantikan latitude & longitude
            $table->string('foto')->nullable();

            // Profil Posyandu
            $table->string('strata')->nullable()->default('Purnama');
            $table->string('program_terintegrasi')->nullable();
            $table->string('pj_umum')->nullable();
            $table->string('pj_operasional')->nullable();
            $table->string('ketua_pelaksana')->nullable();
            $table->string('sekretaris')->nullable();
            $table->string('bendahara')->nullable();
            $table->integer('jml_kader_aktif')->default(0);
            $table->integer('jml_kader_tidak_aktif')->default(0);
            $table->string('bidan_desa')->nullable();
            $table->string('petugas_kb')->nullable();

            // Data Sarana Posyandu
            $table->string('tempat_pelayanan')->nullable()->default('Gedung Sendiri');
            $table->string('timbangan')->nullable()->default('Tersedia');
            $table->string('buku_kia')->nullable()->default('Tersedia');
            $table->string('formulir_sip')->nullable()->default('Tersedia');
            $table->string('blanko_skdn')->nullable()->default('Tersedia');
            $table->string('ape')->nullable()->default('Tersedia');
            $table->string('sarana_lain')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posyandus');
    }
};
