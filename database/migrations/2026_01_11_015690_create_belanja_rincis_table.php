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
        Schema::create('belanja_rincis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('belanja_id')->constrained('belanjas')->onDelete('cascade');
            $table->string('idblrinci');
            $table->string('namakomponen');
            $table->text('spek')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->integer('volume');
            $table->integer('bulan');
            $table->decimal('total_bruto', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanja_rincis');
    }
};
