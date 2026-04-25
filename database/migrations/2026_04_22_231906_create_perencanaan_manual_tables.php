<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LEVEL 1: TABEL MASTER INDEPENDEN (Tidak punya Foreign Key ke tabel internal)

        // 1. Master Sumber Dana
        Schema::create('sumber_dana_manuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index()->nullable();
            $table->string('kode');
            $table->string('nama');
            $table->timestamps();
        });

        // 2. Tabel Program (Standar Pendidikan - NORMALISASI LEVEL 1)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');
            $table->timestamps();
        });

        // LEVEL 2: TABEL YANG BERGANTUNG PADA LEVEL 1

        // 3. Tabel Sub Program (Kategori - NORMALISASI LEVEL 2)
        Schema::create('sub_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('nama_sub_program');
            $table->timestamps();
        });

        // LEVEL 3: TABEL YANG BERGANTUNG PADA LEVEL 2

        // 4. Master Uraian Kegiatan Dasar
        Schema::create('uraian_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_program_id')->constrained('sub_programs')->onDelete('cascade');
            $table->text('nama_uraian');
            $table->timestamps();
        });

        // LEVEL 4: TRANSAKSI / PEMETAAN

        // 5. Kegiatan Manual (Titik Temu Hierarki per Sekolah)
        Schema::create('kegiatan_manuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index()->nullable();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('sub_program_id')->constrained('sub_programs')->onDelete('cascade');

            // Relasi ke Sumber Dana (Best Practice: set null agar data histori tidak hilang)
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_dana_manuals')->onDelete('set null');
            $table->string('id_kegiatan');
            $table->timestamps();

            $table->unique(['school_id', 'id_kegiatan']);
        });

        // 6. Rincian Kegiatan (Sub-Title / Grup Belanja)
        // BEST PRACTICE: Dihubungkan ke kegiatan_manuals, BUKAN sub_programs,
        // karena Grup Belanja spesifik untuk satu kegiatan tertentu di suatu sekolah.
        Schema::create('rincian_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_manual_id')->constrained('kegiatan_manuals')->onDelete('cascade');
            $table->string('nama_rincian'); // Contoh: "Belanja ATK Ujian", "Honor Panitia"
            $table->timestamps();
        });

        // 7. Master Komponen (Barang/Jasa)
        Schema::create('komponen_manuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->foreignId('korek_id')->constrained('koreks')->onDelete('cascade');
            $table->string('id_komponen');
            $table->text('nama');
            $table->text('spesifikasi')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'id_komponen']);
        });

        // LEVEL 5: TRANSAKSI AKHIR (BERGANTUNG PADA SEMUA LEVEL DI ATASNYA)

        // 8. Tabel RKAS Manual
        Schema::create('rkas_manuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->year('tahun_anggaran');

            $table->foreignId('sumber_dana_id')->constrained('sumber_dana_manuals');
            $table->foreignId('kegiatan_manual_id')->constrained('kegiatan_manuals');
            $table->foreignId('korek_id')->constrained('koreks');

            // Komponen opsional (bisa manual ketik tanpa master)
            $table->foreignId('komponen_manual_id')->nullable()->constrained('komponen_manuals')->onDelete('set null');

            $table->foreignId('uraian_id')->constrained('uraian_kegiatans')->onDelete('cascade');
            // Sub-Title opsional
            $table->foreignId('rincian_kegiatan_id')->nullable()->constrained('rincian_kegiatans')->onDelete('set null');
            $table->text('nama_komponen');
            $table->text('spesifikasi')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->integer('volume');
            $table->decimal('jumlah_harga', 15, 2);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 9. Tabel AKB (Aliran Kas Bulanan)
        Schema::create('akb_manuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rkas_manual_id')->constrained('rkas_manuals')->onDelete('cascade');
            $table->tinyInteger('bulan');
            $table->integer('volume_bulan');
            $table->decimal('jumlah_biaya', 15, 2);
            $table->timestamps();

            $table->unique(['rkas_manual_id', 'bulan']);
        });
    }

    public function down(): void
    {
        // BEST PRACTICE: Harus dibalik persis 100% dari urutan public function up()
        // Dari anak paling bawah, ke kakek moyang paling atas

        Schema::dropIfExists('akb_manuals');          // Tergantung pada rkas_manuals
        Schema::dropIfExists('rkas_manuals');         // Tergantung pada banyak hal

        Schema::dropIfExists('komponen_manuals');     // Master level bawah
        Schema::dropIfExists('rincian_kegiatans');    // Tergantung pada kegiatan_manuals
        Schema::dropIfExists('kegiatan_manuals');     // Tergantung pada uraians, program, sub_program

        Schema::dropIfExists('uraian_kegiatans');       // Tergantung pada sub_programs
        Schema::dropIfExists('sub_programs');         // Tergantung pada programs
        Schema::dropIfExists('programs');             // Master puncak

        Schema::dropIfExists('sumber_dana_manuals');  // Master puncak
    }
};
