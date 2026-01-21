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
        Schema::create('akb_rincis', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel AKB Master
            $table->foreignId('akb_id')->constrained('akbs')->onDelete('cascade');
            // Foreign Key ke tabel financial_records
            $table->string('idblrinci')->index();
            $table->integer('bulan');
            $table->decimal('nominal', 15, 2);
            $table->decimal('volume', 15, 4);
            $table->foreignId('anggaran_id')->constrained('anggarans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akb_rincis');
    }
};
