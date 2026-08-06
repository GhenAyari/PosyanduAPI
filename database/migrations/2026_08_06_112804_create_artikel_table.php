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
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->nullable()->constrained('posyandus')->cascadeOnDelete();
            $table->foreignId('penulis_id')->constrained('users')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->longText('isi_artikel');
            $table->string('path_foto')->nullable();
            $table->enum('status', ['draf', 'dipublikasikan'])->default('draf');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
