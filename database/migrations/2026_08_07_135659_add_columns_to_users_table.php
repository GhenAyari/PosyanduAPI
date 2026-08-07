<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek apakah kolom username belum ada, jika belum, baru buat
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('name');
            }

            // Cek apakah kolom role belum ada
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('warga')->after('username');
            }

            // Cek apakah kolom posyandu belum ada
            if (!Schema::hasColumn('users', 'posyandu')) {
                $table->string('posyandu')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cara membatalkannya jika terjadi error
            $table->dropColumn(['username', 'role', 'posyandu']);
        });
    }
};
