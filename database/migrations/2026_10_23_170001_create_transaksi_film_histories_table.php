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
        if (!Schema::hasColumn('transaksis', 'waktu_mulai_film')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->timestamp('waktu_mulai_film')->nullable()->after('waktu_transaksi');
            });
        }

        Schema::create('transaksi_film_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->cascadeOnDelete();
            $table->foreignId('film_id')->constrained('films')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('judul_film');
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_menit_rencana')->default(0);
            $table->integer('durasi_aktual_menit')->default(0);
            $table->decimal('tarif_per_menit', 15, 2)->default(0);
            $table->decimal('biaya_film', 15, 2)->default(0);
            $table->enum('status', ['berjalan', 'diganti', 'selesai'])->default('berjalan');
            $table->string('alasan_ganti')->nullable();
            $table->decimal('selisih_biaya', 15, 2)->default(0);
            $table->string('tipe_selisih')->default('pas'); // tambahan, refund, pas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_film_histories');
        if (Schema::hasColumn('transaksis', 'waktu_mulai_film')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->dropColumn('waktu_mulai_film');
            });
        }
    }
};
