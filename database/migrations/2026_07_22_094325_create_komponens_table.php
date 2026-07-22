<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponens', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rekening')->nullable(); // Menyesuaikan dengan kolom 'kode' di tabel koreks
            $table->string('idkomponen')->index();
            $table->text('namakomponen');
            $table->text('spek')->nullable();
            $table->string('satuan')->nullable();
            $table->bigInteger('harga');
            $table->year('tahun');
            $table->timestamps();

            // Opsional: Jika ingin membuat foreign key ke tabel koreks
            $table->foreign('kode_rekening')->references('kode')->on('koreks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponens');
    }
};
