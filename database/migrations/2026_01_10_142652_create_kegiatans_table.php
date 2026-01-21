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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('snp')->nullable();           // Standar Nasional Pendidikan
            $table->string('sumber_dana')->nullable();    // BOS / BOP
            $table->string('kodedana')->nullable();      // Kode Dana
            $table->string('namadana')->nullable();      // Nama Dana
            $table->string('kodegiat')->nullable();      // Kode Kegiatan
            $table->string('namagiat')->nullable();      // Nama Kegiatan
            $table->text('kegiatan')->nullable();        // Deskripsi panjang kegiatan
            $table->string('idbl')->unique();
            $table->text('link')->nullable();            // URL atau Link terkait
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
