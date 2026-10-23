<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateStruk extends Model
{
    use HasFactory;

    protected $table = 'template_struks';

    protected $fillable = [
        'nama_template',
        'lebar_kertas',
        'font_size',
        'nama_usaha',
        'sub_judul',
        'mode_cabang',
        'nama_cabang_manual',
        'alamat',
        'telepon',
        'website_sosmed',
        'pesan_header',
        'tampilkan_logo',
        'logo_path',
        'tampilkan_cabang',
        'tampilkan_alamat',
        'tampilkan_kontak',
        'tampilkan_no_nota',
        'tampilkan_waktu',
        'tampilkan_kasir',
        'tampilkan_pelanggan',
        'tampilkan_detail_room',
        'tampilkan_detail_penonton',
        'tampilkan_detail_cafe',
        'tampilkan_diskon',
        'tampilkan_metode_bayar',
        'tampilkan_kembalian',
        'tampilkan_barcode',
        'tampilkan_waktu_cetak',
        'pesan_footer_1',
        'pesan_footer_2',
        'catatan_kaki',
        'is_aktif',
    ];

    protected $casts = [
        'tampilkan_logo' => 'boolean',
        'tampilkan_cabang' => 'boolean',
        'tampilkan_alamat' => 'boolean',
        'tampilkan_kontak' => 'boolean',
        'tampilkan_no_nota' => 'boolean',
        'tampilkan_waktu' => 'boolean',
        'tampilkan_kasir' => 'boolean',
        'tampilkan_pelanggan' => 'boolean',
        'tampilkan_detail_room' => 'boolean',
        'tampilkan_detail_penonton' => 'boolean',
        'tampilkan_detail_cafe' => 'boolean',
        'tampilkan_diskon' => 'boolean',
        'tampilkan_metode_bayar' => 'boolean',
        'tampilkan_kembalian' => 'boolean',
        'tampilkan_barcode' => 'boolean',
        'tampilkan_waktu_cetak' => 'boolean',
        'is_aktif' => 'boolean',
    ];

    protected $attributes = [
        'nama_template' => 'Template Utama (80mm)',
        'lebar_kertas' => '80mm',
        'font_size' => 'medium',
        'nama_usaha' => 'PRESSENT STUDIO & CAFE',
        'sub_judul' => 'Private Cinema & Cozy Cafe',
        'mode_cabang' => 'otomatis',
        'alamat' => 'Jl. Arifin Ahmad No. 88, Pekanbaru',
        'telepon' => '0812-3456-7890',
        'website_sosmed' => 'IG: @pressentstudio',
        'pesan_header' => null,
        'tampilkan_logo' => false,
        'tampilkan_cabang' => true,
        'tampilkan_alamat' => true,
        'tampilkan_kontak' => true,
        'tampilkan_no_nota' => true,
        'tampilkan_waktu' => true,
        'tampilkan_kasir' => true,
        'tampilkan_pelanggan' => true,
        'tampilkan_detail_room' => true,
        'tampilkan_detail_penonton' => true,
        'tampilkan_detail_cafe' => true,
        'tampilkan_diskon' => true,
        'tampilkan_metode_bayar' => true,
        'tampilkan_kembalian' => true,
        'tampilkan_barcode' => true,
        'tampilkan_waktu_cetak' => true,
        'pesan_footer_1' => 'TERIMA KASIH ATAS KUNJUNGAN ANDA',
        'pesan_footer_2' => 'Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.',
        'catatan_kaki' => 'Tiket & item cafe yang sudah dibeli tidak dapat ditukar/dikembalikan.',
        'is_aktif' => true,
    ];

    /**
     * Ambil template struk yang saat ini aktif, atau buat baru jika belum ada.
     */
    public static function getActiveTemplate(): self
    {
        $active = static::where('is_aktif', true)->latest('updated_at')->first();

        if ($active) {
            return $active;
        }

        $first = static::first();
        if ($first) {
            $first->update(['is_aktif' => true]);
            return $first;
        }

        return static::create([
            'nama_template' => 'Template Utama (80mm)',
            'lebar_kertas' => '80mm',
            'font_size' => 'medium',
            'nama_usaha' => 'PRESSENT STUDIO & CAFE',
            'sub_judul' => 'Private Cinema & Cozy Cafe',
            'mode_cabang' => 'otomatis',
            'alamat' => 'Jl. Arifin Ahmad No. 88, Pekanbaru',
            'telepon' => '0812-3456-7890',
            'website_sosmed' => 'IG: @pressentstudio',
            'pesan_header' => 'Selamat Datang di Pressent Studio Cinema',
            'tampilkan_logo' => false,
            'tampilkan_cabang' => true,
            'tampilkan_alamat' => true,
            'tampilkan_kontak' => true,
            'tampilkan_no_nota' => true,
            'tampilkan_waktu' => true,
            'tampilkan_kasir' => true,
            'tampilkan_pelanggan' => true,
            'tampilkan_detail_room' => true,
            'tampilkan_detail_penonton' => true,
            'tampilkan_detail_cafe' => true,
            'tampilkan_diskon' => true,
            'tampilkan_metode_bayar' => true,
            'tampilkan_kembalian' => true,
            'tampilkan_barcode' => true,
            'tampilkan_waktu_cetak' => true,
            'pesan_footer_1' => 'TERIMA KASIH ATAS KUNJUNGAN ANDA',
            'pesan_footer_2' => 'Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.',
            'catatan_kaki' => 'Tiket & item cafe yang sudah dibeli tidak dapat ditukar/dikembalikan.',
            'is_aktif' => true,
        ]);
    }
}
