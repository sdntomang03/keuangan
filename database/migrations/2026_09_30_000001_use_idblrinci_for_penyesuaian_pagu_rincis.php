<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_pagu_rincis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyesuaian_pagu_id')->constrained('penyesuaian_pagus')->cascadeOnDelete();
            $table->string('idblrinci');
            $table->string('namakomponen')->nullable();
            $table->string('satuan')->nullable();
            $table->unsignedTinyInteger('bulan');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('volume_awal', 15, 2)->default(0);
            $table->decimal('volume_setelah', 15, 2)->default(0);
            $table->decimal('volume_selisih', 15, 2)->default(0);
            $table->unsignedTinyInteger('ppn_persen')->default(0);
            $table->decimal('nominal_selisih', 15, 2)->default(0);
            $table->decimal('nominal_ppn', 15, 2)->default(0);
            $table->decimal('pagu_dapat_digeser', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['penyesuaian_pagu_id', 'idblrinci', 'bulan'], 'penyesuaian_pagu_rinci_idblrinci_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_pagu_rincis');
    }
};
