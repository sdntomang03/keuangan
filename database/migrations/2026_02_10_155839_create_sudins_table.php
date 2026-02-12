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
        Schema::create('sudins', function (Blueprint $table) {
            $table->id();
            $table->string('nama');          // Contoh: Suku Dinas Pendidikan Wilayah I Kota Adm. Jakarta Barat
            $table->string('singkatan', 10)->nullable(); // Contoh: JB1, JS2
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sudins');
    }
};
