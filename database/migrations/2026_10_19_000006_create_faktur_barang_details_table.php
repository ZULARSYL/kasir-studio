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
        Schema::create('faktur_barang_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faktur_barang_id')->constrained('faktur_barangs')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->foreignId('satuan_barang_id')->constrained('satuan_barangs')->cascadeOnDelete();
            $table->decimal('qty', 10, 2);
            $table->decimal('harga_awal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faktur_barang_details');
    }
};
