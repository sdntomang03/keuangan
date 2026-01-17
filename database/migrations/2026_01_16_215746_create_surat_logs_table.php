<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('surat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade');
            $table->string('jenis_surat'); // Contoh: PH, NH, Pesanan, BAPB
            $table->date('tanggal');
            $table->string('nomor_surat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_logs');
    }
};
