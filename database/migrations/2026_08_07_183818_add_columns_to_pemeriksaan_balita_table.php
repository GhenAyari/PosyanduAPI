<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaan_balita', function (Blueprint $table) {
            $table->json('imunisasi')->nullable()->after('status_gizi');
            $table->json('dokumentasi_foto')->nullable()->after('imunisasi');
            $table->enum('status_form', ['draft', 'final'])->default('final')->after('dokumentasi_foto');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_balita', function (Blueprint $table) {
            $table->dropColumn(['imunisasi', 'dokumentasi_foto', 'status_form']);
        });
    }
};
