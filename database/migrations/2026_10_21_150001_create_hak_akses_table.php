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
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->string('target_key', 150)->unique(); // Contoh: 'role:admin', 'role:kasir', 'jabatan:1'
            $table->string('tipe', 50); // 'role' atau 'jabatan'
            $table->string('role', 100)->nullable(); // 'admin', 'kasir', dll.
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->onDelete('cascade');
            $table->json('menus')->nullable(); // Array of menu keys yang diizinkan
            $table->timestamps();

            $table->index(['tipe', 'role']);
            $table->index(['tipe', 'jabatan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_akses');
    }
};
