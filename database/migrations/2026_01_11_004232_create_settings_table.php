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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke Admin
            $table->string('nama_sekolah');
            $table->string('nama_kepala_sekolah');
            $table->string('nip_kepala_sekolah');
            $table->string('nama_bendahara');
            $table->string('nip_bendahara');
            $table->year('tahun_aktif');
            $table->string('anggaran_aktif');
            $table->integer('triwulan_aktif');
            $table->text('alamat')->nullable();
            $table->string('logo')->nullable(); // Jika ingin upload logo sekolah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
