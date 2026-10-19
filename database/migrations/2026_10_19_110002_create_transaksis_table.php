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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->foreignId('kasir_shift_id')->nullable()->constrained('kasir_shifts')->nullOnDelete();
            $table->foreignId('toko_cabang_id')->constrained('toko_cabang')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('film_id')->nullable()->constrained('films')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('tipe_pembayaran_id')->constrained('tipe_pembayarans')->cascadeOnDelete();
            $table->foreignId('promo_id')->nullable()->constrained('promos')->nullOnDelete();
            $table->string('nama_pelanggan')->default('Pelanggan Umum');
            $table->integer('durasi_menit')->default(0);
            $table->decimal('harga_film', 15, 2)->default(0);
            $table->decimal('harga_room', 15, 2)->default(0);
            $table->decimal('subtotal_film_room', 15, 2)->default(0);
            $table->decimal('subtotal_cafe', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->decimal('uang_diterima', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->string('status')->default('selesai');
            $table->timestamp('waktu_transaksi')->useCurrent();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
