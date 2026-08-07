<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mengubah tipe kolom role menjadi string (VARCHAR) agar fleksibel
            $table->string('role')->default('warga')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Opsional: kosongkan down() jika tidak ingin di-rollback
        });
    }
};
