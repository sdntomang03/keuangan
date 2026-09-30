<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_pagus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggarans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis', 20);
            $table->unsignedTinyInteger('tw')->nullable();
            $table->timestamps();

            $table->index(['anggaran_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_pagus');
    }
};
