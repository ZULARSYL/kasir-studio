<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('film_stok_cabangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('film_id')->constrained('films')->cascadeOnDelete();
            $table->foreignId('toko_cabang_id')->constrained('toko_cabang')->cascadeOnDelete();
            $table->unsignedInteger('stok')->default(1);
            $table->timestamps();

            $table->unique(['film_id', 'toko_cabang_id']);
        });

        // Migrasikan stok film yang sudah ada agar terhubung ke masing-masing outlet
        $films = DB::table('films')->get();
        $cabangs = DB::table('toko_cabang')->get();

        foreach ($films as $film) {
            foreach ($cabangs as $cabang) {
                DB::table('film_stok_cabangs')->updateOrInsert(
                    [
                        'film_id' => $film->id,
                        'toko_cabang_id' => $cabang->id,
                    ],
                    [
                        'stok' => $film->limit_penggunaan ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('film_stok_cabangs');
    }
};
