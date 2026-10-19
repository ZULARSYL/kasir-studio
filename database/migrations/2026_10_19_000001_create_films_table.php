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
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('poster')->nullable();
            $table->text('sinopsis')->nullable();
            $table->unsignedInteger('durasi_menit')->default(0);
            $table->decimal('harga_per_menit', 12, 2)->default(0);
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->unsignedSmallInteger('tahun_rilis')->nullable();
            $table->string('sutradara')->nullable();
            $table->enum('usia_rating', ['SU', '13+', '17+', '21+'])->default('SU');
            $table->enum('status', ['tersedia', 'tidak_tersedia'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
