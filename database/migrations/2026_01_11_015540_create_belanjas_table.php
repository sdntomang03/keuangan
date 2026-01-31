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
        Schema::create('belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('idbl')->nullable();
            $table->string('kodeakun')->nullable();
            $table->foreignId('rekanan_id')->constrained('rekanans'); // Relasi ke master rekanan
            $table->date('tanggal');
            $table->date('tanggal_bast')->nullable(); // Tanggal BAST
            $table->string('no_bast')->nullable();
            $table->string('no_bukti');
            $table->string('rincian')->nullable();
            $table->string('uraian');
            $table->decimal('subtotal', 15, 2);    // Total Bruto (Sum of belanja_rincis)
            $table->decimal('ppn', 15, 2);    // Total Bruto (Sum of belanja_rincis)
            $table->decimal('pph', 15, 2);    // Total Bruto (Sum of belanja_rincis)
            $table->decimal('transfer', 15, 2); // Netto (Total - Pajak)
            $table->enum('status', ['draft', 'posted', 'deleted'])->default('draft');
            $table->foreignId('anggaran_id')->nullable()->constrained('anggarans')->onDelete('cascade');
            $table->integer('tw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanjas');
    }
};
