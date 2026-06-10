<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_ekskuls', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel induk ekskuls
            $table->foreignId('ekskul_id')->constrained('ekskuls')->cascadeOnDelete();

            $table->date('tanggal_kegiatan');
            $table->string('materi'); // Materi latihan
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_ekskuls');
    }
};
