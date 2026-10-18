<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_perdurasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('durasi');
            $table->decimal('kalkulasi_harga', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_perdurasi');
    }
};
