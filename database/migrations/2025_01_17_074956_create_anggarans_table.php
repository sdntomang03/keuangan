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
        Schema::create('anggarans', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 4); // Contoh: "2025", "2026"
            $table->string('singkatan'); // Contoh: "Dana BOS Reguler 2025"
            $table->string('nama_anggaran'); // Contoh: "Dana BOS Reguler 2025"
            $table->boolean('is_aktif')->default(false); // Untuk menandai tahun yang sedang berjalan
            $table->integer('sekolah_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggarans');
    }
};
