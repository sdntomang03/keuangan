<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_ekskul_fotos', function (Blueprint $table) {
            $table->id();
            // Menghubungkan foto ke baris laporan pertemuan spesifik
            $table->foreignId('laporan_ekskul_id')->constrained('laporan_ekskuls')->cascadeOnDelete();
            $table->string('path_foto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_ekskul_fotos');
    }
};
