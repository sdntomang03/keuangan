<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('anggaran_id')->constrained('anggarans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->string('tahun', 4);
            $table->string('nomor_surat')->nullable();
            $table->string('sumber_dana');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('penerimaan_tahap_i', 15, 2)->default(0);
            $table->decimal('penerimaan_tahap_ii', 15, 2)->default(0);
            $table->decimal('penerimaan_kinerja', 15, 2)->default(0);
            $table->decimal('penerimaan_lain', 15, 2)->default(0);
            $table->decimal('pengeluaran_barang_jasa', 15, 2)->default(0);
            $table->decimal('pengeluaran_modal_peralatan', 15, 2)->default(0);
            $table->decimal('pengeluaran_modal_aset_lainnya', 15, 2)->default(0);
            $table->decimal('pengeluaran_lain', 15, 2)->default(0);
            $table->decimal('kas_tunai', 15, 2)->default(0);
            $table->decimal('rekening_bank', 15, 2)->default(0);
            $table->text('penjelasan_selisih')->nullable();
            $table->string('kota_surat')->default('Jakarta');
            $table->date('tanggal_surat');
            $table->timestamps();

            $table->unique(['anggaran_id', 'tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_bulanans');
    }
};
