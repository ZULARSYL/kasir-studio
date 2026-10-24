<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\GenreFilm;
use App\Models\Jabatan;
use App\Models\JenisBiaya;
use App\Models\MenuCafe;
use App\Models\Promo;
use App\Models\Room;
use App\Models\SatuanBarang;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipe Pembayaran (hanya metode pembayaran saja)
        $tipePembayaran = [
            ['metode_pembayaran' => 'Tunai'],
            ['metode_pembayaran' => 'QRIS'],
            ['metode_pembayaran' => 'Transfer BCA'],
            ['metode_pembayaran' => 'Transfer Mandiri'],
            ['metode_pembayaran' => 'Kartu Debit / EDC'],
        ];
        foreach ($tipePembayaran as $item) {
            TipePembayaran::firstOrCreate(['metode_pembayaran' => $item['metode_pembayaran']], $item);
        }

        // 2. Toko Cabang (nama cabang, alamat, kontak)
        $cabangs = [
            [
                'nama_cabang' => 'Pressent Studio Sudirman',
                'alamat' => 'Jl. Jenderal Sudirman No. 128, Pekanbaru',
                'kontak' => '0812-7788-9900',
            ],
            [
                'nama_cabang' => 'Pressent Studio Panam',
                'alamat' => 'Jl. HR. Soebrantas No. 88, Panam, Pekanbaru',
                'kontak' => '0813-6655-4433',
            ],
        ];
        foreach ($cabangs as $item) {
            TokoCabang::firstOrCreate(['nama_cabang' => $item['nama_cabang']], $item);
        }

        $cabangSudirman = TokoCabang::where('nama_cabang', 'Pressent Studio Sudirman')->first();
        $cabangPanam = TokoCabang::where('nama_cabang', 'Pressent Studio Panam')->first();

        // 4. Room (nama, tipe, kapasitas, cabang dari id toko cabang)
        $rooms = [
            [
                'toko_cabang_id' => $cabangSudirman?->id,
                'nama' => 'Studio Deluxe 01',
                'kapasitas' => 4,
            ],
            [
                'toko_cabang_id' => $cabangSudirman?->id,
                'nama' => 'Studio Deluxe 02',
                'kapasitas' => 4,
            ],
            [
                'toko_cabang_id' => $cabangSudirman?->id,
                'nama' => 'Studio VIP Premiere 01',
                'kapasitas' => 8,
            ],
            [
                'toko_cabang_id' => $cabangPanam?->id,
                'nama' => 'Studio Panam Room 1',
                'kapasitas' => 6,
            ],
        ];
        foreach ($rooms as $item) {
            Room::firstOrCreate(['nama' => $item['nama'], 'toko_cabang_id' => $item['toko_cabang_id']], $item);
        }

        // 5. Promo (kode voucher, nama promo, nilai, periode berlaku, status)
        $promos = [
            [
                'kode_voucher' => 'WEEKDAYSERU',
                'nama_promo' => 'Promo Nonton Weekday',
                'nilai' => '15%',
                'periode_berlaku' => '01 Okt 2026 - 31 Des 2026',
                'status' => 'aktif',
            ],
            [
                'kode_voucher' => 'STUDENT20',
                'nama_promo' => 'Diskon Pelajar & Mahasiswa',
                'nilai' => '20%',
                'periode_berlaku' => '01 Okt 2026 - 31 Des 2026',
                'status' => 'aktif',
            ],
            [
                'kode_voucher' => 'HEMAT10K',
                'nama_promo' => 'Potongan Nonton & Cafe',
                'nilai' => 'Rp 10.000',
                'periode_berlaku' => '15 Okt 2026 - 30 Nov 2026',
                'status' => 'aktif',
            ],
        ];
        foreach ($promos as $item) {
            Promo::firstOrCreate(['kode_voucher' => $item['kode_voucher']], $item);
        }

        // 6. Master Cafe (hanya nama: makanan, minuman, snack)
        $cafeKategori = [
            ['nama' => 'Makanan'],
            ['nama' => 'Minuman'],
            ['nama' => 'Snack'],
        ];
        $createdMenuCafes = [];
        foreach ($cafeKategori as $item) {
            $createdMenuCafes[$item['nama']] = MenuCafe::firstOrCreate(['nama' => $item['nama']], $item);
        }

        // 7. Genre (hanya nama genre)
        $genres = [
            ['nama_genre' => 'Action'],
            ['nama_genre' => 'Horror'],
            ['nama_genre' => 'Comedy'],
            ['nama_genre' => 'Drama'],
            ['nama_genre' => 'Sci-Fi'],
            ['nama_genre' => 'Romance'],
            ['nama_genre' => 'Animation'],
            ['nama_genre' => 'Thriller'],
        ];
        foreach ($genres as $item) {
            GenreFilm::firstOrCreate(['nama_genre' => $item['nama_genre']], $item);
        }

        // 8. Master Jenis Biaya (hanya kategori)
        $biayas = [
            ['kategori' => 'Gaji Karyawan'],
            ['kategori' => 'Listrik dan Air'],
            ['kategori' => 'Internet'],
            ['kategori' => 'Sewa Gedung'],
            ['kategori' => 'Operasional'],
            ['kategori' => 'Bahan Baku'],
            ['kategori' => 'Maintenance'],
        ];
        foreach ($biayas as $item) {
            JenisBiaya::firstOrCreate(['kategori' => $item['kategori']], $item);
        }

        // 9. Jabatan (hanya nama jabatan saja)
        $jabatans = [
            ['nama_jabatan' => 'Store Manager'],
            ['nama_jabatan' => 'Kasir'],
            ['nama_jabatan' => 'Barista'],
            ['nama_jabatan' => 'Teknisi Studio'],
            ['nama_jabatan' => 'Cleaning Service'],
        ];
        foreach ($jabatans as $item) {
            Jabatan::firstOrCreate(['nama_jabatan' => $item['nama_jabatan']], $item);
        }

        // 10. Satuan Barang (seperti pcs, renteng, pack, dus, dll)
        $satuans = [
            ['nama_satuan' => 'Pcs'],
            ['nama_satuan' => 'Renteng'],
            ['nama_satuan' => 'Pack'],
            ['nama_satuan' => 'Dus'],
            ['nama_satuan' => 'Botol'],
            ['nama_satuan' => 'Sachet'],
            ['nama_satuan' => 'Cup'],
            ['nama_satuan' => 'Porsi'],
        ];
        $createdSatuans = [];
        foreach ($satuans as $item) {
            $createdSatuans[$item['nama_satuan']] = SatuanBarang::firstOrCreate(
                ['nama_satuan' => $item['nama_satuan']],
                $item
            );
        }

        // 11. Master Data Barang (nama barang dan kategori menu cafe)
        $barangs = [
            ['nama_barang' => 'Kopi Robusta Blend', 'menu_cafe_id' => $createdMenuCafes['Minuman']->id ?? null],
            ['nama_barang' => 'Susu Kental Manis Carnation', 'menu_cafe_id' => $createdMenuCafes['Makanan']->id ?? null],
            ['nama_barang' => 'Sirup Vanilla Monin', 'menu_cafe_id' => $createdMenuCafes['Minuman']->id ?? null],
            ['nama_barang' => 'Teh Celup Melati Sosro', 'menu_cafe_id' => $createdMenuCafes['Minuman']->id ?? null],
            ['nama_barang' => 'Kopi Sachet Instan GoodDay', 'menu_cafe_id' => $createdMenuCafes['Minuman']->id ?? null],
            ['nama_barang' => 'Mie Instan Goreng Indomie', 'menu_cafe_id' => $createdMenuCafes['Makanan']->id ?? null]
        ];
        foreach ($barangs as $item) {
            Barang::updateOrCreate(
                ['nama_barang' => $item['nama_barang']],
                ['menu_cafe_id' => $item['menu_cafe_id']]
            );
        }

        // 12. Master Data Shift Operasional Kasir (Shift 1 & Shift 2)
        $shifts = [
            [
                'nama_shift' => 'Shift 1',
                'jam_mulai' => '11:00',
                'jam_selesai' => '16:00',
                'aturan_modal_awal' => 'manual',
                'urutan' => 1,
                'status' => 'aktif',
                'keterangan' => 'Jadwal operasional pukul 11.00–16.00 WIB. Modal awal dimasukkan manual oleh kasir.',
            ],
            [
                'nama_shift' => 'Shift 2',
                'jam_mulai' => '16:00',
                'jam_selesai' => '00:00',
                'aturan_modal_awal' => 'otomatis_sebelumnya',
                'urutan' => 2,
                'status' => 'aktif',
                'keterangan' => 'Jadwal operasional pukul 16.00–00.00 WIB. Modal awal otomatis mengambil saldo akhir Shift 1.',
            ],
        ];

        foreach ($shifts as $s) {
            \App\Models\Shift::updateOrCreate(
                ['nama_shift' => $s['nama_shift']],
                $s
            );
        }
    }
}
