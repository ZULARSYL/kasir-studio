<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\SatuanBarang;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_test'],
            [
                'name' => 'Kasir Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    /**
     * Test hak akses: kasir ditolak mengakses menu laporan admin.
     */
    public function test_kasir_cannot_access_laporan_routes(): void
    {
        $kasir = $this->getKasir();

        $this->actingAs($kasir)->get(route('admin.laporan.penjualan'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.laporan.riwayat'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.laporan.pembelian'))->assertForbidden();
    }

    /**
     * Test Laporan Penjualan (Shift Tutup):
     * Memastikan shift tutup tersimpan dan muncul di admin, dapat difilter outlet, perhari, perminggu, perbulan, dan export Excel.
     */
    public function test_laporan_penjualan_filter_and_export(): void
    {
        $admin = $this->getAdmin();
        $kasir = $this->getKasir();

        $cabang1 = TokoCabang::first();
        $cabang2 = TokoCabang::where('id', '!=', $cabang1->id)->first();

        // 1. Buat shift yang sedang 'buka' (tidak boleh muncul di laporan tutup kasir)
        $shiftBuka = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang1->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => Carbon::now()->subHours(2),
        ]);

        // 2. Buat shift yang sudah 'tutup' hari ini di Cabang 1
        $shiftTutupHariIni = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang1->id,
            'modal_awal' => 200000,
            'total_penjualan' => 450000,
            'total_tunai' => 300000,
            'total_non_tunai' => 150000,
            'total_transaksi' => 3,
            'modal_akhir' => 500000,
            'status' => 'tutup',
            'waktu_buka' => Carbon::today()->setHour(8),
            'waktu_tutup' => Carbon::today()->setHour(16),
            'catatan_tutup' => 'Shift pagi lancar',
        ]);

        // 3. Buat shift yang sudah 'tutup' 10 hari lalu di Cabang 2
        $shiftTutupLalu = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang2->id,
            'modal_awal' => 150000,
            'total_penjualan' => 600000,
            'total_tunai' => 400000,
            'total_non_tunai' => 200000,
            'total_transaksi' => 5,
            'modal_akhir' => 550000,
            'status' => 'tutup',
            'waktu_buka' => Carbon::now()->subDays(10)->setHour(9),
            'waktu_tutup' => Carbon::now()->subDays(10)->setHour(17),
            'catatan_tutup' => 'Shift cabang 2 selesai',
        ]);

        // Test A: Index Default (Perhari Hari Ini)
        $response = $this->actingAs($admin)->get(route('admin.laporan.penjualan'));
        $response->assertOk();
        $response->assertSee('Laporan Penjualan (Rekap Tutup Kasir)');
        $response->assertSee($cabang1->nama_cabang);
        $response->assertSee('Rp 450.000'); // total penjualan shift hari ini
        $response->assertDontSee('Shift pagi lancar'); // ada di modal detail, bukan row utama

        // Test B: Filter Toko Cabang 1 vs Cabang 2
        $resCabang1 = $this->actingAs($admin)->get(route('admin.laporan.penjualan', [
            'toko_cabang_id' => $cabang1->id,
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));
        $resCabang1->assertOk();
        $resCabang1->assertSee($cabang1->nama_cabang);

        // Test C: Filter Perminggu
        $resMinggu = $this->actingAs($admin)->get(route('admin.laporan.penjualan', [
            'periode' => 'perminggu',
            'start_date' => Carbon::now()->subDays(12)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'toko_cabang_id' => 'all',
        ]));
        $resMinggu->assertOk();
        $resMinggu->assertSee($cabang1->nama_cabang);
        $resMinggu->assertSee($cabang2->nama_cabang);

        // Test D: Filter Perbulan
        $resBulan = $this->actingAs($admin)->get(route('admin.laporan.penjualan', [
            'periode' => 'perbulan',
            'bulan' => Carbon::today()->month,
            'tahun' => Carbon::today()->year,
            'toko_cabang_id' => 'all',
        ]));
        $resBulan->assertOk();

        // Test E: Detail Shift JSON Endpoint
        $resDetail = $this->actingAs($admin)->get(route('admin.laporan.penjualan.shift-detail', $shiftTutupHariIni->id));
        $resDetail->assertOk();
        $resDetail->assertJson([
            'status' => 'success',
            'shift' => [
                'id' => $shiftTutupHariIni->id,
                'status' => 'tutup',
            ],
        ]);

        // Test F: Export Excel Laporan Penjualan
        $resExport = $this->actingAs($admin)->get(route('admin.laporan.penjualan.export', [
            'toko_cabang_id' => 'all',
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));
        $resExport->assertOk();
        $resExport->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('LAPORAN PENJUALAN KASIR', $resExport->getContent());
        $this->assertStringContainsString('450.000', $resExport->getContent());
    }

    /**
     * Test Riwayat Penjualan (Harian):
     * Memastikan transaksi harian tampil, dapat difilter perhari, dicari, dilihat detailnya, dan diexport ke Excel.
     */
    public function test_riwayat_penjualan_daily_filter_and_export(): void
    {
        $admin = $this->getAdmin();
        $kasir = $this->getKasir();

        $cabang = TokoCabang::first();
        $tipeBayar = TipePembayaran::first();
        $room = Room::first();

        $film = Film::create([
            'judul' => 'Pengabdi Setan 2',
            'genre_id' => 1,
            'durasi_menit' => 119,
            'harga_per_menit' => 1000,
            'total_harga' => 119000,
            'poster' => 'posters/test.jpg',
            'status' => 'tersedia',
        ]);

        $barang = Barang::first();
        $satuan = SatuanBarang::first();

        // Shift kasir untuk transaksi
        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 100000,
            'status' => 'tutup',
            'waktu_buka' => Carbon::today()->setHour(8),
            'waktu_tutup' => Carbon::today()->setHour(18),
        ]);

        // Transaksi Hari Ini
        $trxHariIni = Transaksi::create([
            'nomor_transaksi' => 'TRX-' . date('Ymd') . '-0001',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Budi Santoso',
            'durasi_menit' => 119,
            'harga_film' => 119000,
            'harga_room' => 50000,
            'subtotal_film_room' => 169000,
            'subtotal_cafe' => 35000,
            'diskon' => 0,
            'total_bayar' => 204000,
            'uang_diterima' => 250000,
            'kembalian' => 46000,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::today()->setHour(10),
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $trxHariIni->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 2,
            'harga_satuan' => 17500,
            'subtotal' => 35000,
        ]);

        // Transaksi Kemarin (tidak boleh muncul di filter hari ini)
        $trxKemarin = Transaksi::create([
            'nomor_transaksi' => 'TRX-' . Carbon::yesterday()->format('Ymd') . '-0002',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Siti Aisyah',
            'durasi_menit' => 119,
            'harga_film' => 119000,
            'harga_room' => 50000,
            'subtotal_film_room' => 169000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 169000,
            'uang_diterima' => 200000,
            'kembalian' => 31000,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::yesterday()->setHour(14),
        ]);

        // Test A: Index Default Hari Ini
        $response = $this->actingAs($admin)->get(route('admin.laporan.riwayat', [
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));
        $response->assertOk();
        $response->assertSee('Riwayat Transaksi Penjualan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('TRX-' . date('Ymd') . '-0001');
        $response->assertDontSee('Siti Aisyah');

        // Test B: Filter Tanggal Kemarin
        $resKemarin = $this->actingAs($admin)->get(route('admin.laporan.riwayat', [
            'tanggal' => Carbon::yesterday()->format('Y-m-d'),
        ]));
        $resKemarin->assertOk();
        $resKemarin->assertSee('Siti Aisyah');
        $resKemarin->assertDontSee('Budi Santoso');

        // Test C: Pencarian Kata Kunci
        $resSearch = $this->actingAs($admin)->get(route('admin.laporan.riwayat', [
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'search' => 'Budi',
        ]));
        $resSearch->assertOk();
        $resSearch->assertSee('Budi Santoso');

        // Test D: Detail Transaksi JSON Endpoint
        $resDetail = $this->actingAs($admin)->get(route('admin.laporan.riwayat.detail', $trxHariIni->id));
        $resDetail->assertOk();
        $resDetail->assertJson([
            'status' => 'success',
            'transaksi' => [
                'id' => $trxHariIni->id,
                'nomor_transaksi' => $trxHariIni->nomor_transaksi,
                'nama_pelanggan' => 'Budi Santoso',
            ],
        ]);

        // Test E: Export Excel Riwayat Harian
        $resExport = $this->actingAs($admin)->get(route('admin.laporan.riwayat.export', [
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'toko_cabang_id' => 'all',
        ]));
        $resExport->assertOk();
        $resExport->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('RIWAYAT PENJUALAN HARIAN', $resExport->getContent());
        $this->assertStringContainsString('Budi Santoso', $resExport->getContent());
        $this->assertStringContainsString('204.000', $resExport->getContent());
    }

    /**
     * Test Laporan Pembelian (Faktur Barang):
     * Memastikan data faktur pengadaan muncul, dapat difilter outlet, perhari/perminggu/perbulan, dan diexport ke Excel.
     */
    public function test_laporan_pembelian_filter_and_export(): void
    {
        $admin = $this->getAdmin();

        $cabang1 = TokoCabang::first();
        $cabang2 = TokoCabang::where('id', '!=', $cabang1->id)->first();

        $barang = Barang::first();
        $satuan = SatuanBarang::first();

        // Faktur 1: Hari Ini di Cabang 1
        $fakturHariIni = FakturBarang::create([
            'nomor_faktur' => 'FKT-' . date('Ymd') . '-0001',
            'toko_cabang_id' => $cabang1->id,
            'tanggal_faktur' => Carbon::today(),
            'pemasok' => 'PT Indofood Sukses Makmur',
            'catatan' => 'Pengadaan snack dan minuman',
            'total_nominal' => 1500000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $fakturHariIni->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 100,
            'harga_awal' => 15000,
            'harga_jual' => 20000,
            'subtotal' => 1500000,
        ]);

        // Faktur 2: 7 Hari Lalu di Cabang 2
        $fakturMingguLalu = FakturBarang::create([
            'nomor_faktur' => 'FKT-' . Carbon::now()->subDays(7)->format('Ymd') . '-0002',
            'toko_cabang_id' => $cabang2->id,
            'tanggal_faktur' => Carbon::now()->subDays(7),
            'pemasok' => 'CV Sumber Kopi Mandiri',
            'catatan' => 'Restock biji kopi premium',
            'total_nominal' => 2000000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $fakturMingguLalu->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 50,
            'harga_awal' => 40000,
            'harga_jual' => 55000,
            'subtotal' => 2000000,
        ]);

        // Test A: Index Default (Hari Ini)
        $response = $this->actingAs($admin)->get(route('admin.laporan.pembelian', [
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));
        $response->assertOk();
        $response->assertSee('Laporan Pembelian Barang (Pengadaan Faktur)');
        $response->assertSee('PT Indofood Sukses Makmur');
        $response->assertSee('Rp 1.500.000');
        $response->assertDontSee('CV Sumber Kopi Mandiri');

        // Test B: Filter Perminggu
        $resMinggu = $this->actingAs($admin)->get(route('admin.laporan.pembelian', [
            'periode' => 'perminggu',
            'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'toko_cabang_id' => 'all',
        ]));
        $resMinggu->assertOk();
        $resMinggu->assertSee('PT Indofood Sukses Makmur');
        $resMinggu->assertSee('CV Sumber Kopi Mandiri');

        // Test C: Filter Toko Cabang Tertentu
        $resCabang = $this->actingAs($admin)->get(route('admin.laporan.pembelian', [
            'periode' => 'perminggu',
            'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'toko_cabang_id' => $cabang1->id,
        ]));
        $resCabang->assertOk();
        $resCabang->assertSee('PT Indofood Sukses Makmur');
        $resCabang->assertDontSee('CV Sumber Kopi Mandiri');

        // Test D: Filter Perbulan
        $resBulan = $this->actingAs($admin)->get(route('admin.laporan.pembelian', [
            'periode' => 'perbulan',
            'bulan' => Carbon::today()->month,
            'tahun' => Carbon::today()->year,
            'toko_cabang_id' => 'all',
        ]));
        $resBulan->assertOk();

        // Test E: Detail Faktur JSON Endpoint
        $resDetail = $this->actingAs($admin)->get(route('admin.laporan.pembelian.faktur-detail', $fakturHariIni->id));
        $resDetail->assertOk();
        $resDetail->assertJson([
            'status' => 'success',
            'faktur' => [
                'id' => $fakturHariIni->id,
                'nomor_faktur' => $fakturHariIni->nomor_faktur,
                'pemasok' => 'PT Indofood Sukses Makmur',
            ],
        ]);

        // Test F: Export Excel Laporan Pembelian
        $resExport = $this->actingAs($admin)->get(route('admin.laporan.pembelian.export', [
            'toko_cabang_id' => 'all',
            'periode' => 'perminggu',
            'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]));
        $resExport->assertOk();
        $resExport->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('LAPORAN PEMBELIAN BARANG', $resExport->getContent());
        $this->assertStringContainsString('PT Indofood Sukses Makmur', $resExport->getContent());
        $this->assertStringContainsString('CV Sumber Kopi Mandiri', $resExport->getContent());
    }
}
