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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('toko_cabang_id')->nullable()->after('password')->constrained('toko_cabang')->nullOnDelete();
            $table->foreignId('pegawai_id')->nullable()->after('toko_cabang_id')->constrained('pegawais')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->after('pegawai_id')->constrained('jabatans')->nullOnDelete();
            $table->string('role', 100)->default('kasir')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['toko_cabang_id']);
            $table->dropForeign(['pegawai_id']);
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn(['toko_cabang_id', 'pegawai_id', 'jabatan_id']);
        });
    }
};
