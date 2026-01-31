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
        Schema::table('bkus', function (Blueprint $table) {
            // Menambahkan kolom penerimaan_id yang berelasi ke tabel penerimaans
            // onDelete('cascade') memastikan jika penerimaan dihapus, baris di BKU ikut terhapus
            $table->foreignId('penerimaan_id')
                ->nullable()
                ->after('belanja_id')
                ->constrained('penerimaans')
                ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::table('bkus', function (Blueprint $table) {
            $table->dropForeign(['penerimaan_id']);
            $table->dropColumn('penerimaan_id');
        });
    }
};
