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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('id_barang')->unique()->comment('ID unik barang dari sistem asal');
            $table->string('kode_rekening');
            $table->string('nama_rekening');
            $table->string('nama_barang');
            $table->string('satuan', 50);

            // Menggunakan unsignedBigInteger karena harga dalam Rupiah bisa bernilai besar dan tidak minus
            $table->unsignedBigInteger('harga_barang')->default(0);
            $table->unsignedBigInteger('harga_minimal')->default(0);
            $table->unsignedBigInteger('harga_maksimal')->default(0);

            $table->string('kode_belanja');
            $table->string('kategori')->index()->comment('Di-index agar pencarian/filter kategori lebih cepat');
            $table->boolean('digunakan_rkas')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
