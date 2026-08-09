<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suntik kolom lampiran ke tabel pengaduan
        Schema::table('pengaduan_masyarakat', function (Blueprint $table) {
            $table->json('lampiran')->nullable()->after('lokasi_masalah');
        });

        // 2. Suntik kolom dokumentasi_foto ke tabel formulir
        Schema::table('formulir_identifikasi', function (Blueprint $table) {
            $table->json('dokumentasi_foto')->nullable()->after('data_formulir');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduan_masyarakat', function (Blueprint $table) {
            $table->dropColumn('lampiran');
        });

        Schema::table('formulir_identifikasi', function (Blueprint $table) {
            $table->dropColumn('dokumentasi_foto');
        });
    }
};
