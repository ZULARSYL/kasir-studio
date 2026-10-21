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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->integer('jumlah_penonton')->default(1)->after('durasi_menit');
            $table->decimal('denda_penonton', 15, 2)->default(0)->after('jumlah_penonton');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['jumlah_penonton', 'denda_penonton']);
        });
    }
};
