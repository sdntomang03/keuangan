<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekanans', function (Blueprint $table) {
            $table->id();

            // UBAH DARI user_id KE sekolah_id
            // Pastikan tabel 'sekolahs' sudah ada sebelum migration ini jalan
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');

            $table->string('nama_rekanan');

            // Tambahan Kolom (PENTING untuk Cetak SPJ/Word)
            $table->text('alamat')->nullable();         // Alamat Toko
            $table->string('provinsi')->nullable();     // Provinsi (untuk kop surat rekanan)
            $table->string('pimpinan')->nullable();     // Nama Pimpinan Toko (untuk TTD)
            $table->string('no_telp')->nullable();      // No HP Toko

            $table->string('npwp')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekanans');
    }
};
