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
        Schema::create('koreks', function (Blueprint $table) {
            $table->id();
            $table->string('ket')->nullable();            // Kolom Ket
            $table->string('kode')->unique();             // Kolom Kode (sebagai acuan)
            $table->text('uraian_singkat')->nullable();   // Kolom Uraian Singkat
            $table->string('singkat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koreks');
    }
};
