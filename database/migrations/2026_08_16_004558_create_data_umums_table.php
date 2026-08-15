<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_umums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained()->cascadeOnDelete();

            // Header
            $table->string('nama_posyandu')->nullable();
            $table->string('rukun_warga')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();

            // Poin 1 & 2
            $table->string('tahun')->nullable();
            $table->string('bulan')->nullable();

            // Poin 3
            $table->integer('pengunjung_bayi')->default(0);
            $table->integer('pengunjung_baduta')->default(0);
            $table->integer('pengunjung_balita')->default(0);
            $table->integer('pengunjung_wus')->default(0);
            $table->integer('pengunjung_pus')->default(0);
            $table->integer('pengunjung_ibu_hamil')->default(0);
            $table->integer('pengunjung_ibu_menyusui')->default(0);

            // Poin 4
            $table->integer('bayi_lahir')->default(0);
            $table->integer('bayi_meninggal')->default(0);

            // Poin 5
            $table->integer('mati_ibu_hamil_salin_nifas')->default(0);

            // Poin 6
            $table->integer('petugas_kader')->default(0);
            $table->integer('petugas_plkb')->default(0);
            $table->integer('petugas_medis')->default(0);

            // Poin 7 & 8
            $table->integer('nifas_fe')->default(0);
            $table->integer('nifas_vit_a')->default(0);
            $table->integer('hamil_kek')->default(0);
            $table->integer('hamil_anemia')->default(0);

            // Poin 9
            $table->integer('pengunjung_l')->default(0);
            $table->integer('pengunjung_p')->default(0);

            // Poin 10, 11, 12
            $table->integer('jml_kk')->default(0);
            $table->integer('jml_ibu_melahirkan')->default(0);
            $table->integer('mati_ibu_hamil')->default(0);
            $table->integer('mati_ibu_melahirkan')->default(0);
            $table->integer('mati_ibu_nifas')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_umums');
    }
};
