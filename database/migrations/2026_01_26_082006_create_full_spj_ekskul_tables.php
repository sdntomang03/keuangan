<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Master Nama Ekskul (Supaya tidak ketik manual)
        Schema::create('ref_ekskul', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Contoh: Pramuka, Futsal
            $table->timestamps();
        });

        // 2. Tabel Utama (Header Kwitansi & Perhitungan Pajak)
        Schema::create('spj_ekskul', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade'); // Sumber Dana
            $table->foreignId('rekanan_id')->constrained('rekanans'); // Pelatih
            $table->foreignId('ref_ekskul_id')->constrained('ref_ekskul'); // Jenis Ekskul

            // Info Dasar
            $table->integer('tw')->default(1);

            // Keuangan (SNAPSHOT - Angka dikunci saat simpan)
            $table->integer('jumlah_pertemuan')->default(0);
            $table->decimal('honor', 15, 2)->default(0);

            $table->decimal('total_honor', 15, 2); // BRUTO (Kotor)
            $table->decimal('pph_persen', 5, 2);   // Tarif Pajak (5% atau 6%)
            $table->decimal('pph_nominal', 15, 2); // Rupiah Pajak
            $table->decimal('total_netto', 15, 2); // NETTO (Yang diterima)

            $table->timestamps();
        });

        // 3. Tabel Detail (Bukti Foto Per Pertemuan)
        Schema::create('spj_ekskul_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_ekskul_id')->constrained('spj_ekskul')->onDelete('cascade');
            $table->string('materi');
            $table->date('tanggal_kegiatan'); // Tgl: 2026-01-12
            $table->string('foto_kegiatan');  // Path: spj/foto/xyz.jpg

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spj_ekskul_detail');
        Schema::dropIfExists('spj_ekskul');
        Schema::dropIfExists('ref_ekskul');
    }
};
