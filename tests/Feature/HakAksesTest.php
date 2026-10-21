<?php

namespace Tests\Feature;

use App\Models\HakAkses;
use App\Models\Jabatan;
use App\Models\TokoCabang;
use App\Models\User;
use App\Services\HakAksesService;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HakAksesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    protected function getAdmin(): User
    {
        return User::firstOrCreate(
            ['username' => 'admin_hak_akses_tester'],
            [
                'name' => 'Admin Hak Akses Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir(?int $cabangId = null): User
    {
        $cabang = $cabangId ? TokoCabang::find($cabangId) : TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'kasir_hak_akses_tester'],
            [
                'name' => 'Kasir Hak Akses Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test tamu tanpa autentikasi dialihkan ke halaman login saat membuka hak akses.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.pengaturan.hak-akses'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin dapat mengakses halaman pengaturan submenu hak akses.
     */
    public function test_admin_can_access_hak_akses_settings_page(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.pengaturan.hak-akses'));
        $response->assertOk();
        $response->assertSee('Pengaturan Hak Akses User');
        $response->assertSee('Role &amp; Jabatan', false);
        $response->assertSee('Berdasarkan Role');
        $response->assertSee('Berdasarkan Jabatan');
        $response->assertSee('Administrator');
        $response->assertSee('Kasir POS');
        $response->assertSee('Centang Semua');
        $response->assertSee('Hapus Semua');
    }

    /**
     * Test kasir dilarang (403 Forbidden) membuka halaman pengaturan hak akses.
     */
    public function test_kasir_cannot_access_hak_akses_settings_page(): void
    {
        $kasir = $this->getKasir();

        $response = $this->actingAs($kasir)->get(route('admin.pengaturan.hak-akses'));
        $response->assertForbidden();
    }

    /**
     * Test admin dapat menyimpan hak akses per Role sistem (contoh: memberikan hak Laporan Penjualan ke Kasir).
     */
    public function test_admin_can_update_permissions_for_kasir_role(): void
    {
        $admin = $this->getAdmin();
        $kasir = $this->getKasir();

        // Awalnya kasir tidak memiliki izin ke admin.laporan.penjualan
        $this->actingAs($kasir)->get(route('admin.laporan.penjualan'))->assertForbidden();

        // Admin memperbarui izin role kasir dengan menambahkan laporan.penjualan
        $response = $this->actingAs($admin)->post(route('admin.pengaturan.hak-akses.update'), [
            'tipe' => 'role',
            'role' => 'kasir',
            'menus' => ['main', 'kasir_dashboard', 'kasir_pos', 'laporan', 'laporan.penjualan'],
        ]);

        $response->assertRedirect(route('admin.pengaturan.hak-akses', ['tipe' => 'role', 'role' => 'kasir']));
        $response->assertSessionHas('success');

        // Pastikan tersimpan di tabel hak_akses
        $this->assertDatabaseHas('hak_akses', [
            'target_key' => 'role:kasir',
            'tipe' => 'role',
            'role' => 'kasir',
        ]);

        // Verifikasi service canAccess
        $this->assertTrue(HakAksesService::canAccess($kasir, 'laporan.penjualan'));
        $this->assertFalse(HakAksesService::canAccess($kasir, 'laporan.pembelian'));

        // Kasir kini dapat membuka route admin.laporan.penjualan
        $kasirResponse = $this->actingAs($kasir)->get(route('admin.laporan.penjualan'));
        $kasirResponse->assertOk();

        // Namun kasir tetap dilarang mengakses laporan lain yang tidak dicentang (misal: laporan pembelian)
        $this->actingAs($kasir)->get(route('admin.laporan.pembelian'))->assertForbidden();
    }

    /**
     * Test safeguard admin mencegah penghapusan hak akses dan dashboard untuk role Administrator.
     */
    public function test_admin_safeguard_prevents_removing_hak_akses_and_dashboard_for_admin(): void
    {
        $admin = $this->getAdmin();

        // Admin mencoba menghapus semua menu untuk role admin
        $response = $this->actingAs($admin)->post(route('admin.pengaturan.hak-akses.update'), [
            'tipe' => 'role',
            'role' => 'admin',
            'menus' => [],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $record = HakAkses::where('target_key', 'role:admin')->first();
        $this->assertNotNull($record);

        // Safeguard harus memastikan pengaturan.hak-akses dan dashboard tetap ada
        $this->assertContains('pengaturan.hak-akses', $record->menus);
        $this->assertContains('dashboard', $record->menus);

        $this->assertTrue(HakAksesService::canAccess($admin, 'pengaturan.hak-akses'));
        $this->assertTrue(HakAksesService::canAccess($admin, 'dashboard'));
    }

    /**
     * Test admin dapat mengkonfigurasi hak akses berdasarkan Jabatan Pegawai.
     */
    public function test_admin_can_configure_permissions_per_jabatan(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();

        // Buat master jabatan baru
        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Store Manager Senior',
        ]);

        // Buat user dengan jabatan tersebut
        $userJabatan = User::create([
            'name' => 'Manager Hendra',
            'username' => 'manager_hendra',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'jabatan_id' => $jabatan->id,
            'toko_cabang_id' => $cabang->id,
        ]);

        // Simpan hak akses khusus untuk jabatan ini (hanya laporan.stok dan cafe.stok)
        $response = $this->actingAs($admin)->post(route('admin.pengaturan.hak-akses.update'), [
            'tipe' => 'jabatan',
            'jabatan_id' => $jabatan->id,
            'menus' => ['cafe', 'cafe.stok', 'laporan', 'laporan.stok'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('hak_akses', [
            'target_key' => "jabatan:{$jabatan->id}",
            'tipe' => 'jabatan',
            'jabatan_id' => $jabatan->id,
        ]);

        // User dengan jabatan ini dapat membuka cafe.stok & laporan.stok
        $this->assertTrue(HakAksesService::canAccess($userJabatan, 'cafe.stok'));
        $this->assertTrue(HakAksesService::canAccess($userJabatan, 'laporan.stok'));

        // User dengan jabatan ini tidak dapat membuka menu yang tidak dicentang (misal: film.index)
        $this->assertFalse(HakAksesService::canAccess($userJabatan, 'film.index'));
        $this->actingAs($userJabatan)->get(route('admin.film.index'))->assertForbidden();
    }

    /**
     * Test sidebar hanya merender menu yang memiliki hak akses.
     */
    public function test_sidebar_displays_only_allowed_menus(): void
    {
        $cabang = TokoCabang::first();
        $kasir = $this->getKasir($cabang->id);

        // Sidebar kasir default hanya menampilkan Dashboard kasir dan POS
        $defaultResponse = $this->actingAs($kasir)->get(route('kasir.dashboard'));
        $defaultResponse->assertOk();
        $defaultResponse->assertSee('Main Kasir');
        $defaultResponse->assertSee('Menu Kasir (POS)');
        $defaultResponse->assertDontSee('Layanan Studio & Cafe');
        $defaultResponse->assertDontSee('Master Data');

        // Berikan izin Laporan Penjualan pada role kasir
        HakAksesService::savePermissions('role', ['main', 'kasir_dashboard', 'kasir_pos', 'laporan', 'laporan.penjualan'], 'kasir');

        // Sekarang sidebar kasir menampilkan accordion Laporan dan submenu Laporan Penjualan
        $updatedResponse = $this->actingAs($kasir)->get(route('admin.laporan.penjualan'));
        $updatedResponse->assertOk();
        $updatedResponse->assertSee('Laporan Penjualan');
        $updatedResponse->assertDontSee('Laporan Pembelian');
    }
}
