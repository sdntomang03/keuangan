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
        Schema::create('penomoran_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->integer('tahun');
            $table->integer('triwulan');
            $table->integer('nomor_awal'); // Input murni dari user
            $table->timestamps();

            // Pastikan tidak ada duplikasi data untuk TW yang sama di tahun yang sama
            $table->unique(['sekolah_id', 'tahun', 'triwulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penomoran_surats');
    }
};
