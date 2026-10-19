<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FakturBarangTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    /**
     * Test halaman daftar riwayat faktur barang dapat diakses.
     */
    public function test_faktur_barang_index_page_loads_successfully(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.cafe.faktur.index'));
        $response->assertOk();
        $response->assertSee('Faktur Barang (Pembelian / Grosir)', false);
    }

    /**
     * Test halaman input faktur baru memuat data master outlet, barang, dan satuan.
     */
    public function test_faktur_barang_create_page_loads_with_master_data(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.cafe.faktur.create'));
        $response->assertOk();
        $response->assertSee('Pilih Outlet Cabang');
        $response->assertSee('Tambah Barang dari Master');
    }

    /**
     * Test input faktur barang pembelian/grosir berhasil menyimpan header dan items.
     */
    public function test_store_faktur_barang_with_items_and_prices_successfully(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $satuanDus = SatuanBarang::where('nama_satuan', 'Dus')->first();
        $satuanPcs = SatuanBarang::where('nama_satuan', 'Pcs')->first();
        $barang1 = Barang::first();
        $barang2 = Barang::skip(1)->first();

        $postData = [
            'nomor_faktur' => 'FKT-TEST-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'pemasok' => 'Grosir Berkah Pekanbaru',
            'catatan' => 'Pembelian grosir bulanan',
            'items' => [
                [
                    'barang_id' => $barang1->id,
                    'satuan_barang_id' => $satuanDus->id,
                    'qty' => 5,
                    'harga_awal' => 100000, // 5 * 100.000 = 500.000
                    'harga_jual' => 120000,
                ],
                [
                    'barang_id' => $barang2->id,
                    'satuan_barang_id' => $satuanPcs->id,
                    'qty' => 20,
                    'harga_awal' => 15000,  // 20 * 15.000 = 300.000
                    'harga_jual' => 20000,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cafe.faktur.store'), $postData);
        $response->assertRedirect(route('admin.cafe.faktur.index'));
        $response->assertSessionHas('success');

        // Pastikan total nominal terhitung benar: 500.000 + 300.000 = 800.000
        $this->assertDatabaseHas('faktur_barangs', [
            'nomor_faktur' => 'FKT-TEST-001',
            'toko_cabang_id' => $outlet->id,
            'total_nominal' => 800000,
        ]);

        $this->assertDatabaseHas('faktur_barang_details', [
            'barang_id' => $barang1->id,
            'satuan_barang_id' => $satuanDus->id,
            'qty' => 5,
            'harga_awal' => 100000,
            'harga_jual' => 120000,
            'subtotal' => 500000,
        ]);

        $this->assertDatabaseHas('faktur_barang_details', [
            'barang_id' => $barang2->id,
            'satuan_barang_id' => $satuanPcs->id,
            'qty' => 20,
            'harga_awal' => 15000,
            'harga_jual' => 20000,
            'subtotal' => 300000,
        ]);
    }

    /**
     * Test validasi: Faktur tanpa barang ditolak.
     */
    public function test_store_faktur_rejects_empty_items(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();

        $postData = [
            'nomor_faktur' => 'FKT-EMPTY-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'items' => [],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cafe.faktur.store'), $postData);
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('faktur_barangs', ['nomor_faktur' => 'FKT-EMPTY-001']);
    }

    /**
     * Test proteksi Inspect Element DevTools pada dropdown outlet cabang.
     */
    public function test_store_faktur_protects_against_outlet_tampering(): void
    {
        $admin = $this->getAdmin();
        $satuan = SatuanBarang::first();
        $barang = Barang::first();

        $postData = [
            'nomor_faktur' => 'FKT-TAMPER-OUTLET',
            'toko_cabang_id' => 999999, // ID tidak terdaftar (dimodifikasi lewat DevTools)
            'tanggal_faktur' => '2026-10-19',
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'satuan_barang_id' => $satuan->id,
                    'qty' => 10,
                    'harga_awal' => 5000,
                    'harga_jual' => 8000,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cafe.faktur.store'), $postData);
        $response->assertSessionHasErrors('toko_cabang_id');
        $this->assertDatabaseMissing('faktur_barangs', ['nomor_faktur' => 'FKT-TAMPER-OUTLET']);
    }

    /**
     * Test proteksi Inspect Element DevTools pada dropdown satuan barang.
     */
    public function test_store_faktur_protects_against_satuan_tampering(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $barang = Barang::first();

        $postData = [
            'nomor_faktur' => 'FKT-TAMPER-SATUAN',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'satuan_barang_id' => 888888, // Satuan palsu lewat DevTools
                    'qty' => 10,
                    'harga_awal' => 5000,
                    'harga_jual' => 8000,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.cafe.faktur.store'), $postData);
        $response->assertSessionHasErrors('items.0.satuan_barang_id');
        $this->assertDatabaseMissing('faktur_barangs', ['nomor_faktur' => 'FKT-TAMPER-SATUAN']);
    }

    /**
     * Test melihat detail rincian faktur.
     */
    public function test_show_faktur_page(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $satuan = SatuanBarang::first();
        $barang = Barang::first();

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-SHOW-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'total_nominal' => 50000,
        ]);

        $faktur->details()->create([
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 5,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cafe.faktur.show', $faktur->id));
        $response->assertOk();
        $response->assertSee('FKT-SHOW-001');
        $response->assertSee($barang->nama_barang);
    }

    /**
     * Test menghapus faktur dan cascade detail item.
     */
    public function test_destroy_faktur_cascades_details(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $satuan = SatuanBarang::first();
        $barang = Barang::first();

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-DELETE-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-19',
            'total_nominal' => 20000,
        ]);

        $detail = $faktur->details()->create([
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 2,
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 20000,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.cafe.faktur.destroy', $faktur->id));
        $response->assertRedirect(route('admin.cafe.faktur.index'));

        $this->assertDatabaseMissing('faktur_barangs', ['id' => $faktur->id]);
        $this->assertDatabaseMissing('faktur_barang_details', ['id' => $detail->id]);
    }
}
