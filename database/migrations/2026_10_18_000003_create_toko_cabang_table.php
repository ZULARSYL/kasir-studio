<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toko_cabang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_cabang');
            $table->text('alamat');
            $table->string('kontak');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toko_cabang');
    }
};
