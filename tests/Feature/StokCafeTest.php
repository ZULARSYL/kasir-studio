<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\MenuCafe;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StokCafeTest extends TestCase
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
     * Test halaman stok cafe dapat diakses oleh admin saat belum ada data faktur.
     */
    public function test_stok_cafe_page_loads_with_empty_state(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.cafe.stok'));
        $response->assertOk();
        $response->assertSee('Stok Barang Cafe (Inventaris Outlet)');
        $response->assertSee('Total Nilai Aset');
        $response->assertSee('Total Kuantitas Barang');
        $response->assertSee('Belum Ada Stok Barang Tercatat');
        $response->assertSee('Rp 0');
    }

    /**
     * Test kasir tidak memiliki izin akses ke halaman stok admin.
     */
    public function test_kasir_cannot_access_admin_stok_page(): void
    {
        $kasir = $this->getKasir();

        $response = $this->actingAs($kasir)->get(route('admin.cafe.stok'));
        $response->assertForbidden();
    }

    /**
     * Test stok terakumulasi dari faktur barang, menampilkan nama barang, outlet, kategori, satuan, stok, serta card total asset & barang.
     */
    public function test_stok_aggregates_from_faktur_barangs_and_displays_required_fields(): void
    {
        $admin = $this->getAdmin();

        $outletA = TokoCabang::first();
        $outletB = TokoCabang::skip(1)->first();

        $kategoriMakanan = MenuCafe::where('nama', 'Makanan')->first();
        $kategoriMinuman = MenuCafe::where('nama', 'Minuman')->first();

        $satuanPcs = SatuanBarang::where('nama_satuan', 'Pcs')->first();
        $satuanDus = SatuanBarang::where('nama_satuan', 'Dus')->first();

        // Buat barang spesifik
        $barangKopi = Barang::create([
            'nama_barang' => 'Kopi Robusta Aceh',
            'menu_cafe_id' => $kategoriMinuman->id,
        ]);

        $barangRoti = Barang::create([
            'nama_barang' => 'Roti Bakar Coklat',
            'menu_cafe_id' => $kategoriMakanan->id,
        ]);

        // 1. Buat Faktur 1 untuk Outlet A (Kopi: 20 Pcs @ 10.000 = 200.000)
        $faktur1 = FakturBarang::create([
            'nomor_faktur' => 'FKT-TEST-001',
            'toko_cabang_id' => $outletA->id,
            'tanggal_faktur' => '2026-10-18',
            'pemasok' => 'Supplier Kopi Nusantara',
            'total_nominal' => 200000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur1->id,
            'barang_id' => $barangKopi->id,
            'satuan_barang_id' => $satuanPcs->id,
            'qty' => 20,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 200000,
        ]);

        // 2. Buat Faktur 2 untuk Outlet A (Kopi tambahan: 30 Pcs @ 12.000 = 360.000)
        // Total kopi di Outlet A harus jadi 20 + 30 = 50 Pcs, asset = 560.000
        $faktur2 = FakturBarang::create([
            'nomor_faktur' => 'FKT-TEST-002',
            'toko_cabang_id' => $outletA->id,
            'tanggal_faktur' => '2026-10-19',
            'pemasok' => 'Supplier Kopi Nusantara',
            'total_nominal' => 360000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur2->id,
            'barang_id' => $barangKopi->id,
            'satuan_barang_id' => $satuanPcs->id,
            'qty' => 30,
            'harga_awal' => 12000,
            'harga_jual' => 16000,
            'subtotal' => 360000,
        ]);

        // 3. Buat Faktur 3 untuk Outlet B (Roti: 10 Dus @ 50.000 = 500.000)
        $faktur3 = FakturBarang::create([
            'nomor_faktur' => 'FKT-TEST-003',
            'toko_cabang_id' => $outletB->id,
            'tanggal_faktur' => '2026-10-19',
            'pemasok' => 'Bakery Segar',
            'total_nominal' => 500000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur3->id,
            'barang_id' => $barangRoti->id,
            'satuan_barang_id' => $satuanDus->id,
            'qty' => 10,
            'harga_awal' => 50000,
            'harga_jual' => 70000,
            'subtotal' => 500000,
        ]);

        // Total keseluruhan:
        // Total asset = 200.000 + 360.000 + 500.000 = 1.060.000
        // Total barang fisik = 20 + 30 + 10 = 60
        // Total varian barang = 2
        // Total outlet = 2

        $response = $this->actingAs($admin)->get(route('admin.cafe.stok'));
        $response->assertOk();

        // Cek Card Total Asset & Total Barang
        $response->assertSee('Rp 1.060.000');
        $response->assertSee('60'); // Total kuantitas barang

        // Cek Fields Wajib yang diminta pada tabel:
        // 1. Nama barang
        $response->assertSee('Kopi Robusta Aceh');
        $response->assertSee('Roti Bakar Coklat');

        // 2. Nama outlet
        $response->assertSee($outletA->nama_cabang);
        $response->assertSee($outletB->nama_cabang);

        // 3. Kategori (menu cafe)
        $response->assertSee('Minuman');
        $response->assertSee('Makanan');

        // 4. Satuan
        $response->assertSee('Pcs');
        $response->assertSee('Dus');

        // 5. Stok (diambil dari faktur barang): 50 Pcs untuk Kopi di Outlet A
        $response->assertSee('50 Pcs');
        $response->assertSee('10 Dus');
    }

    /**
     * Test filter berdasarkan outlet cabang pada halaman stok.
     */
    public function test_stok_filter_by_outlet(): void
    {
        $admin = $this->getAdmin();
        $outletA = TokoCabang::first();
        $outletB = TokoCabang::skip(1)->first();

        $kategori = MenuCafe::first();
        $satuan = SatuanBarang::first();

        $barangA = Barang::create(['nama_barang' => 'Barang Khusus Cabang A', 'menu_cafe_id' => $kategori->id]);
        $barangB = Barang::create(['nama_barang' => 'Barang Khusus Cabang B', 'menu_cafe_id' => $kategori->id]);

        $fakturA = FakturBarang::create([
            'nomor_faktur' => 'FKT-A-001',
            'toko_cabang_id' => $outletA->id,
            'tanggal_faktur' => '2026-10-19',
            'total_nominal' => 100000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $fakturA->id,
            'barang_id' => $barangA->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 15,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 150000,
        ]);

        $fakturB = FakturBarang::create([
            'nomor_faktur' => 'FKT-B-001',
            'toko_cabang_id' => $outletB->id,
            'tanggal_faktur' => '2026-10-19',
            'total_nominal' => 200000,
            'user_id' => $admin->id,
        ]);
        FakturBarangDetail::create([
            'faktur_barang_id' => $fakturB->id,
            'barang_id' => $barangB->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 25,
            'harga_awal' => 20000,
            'harga_jual' => 25000,
            'subtotal' => 500000,
        ]);

        // Filter hanya Outlet A
        $response = $this->actingAs($admin)->get(route('admin.cafe.stok', ['toko_cabang_id' => $outletA->id]));
        $response->assertOk();
        $response->assertSee('Barang Khusus Cabang A');
        $response->assertDontSee('Barang Khusus Cabang B');
        $response->assertSee('Rp 150.000'); // Total Asset filtered
    }

    /**
     * Test endpoint riwayat faktur barang via AJAX/JSON.
     */
    public function test_stok_riwayat_faktur_endpoint(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $kategori = MenuCafe::first();
        $satuan = SatuanBarang::first();
        $barang = Barang::create(['nama_barang' => 'Kopi Ulee Kareng', 'menu_cafe_id' => $kategori->id]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-RIWAYAT-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'pemasok' => 'Agen Banda Aceh',
            'total_nominal' => 250000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 25,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 250000,
        ]);

        $url = route('admin.cafe.stok.riwayat', [
            'toko_cabang_id' => $outlet->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
        ]);

        $response = $this->actingAs($admin)->getJson($url);
        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'total_masuk' => 25,
            'total_asset' => 250000,
        ]);
        $response->assertJsonFragment([
            'nomor_faktur' => 'FKT-RIWAYAT-001',
            'pemasok' => 'Agen Banda Aceh',
        ]);
    }
}
