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
        Schema::create('rkas', function (Blueprint $table) {
            $table->id();

            // ID unik dari JSON
            $table->string('idblrinci')->unique();
            $table->string('idbl')->nullable();
            $table->string('idsubtitle')->nullable();

            // Deskripsi & Akun
            $table->text('namasub')->nullable(); // Menggunakan text karena mengandung HTML
            $table->string('keterangan')->nullable();
            $table->string('kodeakun')->nullable();
            $table->string('namaakun')->nullable();

            // Komponen & Spek
            $table->string('idkomponen')->nullable();
            $table->string('namakomponen')->nullable();
            $table->text('spek')->nullable(); // Menggunakan text karena bisa panjang

            // Satuan & Harga (Menggunakan decimal agar bisa dihitung di Excel)
            $table->string('satuan')->nullable();
            $table->string('koefisien')->nullable();
            $table->decimal('hargasatuan', 15, 2)->default(0);
            $table->decimal('hargabaru', 15, 2)->nullable();
            $table->decimal('totalharga', 15, 2)->default(0);
            $table->decimal('totalpajak', 15, 2)->default(0);
            $table->foreignId('anggaran_id')->constrained('anggarans')->onDelete('cascade');
            // Tambahan
            $table->string('giatsubteks')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rkas');
    }
};
