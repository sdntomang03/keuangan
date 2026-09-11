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
        Schema::create('catatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('anggaran_id')->constrained('anggarans')->onDelete('cascade');
            $table->integer('tw');
            $table->text('catatan');
            $table->string('file_path')->nullable(); // Menyimpan path file .webp
            $table->boolean('is_tl')->default(false); // Status Tindak Lanjut (TL)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatans');
    }
};
