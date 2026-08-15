<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained()->cascadeOnDelete();

            // 1. Identitas & Waktu
            $table->string('kd_kec')->nullable();
            $table->string('kd_desa')->nullable();
            $table->string('rt')->nullable();
            $table->string('no_posyandu')->nullable();
            $table->string('bulan_pendataan')->nullable();
            $table->integer('jumlah')->default(0);

            // 2. Ibu Hamil & KB
            $table->integer('ibu_hamil_periksa')->default(0);
            $table->integer('ibu_hamil_fe')->default(0);
            $table->integer('ibu_menyusui')->default(0);
            $table->integer('kb_kondom')->default(0);
            $table->integer('kb_pil')->default(0);
            $table->integer('kb_suntik')->default(0);

            // 3. Penimbangan Balita (SKDN)
            $table->integer('skdn_s')->default(0);
            $table->integer('skdn_k')->default(0);
            $table->integer('skdn_d')->default(0);
            $table->integer('skdn_n')->default(0);
            $table->integer('skdn_bgm')->default(0);

            // 4. Rincian Balita
            $table->integer('bgm_l')->default(0);
            $table->integer('bgm_p')->default(0);
            $table->integer('vit_a')->default(0);
            $table->integer('kms_keluar')->default(0);
            $table->integer('fe_1')->default(0);
            $table->integer('fe_2')->default(0);
            $table->integer('pmt')->default(0);

            // 5. Imunisasi
            $table->integer('hep_0_7')->default(0);
            $table->integer('bcg')->default(0);
            $table->integer('dpt_1')->default(0);
            $table->integer('dpt_2')->default(0);
            $table->integer('dpt_3')->default(0);
            $table->integer('polio_1')->default(0);
            $table->integer('polio_2')->default(0);
            $table->integer('polio_3')->default(0);
            $table->integer('polio_4')->default(0);
            $table->integer('campak')->default(0);
            $table->integer('hep_1')->default(0);
            $table->integer('hep_2')->default(0);
            $table->integer('hep_3')->default(0);
            $table->integer('tt_1')->default(0);
            $table->integer('tt_2')->default(0);

            // 6. Diare & Layanan Lain
            $table->integer('diare_jml')->default(0);
            $table->integer('diare_oralit')->default(0);
            $table->integer('sosialisasi')->default(0);
            $table->integer('bayi_kms')->default(0);
            $table->integer('balita_imunisasi')->default(0);
            $table->integer('balita_kurang_gizi')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_kegiatans');
    }
};
