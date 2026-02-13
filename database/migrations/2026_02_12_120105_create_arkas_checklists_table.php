<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('arkas_checklists', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel rkas
            $table->foreignId('rkas_id')->constrained('rkas')->onDelete('cascade');
            // Status checklist (true = sudah input, false = belum)
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arkas_checklists');
    }
};
