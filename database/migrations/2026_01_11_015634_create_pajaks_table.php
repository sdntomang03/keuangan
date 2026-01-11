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
        Schema::create('pajaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade');

            // Relasi ke master data pajak
            $table->foreignId('dasar_pajak_id')->constrained('dasar_pajaks');

            $table->decimal('nominal', 15, 2);
            $table->boolean('is_terima')->default(false);
            $table->boolean('is_setor')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajaks');
    }
};
