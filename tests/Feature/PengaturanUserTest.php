<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\TokoCabang;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengaturanUserTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_pengaturan_tester'],
            [
                'name' => 'Admin Pengaturan Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_pengaturan_tester'],
            [
                'name' => 'Kasir Pengaturan Tester',
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
     * Test kasir tidak dapat mengakses menu pengaturan user.
     */
    public function test_kasir_cannot_access_pengaturan_user(): void
    {
        $kasir = $this->getKasir();

        $response = $this->actingAs($kasir)->get(route('admin.pengaturan.user.index'));
        $response->assertForbidden();
    }

    /**
     * Test admin dapat melihat submenu User di menu Pengaturan pada sidebar.
     */
    public function test_admin_sees_user_submenu_in_sidebar(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Pengaturan');
        $response->assertSee(route('admin.pengaturan.user.index'));
    }

    /**
     * Test siklus CRUD lengkap pengaturan user dengan penempatan outlet, pegawai, dan jabatan sebagai role.
     */
    public function test_user_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // 1. Siapkan data master terkait
        $cabang = TokoCabang::create([
            'nama_cabang' => 'Pressent Studio Pekanbaru Kota',
            'alamat' => 'Jl. Sudirman No. 100',
            'kontak' => '081234567890',
        ]);

        $jabatanKasir = Jabatan::create([
            'nama_jabatan' => 'Kasir Front Office',
        ]);

        $pegawai = Pegawai::create([
            'nama_pegawai' => 'Dimas Anggara',
            'jabatan_id' => $jabatanKasir->id,
            'nomor_hp' => '081299991111',
            'alamat' => 'Jl. Paus No. 25, Marpoyan Damai',
        ]);

        // 2. Index Page
        $response = $this->actingAs($admin)->get(route('admin.pengaturan.user.index'));
        $response->assertOk();
        $response->assertSee('Submenu Pengaturan User');
        $response->assertSee('Tambah User Baru');

        // 3. Store User Baru
        $storeData = [
            'pegawai_id' => $pegawai->id,
            'toko_cabang_id' => $cabang->id,
            'jabatan_id' => $jabatanKasir->id,
            'username' => 'dimas_kasir',
            'password' => 'secret123',
        ];

        $storeResponse = $this->actingAs($admin)->post(route('admin.pengaturan.user.store'), $storeData);
        $storeResponse->assertRedirect(route('admin.pengaturan.user.index'));
        $storeResponse->assertSessionHas('success');

        // Pastikan tersimpan dengan role 'kasir' yang diambil dari nama jabatan 'Kasir Front Office'
        $this->assertDatabaseHas('users', [
            'name' => 'Dimas Anggara',
            'username' => 'dimas_kasir',
            'pegawai_id' => $pegawai->id,
            'toko_cabang_id' => $cabang->id,
            'jabatan_id' => $jabatanKasir->id,
            'role' => 'kasir',
        ]);

        $newUser = User::where('username', 'dimas_kasir')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(Hash::check('secret123', $newUser->password));
        $this->assertSame('Pressent Studio Pekanbaru Kota', $newUser->tokoCabang->nama_cabang);
        $this->assertSame('Dimas Anggara', $newUser->pegawai->nama_pegawai);
        $this->assertSame('Kasir Front Office', $newUser->jabatan->nama_jabatan);

        // 4. Proteksi Anti-DevTools Tampering
        // Pegawai tidak valid
        $this->actingAs($admin)
            ->post(route('admin.pengaturan.user.store'), array_merge($storeData, [
                'pegawai_id' => 999999,
                'username' => 'fake_user_1',
            ]))
            ->assertSessionHasErrors('pegawai_id');

        // Jabatan tidak valid
        $this->actingAs($admin)
            ->post(route('admin.pengaturan.user.store'), array_merge($storeData, [
                'jabatan_id' => 999999,
                'username' => 'fake_user_2',
            ]))
            ->assertSessionHasErrors('jabatan_id');

        // Outlet tidak valid
        $this->actingAs($admin)
            ->post(route('admin.pengaturan.user.store'), array_merge($storeData, [
                'toko_cabang_id' => 999999,
                'username' => 'fake_user_3',
            ]))
            ->assertSessionHasErrors('toko_cabang_id');

        // Username duplikat
        $this->actingAs($admin)
            ->post(route('admin.pengaturan.user.store'), $storeData)
            ->assertSessionHasErrors('username');

        // 5. Update User (Promosi jadi Admin Cabang)
        $jabatanAdmin = Jabatan::create(['nama_jabatan' => 'Super Administrator']);
        $cabang2 = TokoCabang::create([
            'nama_cabang' => 'Pressent Studio Panam',
            'alamat' => 'Jl. HR Soebrantas',
            'kontak' => '081288887777',
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.pengaturan.user.update', $newUser->id), [
            'pegawai_id' => $pegawai->id,
            'toko_cabang_id' => $cabang2->id,
            'jabatan_id' => $jabatanAdmin->id,
            'username' => 'dimas_admin',
            'password' => '', // Kosongkan password agar tidak berubah
        ]);

        $updateResponse->assertRedirect(route('admin.pengaturan.user.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'username' => 'dimas_admin',
            'toko_cabang_id' => $cabang2->id,
            'jabatan_id' => $jabatanAdmin->id,
            'role' => 'admin',
        ]);

        // Password tetap 'secret123'
        $updatedUser = User::find($newUser->id);
        $this->assertTrue(Hash::check('secret123', $updatedUser->password));

        // 6. Filter & Pencarian User
        $searchResponse = $this->actingAs($admin)->get(route('admin.pengaturan.user.index', ['search' => 'dimas_admin']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('dimas_admin');
        $searchResponse->assertSee('Pressent Studio Panam');

        // 7. Proteksi Self-Deletion (Admin tidak dapat menghapus akun sendiri)
        $selfDeleteResponse = $this->actingAs($admin)->delete(route('admin.pengaturan.user.destroy', $admin->id));
        $selfDeleteResponse->assertRedirect(route('admin.pengaturan.user.index'));
        $selfDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // 8. Destroy User
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.pengaturan.user.destroy', $newUser->id));
        $deleteResponse->assertRedirect(route('admin.pengaturan.user.index'));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    /**
     * Test akun user baru yang dibuat dapat melakukan login ke sistem.
     */
    public function test_newly_created_user_can_login(): void
    {
        $admin = $this->getAdmin();

        $jabatan = Jabatan::create(['nama_jabatan' => 'Kasir Studio']);
        $pegawai = Pegawai::create([
            'nama_pegawai' => 'Siti Nurhaliza',
            'jabatan_id' => $jabatan->id,
            'nomor_hp' => '081233334444',
            'alamat' => 'Pekanbaru',
        ]);

        $this->actingAs($admin)->post(route('admin.pengaturan.user.store'), [
            'pegawai_id' => $pegawai->id,
            'jabatan_id' => $jabatan->id,
            'username' => 'siti_kasir',
            'password' => 'kasirpass123',
        ]);

        // Logout admin dan login dengan user baru
        $this->post(route('logout'));

        $loginResponse = $this->post(route('login.authenticate'), [
            'username' => 'siti_kasir',
            'password' => 'kasirpass123',
        ]);

        $loginResponse->assertRedirect(route('kasir.index'));
        $this->assertAuthenticated();
        $this->assertSame('siti_kasir', auth()->user()->username);
        $this->assertSame('kasir', auth()->user()->role);
    }
}
