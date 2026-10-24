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
        Schema::create('transaksi_room_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->onDelete('cascade');
            $table->foreignId('room_asal_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('room_tujuan_id')->constrained('rooms')->onDelete('cascade');
            $table->string('nama_room_asal');
            $table->string('nama_room_tujuan');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_user')->nullable();
            $table->dateTime('waktu_pindah');
            $table->integer('durasi_berjalan_menit')->default(0);
            $table->integer('sisa_durasi_menit')->default(0);
            $table->string('alasan_pindah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_room_histories');
    }
};
