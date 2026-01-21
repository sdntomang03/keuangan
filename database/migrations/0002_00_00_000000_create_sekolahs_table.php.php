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
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();
            // Relasi ke User (Owner/Admin Sekolah)

            // Identitas Sekolah
            $table->string('nama_sekolah');
            $table->string('npsn')->nullable(); // Tambahan standar untuk data sekolah
            $table->string('nama_kepala_sekolah');
            $table->string('nip_kepala_sekolah');
            $table->string('nama_bendahara');
            $table->string('nip_bendahara');
            $table->foreignId('user_id');
            // Status Aktif (Sekarang menggunakan ID Anggaran)
            $table->integer('anggaran_id_aktif')->nullable();
            $table->integer('triwulan_aktif')->default(1);

            // Alamat Lengkap
            $table->text('alamat')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('kodepos')->nullable();
            $table->string('telp')->nullable();
            $table->string('email')->nullable();

            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
