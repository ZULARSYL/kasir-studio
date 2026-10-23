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
        Schema::create('template_struks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template')->default('Template Utama (80mm)');
            $table->string('lebar_kertas')->default('80mm'); // 80mm atau 58mm
            $table->string('font_size')->default('medium'); // small, medium, large
            
            // Header Toko
            $table->string('nama_usaha')->default('PRESSENT STUDIO & CAFE');
            $table->string('sub_judul')->nullable()->default('Private Cinema & Cozy Cafe');
            $table->string('mode_cabang')->default('otomatis'); // otomatis / manual
            $table->string('nama_cabang_manual')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable()->default('0812-3456-7890');
            $table->string('website_sosmed')->nullable()->default('IG: @pressentstudio');
            $table->text('pesan_header')->nullable();
            $table->boolean('tampilkan_logo')->default(false);
            $table->string('logo_path')->nullable();

            // Toggle Tampilan Informasi & Transaksi
            $table->boolean('tampilkan_cabang')->default(true);
            $table->boolean('tampilkan_alamat')->default(true);
            $table->boolean('tampilkan_kontak')->default(true);
            $table->boolean('tampilkan_no_nota')->default(true);
            $table->boolean('tampilkan_waktu')->default(true);
            $table->boolean('tampilkan_kasir')->default(true);
            $table->boolean('tampilkan_pelanggan')->default(true);
            $table->boolean('tampilkan_detail_room')->default(true);
            $table->boolean('tampilkan_detail_penonton')->default(true);
            $table->boolean('tampilkan_detail_cafe')->default(true);
            $table->boolean('tampilkan_diskon')->default(true);
            $table->boolean('tampilkan_metode_bayar')->default(true);
            $table->boolean('tampilkan_kembalian')->default(true);
            $table->boolean('tampilkan_barcode')->default(true);
            $table->boolean('tampilkan_waktu_cetak')->default(true);

            // Footer Struk
            $table->text('pesan_footer_1')->nullable();
            $table->text('pesan_footer_2')->nullable();
            $table->text('catatan_kaki')->nullable();

            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_struks');
    }
};
