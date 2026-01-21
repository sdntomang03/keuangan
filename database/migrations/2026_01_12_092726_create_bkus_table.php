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
        Schema::create('bkus', function (Blueprint $table) {
            $table->id();

            // 1. Identitas Sekolah (Multi-School)
            $table->foreignId('anggaran_id')->constrained('anggarans')->onDelete('cascade');

            $table->integer('no_urut');
            $table->date('tanggal');
            $table->string('no_bukti');
            $table->string('uraian');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);

            // Relasi opsional untuk tracking sumber data
            $table->foreignId('belanja_id')->nullable()->constrained('belanjas')->onDelete('cascade');
            $table->foreignId('pajak_id')->nullable()->constrained('pajaks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexing agar query cepat saat data sudah ribuan
            $table->index(['anggaran_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bkus');
    }
};
