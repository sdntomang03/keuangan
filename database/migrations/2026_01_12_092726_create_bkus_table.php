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
            $table->foreignId('setting_id')->constrained('settings')->onDelete('cascade');

            // 2. Identitas Anggaran & Tahun (Multi-Year & Multi-Budget)
            $table->string('jenis_anggaran');

            $table->integer('no_urut');
            $table->date('tanggal');
            $table->string('no_bukti');
            $table->string('uraian');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);

            // Relasi opsional untuk tracking sumber data
            $table->foreignId('belanja_id')->nullable()->constrained('belanjas')->onDelete('set null');
            $table->foreignId('pajak_id')->nullable()->constrained('pajaks')->onDelete('set null');

            $table->timestamps();

            // Indexing agar query cepat saat data sudah ribuan
            $table->index(['setting_id', 'anggaran_id']);
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
