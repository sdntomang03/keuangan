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
        Schema::create('npds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');
            $table->string('nomor_npd');
            $table->date('tanggal');

            // GANTI INI: Sesuaikan dengan RKAS
            $table->unsignedBigInteger('idbl'); // Pengganti kegiatan_id
            $table->string('kodeakun');         // Pengganti korek_id (String karena kode rekening biasanya string)

            $table->text('uraian');
            $table->decimal('nilai_npd', 15, 2);

            // Snapshot History
            $table->decimal('pagu_anggaran', 15, 2)->default(0);
            $table->decimal('total_realisasi', 15, 2)->default(0);
            $table->decimal('sisa_anggaran', 15, 2)->default(0);
            $table->foreignId('anggaran_id')->constrained('anggarans')->onDelete('cascade');
            // Status
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'cair', 'ditolak'])->default('diajukan');
            $table->integer('triwulan')->default(1);
            $table->timestamps();

            // Indexing biar cepat
            $table->index(['sekolah_id', 'idbl', 'kodeakun']);
        });
    }
};
