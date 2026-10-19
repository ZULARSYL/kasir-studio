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
        Schema::create('kasir_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('toko_cabang_id')->constrained('toko_cabang')->cascadeOnDelete();
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->decimal('total_penjualan', 15, 2)->default(0);
            $table->decimal('total_tunai', 15, 2)->default(0);
            $table->decimal('total_non_tunai', 15, 2)->default(0);
            $table->integer('total_transaksi')->default(0);
            $table->decimal('modal_akhir', 15, 2)->nullable();
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->timestamp('waktu_buka')->useCurrent();
            $table->timestamp('waktu_tutup')->nullable();
            $table->text('catatan_buka')->nullable();
            $table->text('catatan_tutup')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasir_shifts');
    }
};
