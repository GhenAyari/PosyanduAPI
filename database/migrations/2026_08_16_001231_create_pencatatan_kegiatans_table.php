<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencatatan_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained()->cascadeOnDelete();

            $table->string('nama_posyandu')->nullable();
            $table->string('ketua_pelaksana')->nullable();

            // 1. Ibu Hamil & 2. Menyusui
            $table->integer('ibu_hamil')->default(0);
            $table->integer('ibu_hamil_periksa')->default(0);
            $table->integer('ibu_hamil_fe')->default(0);
            $table->integer('ibu_menyusui')->default(0);

            // 3. KB
            $table->integer('kb_kondom')->default(0);
            $table->integer('kb_pil')->default(0);
            $table->integer('kb_suntik')->default(0);

            // 4. SKDN
            $table->integer('skdn_s')->default(0);
            $table->integer('skdn_k')->default(0);
            $table->integer('skdn_d')->default(0);
            $table->integer('skdn_n')->default(0);
            $table->integer('skdn_bgm')->default(0);
            $table->integer('bgm_l')->default(0);
            $table->integer('bgm_p')->default(0);

            // 5. Rincian Balita
            $table->integer('vit_a')->default(0);
            $table->integer('kms_keluar')->default(0);
            $table->integer('fe_1')->default(0);
            $table->integer('fe_2')->default(0);
            $table->integer('pmt')->default(0);

            // 6. Imunisasi
            $table->integer('hep_0_7')->default(0);
            $table->integer('dpt_hb')->default(0);
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

            // 7 - 13. Layanan & Diare
            $table->integer('diare_jml')->default(0);
            $table->integer('diare_oralit')->default(0);
            $table->integer('layanan_kesehatan')->default(0);
            $table->integer('sosialisasi')->default(0);
            $table->integer('bayi_kms')->default(0);
            $table->integer('balita_imunisasi')->default(0);
            $table->integer('balita_kurang_gizi')->default(0);
            $table->integer('kematian_balita')->default(0);

            // Tanda Tangan (Disimpan sebagai teks panjang Base64)
            $table->longText('signature_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencatatan_kegiatans');
    }
};
