<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\MenuCafe;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanStokTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_stok_tester'],
            [
                'name' => 'Admin Stok Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_stok_tester'],
            [
                'name' => 'Kasir Stok Tester',
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
     * Test hak akses: kasir ditolak mengakses menu laporan stok dan ekspornya.
     */
    public function test_kasir_cannot_access_laporan_stok(): void
    {
        $kasir = $this->getKasir();

        $this->actingAs($kasir)->get(route('admin.laporan.stok'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.laporan.stok.export'))->assertForbidden();
    }

    /**
     * Test admin dapat mengakses halaman laporan stok dan melihat ringkasan metrik & tabel.
     */
    public function test_admin_can_view_laporan_stok_page_with_metrics_and_items(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();
        $satuan = SatuanBarang::first();

        // Buat kategori & barang
        $kategori = MenuCafe::firstOrCreate(
            ['nama' => 'Snack & Makanan Ringan'],
            ['keterangan' => 'Kategori Snack']
        );

        $barang1 = Barang::create([
            'menu_cafe_id' => $kategori->id,
            'nama_barang' => 'Keripik Kentang Renyah',
            'satuan_barang_id' => $satuan->id,
            'status' => 'tersedia',
        ]);

        $barang2 = Barang::create([
            'menu_cafe_id' => $kategori->id,
            'nama_barang' => 'Air Mineral Botol 600ml',
            'satuan_barang_id' => $satuan->id,
            'status' => 'tersedia',
        ]);

        // Buat Faktur Pengadaan Stok untuk Hari Ini
        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-STOK-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => Carbon::today(),
            'pemasok' => 'Supplier Utama',
            'catatan' => 'Restock Barang',
            'total_nominal' => 700000,
            'user_id' => $admin->id,
        ]);

        // Barang 1: 50 pcs @ Rp 10.000 = Rp 500.000
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang1->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 50,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 500000,
        ]);

        // Barang 2: 40 pcs @ Rp 5.000 = Rp 200.000
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang2->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 40,
            'harga_awal' => 5000,
            'harga_jual' => 8000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee('Laporan Stok Barang');
        $response->assertSee('Grand Total Nilai Aset');
        $response->assertSee('Grand Total Kuantitas');
        $response->assertSee('BANYAKNYA STOK DI SISTEM');
        $response->assertSee('TOTAL ASET PER ITEM');
        $response->assertSee('GRAND TOTAL KESELURUHAN STOK');

        // Cek data barang 1 dan barang 2
        $response->assertSee('Keripik Kentang Renyah');
        $response->assertSee('Air Mineral Botol 600ml');
        $response->assertSee('50'); // Stok barang 1
        $response->assertSee('40'); // Stok barang 2
        $response->assertSee('500.000'); // Total aset barang 1
        $response->assertSee('200.000'); // Total aset barang 2
        $response->assertSee('700.000'); // Grand total aset
        $response->assertSee('90 Unit'); // Grand total kuantitas (50 + 40)
    }

    /**
     * Test filter periode (perhari, perminggu, perbulan) dan filter cabang/pencarian.
     */
    public function test_laporan_stok_filters(): void
    {
        $admin = $this->getAdmin();
        $cabangs = TokoCabang::take(2)->get();
        $cabang1 = $cabangs[0];
        $cabang2 = $cabangs[1];
        $satuan = SatuanBarang::first();

        $barangA = Barang::create([
            'nama_barang' => 'Kopi Arabika Premium',
            'satuan_barang_id' => $satuan->id,
            'status' => 'tersedia',
        ]);

        $barangB = Barang::create([
            'nama_barang' => 'Teh Melati Wangi',
            'satuan_barang_id' => $satuan->id,
            'status' => 'tersedia',
        ]);

        // Faktur 1: Hari Ini di Cabang 1 (Barang A: 25 pcs = Rp 250.000)
        $faktur1 = FakturBarang::create([
            'nomor_faktur' => 'FKT-001',
            'toko_cabang_id' => $cabang1->id,
            'tanggal_faktur' => Carbon::today(),
            'pemasok' => 'Supplier A',
            'total_nominal' => 250000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur1->id,
            'barang_id' => $barangA->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 25,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 250000,
        ]);

        // Faktur 2: 20 Hari Lalu (Bulan ini atau bulan lalu) di Cabang 2 (Barang B: 30 pcs = Rp 300.000)
        $tanggalFaktur2 = Carbon::today()->subMonths(2)->startOfMonth();
        $faktur2 = FakturBarang::create([
            'nomor_faktur' => 'FKT-002',
            'toko_cabang_id' => $cabang2->id,
            'tanggal_faktur' => $tanggalFaktur2,
            'pemasok' => 'Supplier B',
            'total_nominal' => 300000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur2->id,
            'barang_id' => $barangB->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 30,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 300000,
        ]);

        // 1. Filter Hari Ini: Hanya Faktur 1 (Barang A) yang muncul
        $respHari = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]));
        $respHari->assertOk();
        $respHari->assertSee('Kopi Arabika Premium');
        $respHari->assertDontSee('Teh Melati Wangi');
        $respHari->assertSee('25 Unit');

        // 2. Filter Perbulan: Ambil bulan 2 bulan lalu
        $respBulan = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'perbulan',
            'bulan' => $tanggalFaktur2->month,
            'tahun' => $tanggalFaktur2->year,
        ]));
        $respBulan->assertOk();
        $respBulan->assertSee('Teh Melati Wangi');
        $respBulan->assertDontSee('Kopi Arabika Premium');
        $respBulan->assertSee('30 Unit');

        // 3. Filter Perminggu: Rentang hari ini
        $respMinggu = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'perminggu',
            'start_date' => Carbon::today()->startOfWeek()->format('Y-m-d'),
            'end_date' => Carbon::today()->endOfWeek()->format('Y-m-d'),
        ]));
        $respMinggu->assertOk();
        $respMinggu->assertSee('Kopi Arabika Premium');
        $respMinggu->assertDontSee('Teh Melati Wangi');

        // 4. Filter Toko Cabang: Hanya cabang 1 pada semua periode
        $respCabang = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'semua',
            'toko_cabang_id' => $cabang1->id,
        ]));
        $respCabang->assertOk();
        $respCabang->assertSee('Kopi Arabika Premium');
        $respCabang->assertDontSee('Teh Melati Wangi');

        // 5. Filter Pencarian Text
        $respSearch = $this->actingAs($admin)->get(route('admin.laporan.stok', [
            'periode' => 'semua',
            'search' => 'Arabika',
        ]));
        $respSearch->assertOk();
        $respSearch->assertSee('Kopi Arabika Premium');
        $respSearch->assertDontSee('Teh Melati Wangi');
    }

    /**
     * Test ekspor laporan stok ke format Excel (.xls).
     */
    public function test_laporan_stok_export_excel(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();
        $satuan = SatuanBarang::first();

        $barang = Barang::create([
            'nama_barang' => 'Popcorn Caramel Jumbo',
            'satuan_barang_id' => $satuan->id,
            'status' => 'tersedia',
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-EXPORT-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => Carbon::today(),
            'pemasok' => 'Supplier Bioskop',
            'total_nominal' => 450000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 15,
            'harga_awal' => 30000,
            'harga_jual' => 45000,
            'subtotal' => 450000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.stok.export', [
            'periode' => 'perhari',
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'toko_cabang_id' => 'all',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->getContent();
        $this->assertStringContainsString('LAPORAN STOK BARANG &amp; TOTAL NILAI ASET', $content);
        $this->assertStringContainsString('Popcorn Caramel Jumbo', $content);
        $this->assertStringContainsString('450.000', $content);
        $this->assertStringContainsString('15', $content);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN STOK', $content);
    }
}
