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
        Schema::create('akbs', function (Blueprint $table) {
            $table->id();

            // Identitas Akun & Komponen
            $table->string('idakun')->nullable();
            $table->string('idblrinci'); // Kunci unik untuk relasi dan update

            // Detail Satuan & Volume
            $table->string('volume')->nullable();
            $table->integer('pajak')->default(0);

            $table->decimal('totalrincian', 15, 2)->default(0);

            // Anggaran Bulanan (Bulan 1 - 12)
            for ($i = 1; $i <= 12; $i++) {
                $table->decimal("bulan$i", 15, 2)->default(0);
            }

            // Data Aksi & Total
            $table->decimal('totalakb', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);

            // Realisasi Triwulan
            $table->decimal('realtw1', 15, 2)->default(0);
            $table->decimal('realtw2', 15, 2)->default(0);
            $table->decimal('realtw3', 15, 2)->default(0);
            $table->decimal('realtw4', 15, 2)->default(0);
            $table->string('jenis_anggaran')->nullable(); // BOS / BOP
            $table->integer('tahun')->default(2026);
            $table->foreignId('setting_id')->constrained('settings')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['idblrinci', 'tipe_anggaran'], 'unique_akb_per_tipe');
            $table->foreign('idblrinci')->references('idblrinci')->on('rkas')->onDelete('cascade');
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akbs');
    }
};
