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
        // Schema::table('users', function (Blueprint $table) {
        //     // Hanya pasang relasi (Constraint)
        //     $table->foreign('sekolah_id')
        //         ->references('id')
        //         ->on('sekolahs')
        //         ->onDelete('set null');
        // });

        // Schema::table('sekolahs', function (Blueprint $table) {
        //     $table->foreign('user_id')
        //         ->references('id')
        //         ->on('users')
        //         ->onDelete('cascade');
        // });

        // // 3. RKAS (Pastikan kolom idbl, user_id, dan anggaran_id sudah ada di file rkas asli)
        // Schema::table('rkas', function (Blueprint $table) {
        //     $table->foreign('idbl')->references('idbl')->on('kegiatans')->onDelete('set null');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('anggaran_id')->references('id')->on('anggarans')->onDelete('cascade');
        // });

        // // 4. AKBS (Pastikan kolom idblrinci, user_id, dan anggaran_id sudah ada di file akbs asli)
        // Schema::table('akbs', function (Blueprint $table) {
        //     $table->foreign('idblrinci')->references('idblrinci')->on('rkas')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('anggaran_id')->references('id')->on('anggarans')->onDelete('cascade');
        // });

        // // ANGGARANS
        // Schema::table('anggarans', function (Blueprint $table) {
        //     $table->foreign('sekolah_id')->references('id')->on('sekolahs')->onDelete('cascade');
        // });

        // // PAJAKS
        // Schema::table('pajaks', function (Blueprint $table) {
        //     $table->foreign('belanja_id')->references('id')->on('belanjas')->onDelete('cascade');
        //     $table->foreign('dasar_pajak_id')->references('id')->on('dasar_pajaks')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_and_sekolahs', function (Blueprint $table) {
            //
        });
    }
};
