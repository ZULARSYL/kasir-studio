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
        // 1. Tabel Master Data Shifts
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift'); // Shift 1, Shift 2
            $table->string('jam_mulai', 10); // 11:00
            $table->string('jam_selesai', 10); // 16:00, 00:00
            $table->enum('aturan_modal_awal', ['manual', 'otomatis_sebelumnya'])->default('manual');
            $table->integer('urutan')->default(1);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Update kasir_shifts untuk menghubungkan master shift dan mencatat pergantian shift
        Schema::table('kasir_shifts', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('toko_cabang_id')->constrained('shifts')->nullOnDelete();
            $table->string('nama_shift')->nullable()->after('shift_id');
            $table->string('jam_mulai_jadwal', 10)->nullable()->after('nama_shift');
            $table->string('jam_selesai_jadwal', 10)->nullable()->after('jam_mulai_jadwal');
            $table->foreignId('pergantian_dari_kasir_shift_id')->nullable()->after('modal_awal')->constrained('kasir_shifts')->nullOnDelete();
            $table->text('keterangan_waktu')->nullable()->after('catatan_tutup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasir_shifts', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['pergantian_dari_kasir_shift_id']);
            $table->dropColumn([
                'shift_id',
                'nama_shift',
                'jam_mulai_jadwal',
                'jam_selesai_jadwal',
                'pergantian_dari_kasir_shift_id',
                'keterangan_waktu',
            ]);
        });

        Schema::dropIfExists('shifts');
    }
};
