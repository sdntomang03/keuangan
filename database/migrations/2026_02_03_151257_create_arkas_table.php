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
        Schema::create('arkas', function (Blueprint $table) {
            // ID Utama Laravel (Auto Increment)
            $table->id();

            // ID Barang (Dari ARKAS biasanya string unik)
            $table->string('id_barang')->nullable()->index();

            // Rekening
            $table->string('kode_rekening')->nullable()->index();
            $table->string('nama_rekening')->nullable();

            // Barang
            $table->text('nama_barang')->nullable();
            $table->string('satuan', 50)->nullable();

            // Harga (Presisi 18 digit, 2 desimal)
            $table->decimal('harga_barang', 18, 2)->default(0);
            $table->decimal('harga_minimal', 18, 2)->default(0);
            $table->decimal('harga_maksimal', 18, 2)->default(0);

            // Klasifikasi Belanja
            $table->string('kode_belanja')->nullable(); // KodeBelanja
            $table->string('jenis_belanja')->nullable(); // Jenisbelanja

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arkas');
    }
};
