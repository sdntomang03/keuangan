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
        Schema::create('rekanans', function (Blueprint $table) {
            $table->id();

            // Relasi ke Sekolah (Sesuai permintaan sebelumnya)
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');

            // Identitas Rekanan / Toko
            $table->string('nama_rekanan');
            $table->text('alamat')->nullable(); // Pakai text agar muat panjang
            $table->text('alamat2')->nullable(); // Pakai text agar muat panjang
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('no_telp')->nullable(); // Opsional: biasanya butuh untuk kontak

            // Data Pimpinan & PIC
            $table->string('nama_pimpinan')->nullable(); // Pemilik Toko (utk TTD kontrak)
            $table->string('pic')->nullable();           // Person In Charge (Kontak person)
            $table->string('jabatan')->nullable();       // Jabatan PIC

            // Data Keuangan & Pajak
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('npwp')->nullable();
            $table->string('pkp')->nullable(); // Status Pengusaha Kena Pajak

            // Lainnya
            $table->text('ket')->nullable(); // Keterangan tambahan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekanans');
    }
};
