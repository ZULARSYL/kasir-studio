<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('satuan_barang_id');
            $table->foreignId('menu_cafe_id')
                ->nullable()
                ->after('nama_barang')
                ->constrained('menu_cafe')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_cafe_id');
            $table->foreignId('satuan_barang_id')
                ->nullable()
                ->after('nama_barang')
                ->constrained('satuan_barangs')
                ->nullOnDelete();
        });
    }
};
