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
        Schema::create('talangans', function (Blueprint $table) {
            $table->id();
            // Identitas Surat (Menghubungkan baris-baris tagihan yang sama)
            $table->string('surat_id')->index();

            $table->unsignedBigInteger('anggaran_id');
            $table->integer('triwulan');
            $table->string('kodeakun');
            $table->string('kodepelanggan');
            $table->string('bulan');
            $table->bigInteger('jumlah');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talangans');
    }
};
