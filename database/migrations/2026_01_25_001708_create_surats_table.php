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
        Schema::create('surats', function (Blueprint $table) {
            $table->id();

            // 1. Tambahkan Sekolah ID (Wajib untuk filter data per sekolah)
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');

            // Relasi ke Belanja
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade');

            // 2. Tambahkan Triwulan (1, 2, 3, atau 4)
            $table->tinyInteger('triwulan');

            $table->string('jenis_surat'); // PH, NH, SP, BAPB
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->boolean('is_parsial')->default(false); // Penanda Parsial
            $table->string('keterangan')->nullable();      // Contoh: "Tahap 1", "Pengiriman Awal"
            $table->string('no_bast')->nullable();
            $table->date('tanggal_bast')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
