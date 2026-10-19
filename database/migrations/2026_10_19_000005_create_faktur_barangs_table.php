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
        Schema::create('faktur_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_faktur')->unique();
            $table->foreignId('toko_cabang_id')->constrained('toko_cabang')->cascadeOnDelete();
            $table->date('tanggal_faktur');
            $table->string('pemasok')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faktur_barangs');
    }
};
