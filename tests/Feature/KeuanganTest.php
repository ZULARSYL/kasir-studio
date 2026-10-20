<?php

namespace Tests\Feature;

use App\Models\JenisBiaya;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\TokoCabang;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KeuanganTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_keuangan_tester'],
            [
                'name' => 'Admin Keuangan Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_keuangan_tester'],
            [
                'name' => 'Kasir Keuangan Tester',
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
     * Test kasir tidak dapat mengakses menu keuangan pemasukan dan pengeluaran.
     */
    public function test_kasir_cannot_access_keuangan(): void
    {
        $kasir = $this->getKasir();

        $responsePemasukan = $this->actingAs($kasir)->get(route('admin.keuangan.pemasukan'));
        $responsePemasukan->assertForbidden();

        $responsePengeluaran = $this->actingAs($kasir)->get(route('admin.keuangan.pengeluaran'));
        $responsePengeluaran->assertForbidden();
    }

    /**
     * Test admin dapat melihat menu keuangan pada sidebar.
     */
    public function test_admin_sees_keuangan_menus_in_sidebar(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee(route('admin.keuangan.pemasukan'));
        $response->assertSee(route('admin.keuangan.pengeluaran'));
    }

    /**
     * Test CRUD Pemasukan Manual (di luar transaksi kasir).
     */
    public function test_pemasukan_crud_operations(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();
        $jenisBiaya = JenisBiaya::first();

        // 1. Index page
        $response = $this->actingAs($admin)->get(route('admin.keuangan.pemasukan'));
        $response->assertOk();
        $response->assertSee('Pemasukan Manual');
        $response->assertSee('Catat Pemasukan Baru');

        // 2. Store Pemasukan
        $storeData = [
            'tanggal' => '2026-10-20',
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => '750000',
            'keterangan' => 'Sponsorship event nonton bareng komunitas film',
        ];

        $storeResponse = $this->actingAs($admin)->post(route('admin.keuangan.pemasukan.store'), $storeData);
        $storeResponse->assertRedirect(route('admin.keuangan.pemasukan'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pemasukans', [
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => 750000,
            'keterangan' => 'Sponsorship event nonton bareng komunitas film',
            'user_id' => $admin->id,
        ]);

        $pemasukan = Pemasukan::first();
        $this->assertNotNull($pemasukan);
        $this->assertEquals(750000, $pemasukan->nominal);
        $this->assertEquals('2026-10-20', $pemasukan->tanggal->format('Y-m-d'));

        // 3. Validation failure
        $failResponse = $this->actingAs($admin)->post(route('admin.keuangan.pemasukan.store'), [
            'tanggal' => '',
            'toko_cabang_id' => 999999, // invalid cabang
            'jenis_biaya_id' => 999999, // invalid jenis biaya
            'nominal' => -500,
        ]);
        $failResponse->assertSessionHasErrors(['tanggal', 'toko_cabang_id', 'jenis_biaya_id', 'nominal']);

        // 4. Update Pemasukan
        $updateResponse = $this->actingAs($admin)->put(route('admin.keuangan.pemasukan.update', $pemasukan->id), [
            'tanggal' => '2026-10-21',
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => '900000',
            'keterangan' => 'Revisi sponsorship nobar',
        ]);
        $updateResponse->assertRedirect(route('admin.keuangan.pemasukan'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pemasukans', [
            'id' => $pemasukan->id,
            'nominal' => 900000,
            'keterangan' => 'Revisi sponsorship nobar',
        ]);

        // 5. Filter & Search
        $searchResponse = $this->actingAs($admin)->get(route('admin.keuangan.pemasukan', ['search' => 'sponsorship']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('900.000');

        $filterCabangResponse = $this->actingAs($admin)->get(route('admin.keuangan.pemasukan', ['toko_cabang_id' => $cabang->id]));
        $filterCabangResponse->assertOk();
        $filterCabangResponse->assertSee('900.000');

        // 6. Destroy Pemasukan
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.keuangan.pemasukan.destroy', $pemasukan->id));
        $deleteResponse->assertRedirect(route('admin.keuangan.pemasukan'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('pemasukans', ['id' => $pemasukan->id]);
    }

    /**
     * Test CRUD Pengeluaran Operasional Manual.
     */
    public function test_pengeluaran_crud_operations(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();
        $jenisBiaya = JenisBiaya::first();

        // 1. Index page
        $response = $this->actingAs($admin)->get(route('admin.keuangan.pengeluaran'));
        $response->assertOk();
        $response->assertSee('Pengeluaran Operasional');
        $response->assertSee('Catat Pengeluaran Baru');

        // 2. Store Pengeluaran
        $storeData = [
            'tanggal' => '2026-10-20',
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => '350000',
            'keterangan' => 'Pembayaran tagihan listrik PLN studio bulan Oktober',
        ];

        $storeResponse = $this->actingAs($admin)->post(route('admin.keuangan.pengeluaran.store'), $storeData);
        $storeResponse->assertRedirect(route('admin.keuangan.pengeluaran'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pengeluarans', [
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => 350000,
            'keterangan' => 'Pembayaran tagihan listrik PLN studio bulan Oktober',
            'user_id' => $admin->id,
        ]);

        $pengeluaran = Pengeluaran::first();
        $this->assertNotNull($pengeluaran);
        $this->assertEquals(350000, $pengeluaran->nominal);
        $this->assertEquals('2026-10-20', $pengeluaran->tanggal->format('Y-m-d'));

        // 3. Validation failure
        $failResponse = $this->actingAs($admin)->post(route('admin.keuangan.pengeluaran.store'), [
            'tanggal' => '',
            'toko_cabang_id' => 999999, // invalid cabang
            'jenis_biaya_id' => 999999, // invalid jenis biaya
            'nominal' => 0,
        ]);
        $failResponse->assertSessionHasErrors(['tanggal', 'toko_cabang_id', 'jenis_biaya_id', 'nominal']);

        // 4. Update Pengeluaran
        $updateResponse = $this->actingAs($admin)->put(route('admin.keuangan.pengeluaran.update', $pengeluaran->id), [
            'tanggal' => '2026-10-21',
            'toko_cabang_id' => $cabang->id,
            'jenis_biaya_id' => $jenisBiaya->id,
            'nominal' => '400000',
            'keterangan' => 'Penyesuaian tagihan listrik PLN plus denda',
        ]);
        $updateResponse->assertRedirect(route('admin.keuangan.pengeluaran'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pengeluarans', [
            'id' => $pengeluaran->id,
            'nominal' => 400000,
            'keterangan' => 'Penyesuaian tagihan listrik PLN plus denda',
        ]);

        // 5. Filter & Search
        $searchResponse = $this->actingAs($admin)->get(route('admin.keuangan.pengeluaran', ['search' => 'listrik PLN']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('400.000');

        $filterCabangResponse = $this->actingAs($admin)->get(route('admin.keuangan.pengeluaran', ['toko_cabang_id' => $cabang->id]));
        $filterCabangResponse->assertOk();
        $filterCabangResponse->assertSee('400.000');

        // 6. Destroy Pengeluaran
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.keuangan.pengeluaran.destroy', $pengeluaran->id));
        $deleteResponse->assertRedirect(route('admin.keuangan.pengeluaran'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('pengeluarans', ['id' => $pengeluaran->id]);
    }
}
