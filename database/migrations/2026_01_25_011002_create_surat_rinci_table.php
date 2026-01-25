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
        Schema::create('surat_rinci', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel Surats
            $table->foreignId('surat_id')->constrained('surats')->onDelete('cascade');

            // Relasi ke tabel Belanja Rincis Anda (Pastikan nama tabelnya benar)
            // Jika nama tabel database Anda 'belanja_rincis', gunakan ini:
            $table->foreignId('belanja_rinci_id')->constrained('belanja_rincis')->onDelete('cascade');
            $table->integer('volume');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_rinci');
    }
};
