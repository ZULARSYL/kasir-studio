<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KepegawaianTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_kepegawaian_tester'],
            [
                'name' => 'Admin Kepegawaian Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_kepegawaian_tester'],
            [
                'name' => 'Kasir Kepegawaian Tester',
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
     * Test kasir tidak dapat mengakses menu kepegawaian.
     */
    public function test_kasir_cannot_access_kepegawaian(): void
    {
        $kasir = $this->getKasir();

        $responseJabatan = $this->actingAs($kasir)->get(route('admin.kepegawaian.jabatan.index'));
        $responseJabatan->assertForbidden();

        $responsePegawai = $this->actingAs($kasir)->get(route('admin.kepegawaian.pegawai.index'));
        $responsePegawai->assertForbidden();
    }

    /**
     * Test admin dapat melihat menu submenu jabatan & pegawai pada sidebar.
     */
    public function test_admin_sees_kepegawaian_submenus_in_sidebar(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee(route('admin.kepegawaian.jabatan.index'));
        $response->assertSee(route('admin.kepegawaian.pegawai.index'));
    }

    /**
     * Test CRUD submenu Jabatan pada modul Kepegawaian.
     */
    public function test_jabatan_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // 1. Index page
        $response = $this->actingAs($admin)->get(route('admin.kepegawaian.jabatan.index'));
        $response->assertOk();
        $response->assertSee('Jabatan Pegawai');
        $response->assertSee('Tambah Jabatan Baru');

        // 2. Store Jabatan
        $storeResponse = $this->actingAs($admin)->post(route('admin.kepegawaian.jabatan.store'), [
            'nama_jabatan' => 'Floor Supervisor',
        ]);
        $storeResponse->assertRedirect(route('admin.kepegawaian.jabatan.index'));
        $this->assertDatabaseHas('jabatans', ['nama_jabatan' => 'Floor Supervisor']);

        $jabatan = Jabatan::where('nama_jabatan', 'Floor Supervisor')->first();

        // Validasi duplikat
        $dupResponse = $this->actingAs($admin)->post(route('admin.kepegawaian.jabatan.store'), [
            'nama_jabatan' => 'Floor Supervisor',
        ]);
        $dupResponse->assertSessionHasErrors('nama_jabatan');

        // 3. Update Jabatan
        $updateResponse = $this->actingAs($admin)->put(route('admin.kepegawaian.jabatan.update', $jabatan->id), [
            'nama_jabatan' => 'Senior Floor Supervisor',
        ]);
        $updateResponse->assertRedirect(route('admin.kepegawaian.jabatan.index'));
        $this->assertDatabaseHas('jabatans', ['nama_jabatan' => 'Senior Floor Supervisor']);

        // 4. Destroy Jabatan
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.kepegawaian.jabatan.destroy', $jabatan->id));
        $deleteResponse->assertRedirect(route('admin.kepegawaian.jabatan.index'));
        $this->assertDatabaseMissing('jabatans', ['id' => $jabatan->id]);
    }

    /**
     * Test CRUD submenu Pegawai pada modul Kepegawaian beserta relasi jabatannya.
     */
    public function test_pegawai_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // Siapkan master jabatan
        $jabatanKasir = Jabatan::create(['nama_jabatan' => 'Kasir Utama']);
        $jabatanBarista = Jabatan::create(['nama_jabatan' => 'Barista Cafe']);

        // 1. Index page
        $response = $this->actingAs($admin)->get(route('admin.kepegawaian.pegawai.index'));
        $response->assertOk();
        $response->assertSee('Pegawai');
        $response->assertSee('Tambah Pegawai Baru');
        $response->assertSee('Kasir Utama');

        // 2. Store Pegawai
        $storeData = [
            'nama_pegawai' => 'Rian Hidayat',
            'jabatan_id' => $jabatanKasir->id,
            'nomor_hp' => '081234567890',
            'alamat' => 'Jl. HR Soebrantas No. 88, Panam, Pekanbaru',
        ];

        $storeResponse = $this->actingAs($admin)->post(route('admin.kepegawaian.pegawai.store'), $storeData);
        $storeResponse->assertRedirect(route('admin.kepegawaian.pegawai.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pegawais', [
            'nama_pegawai' => 'Rian Hidayat',
            'jabatan_id' => $jabatanKasir->id,
            'nomor_hp' => '081234567890',
            'alamat' => 'Jl. HR Soebrantas No. 88, Panam, Pekanbaru',
        ]);

        $pegawai = Pegawai::where('nama_pegawai', 'Rian Hidayat')->first();
        $this->assertNotNull($pegawai);
        $this->assertSame('Kasir Utama', $pegawai->jabatan->nama_jabatan);

        // 3. DevTools Tampering Protection (Input jabatan yang tidak ada)
        $tamperedResponse = $this->actingAs($admin)->post(route('admin.kepegawaian.pegawai.store'), [
            'nama_pegawai' => 'Hacker Tamper',
            'jabatan_id' => 999999, // ID tidak ada di tabel jabatans
            'nomor_hp' => '0899999999',
            'alamat' => 'Fake Address',
        ]);
        $tamperedResponse->assertSessionHasErrors('jabatan_id');
        $this->assertDatabaseMissing('pegawais', ['nama_pegawai' => 'Hacker Tamper']);

        // 4. Update Pegawai (Pindah ke jabatan Barista Cafe dan update no HP)
        $updateResponse = $this->actingAs($admin)->put(route('admin.kepegawaian.pegawai.update', $pegawai->id), [
            'nama_pegawai' => 'Rian Hidayat, S.Kom',
            'jabatan_id' => $jabatanBarista->id,
            'nomor_hp' => '081299998888',
            'alamat' => 'Jl. Riau No. 12, Senapelan, Pekanbaru',
        ]);
        $updateResponse->assertRedirect(route('admin.kepegawaian.pegawai.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('pegawais', [
            'id' => $pegawai->id,
            'nama_pegawai' => 'Rian Hidayat, S.Kom',
            'jabatan_id' => $jabatanBarista->id,
            'nomor_hp' => '081299998888',
        ]);

        // 5. Filter Pencarian Pegawai
        $searchResponse = $this->actingAs($admin)->get(route('admin.kepegawaian.pegawai.index', ['search' => 'Senapelan']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Rian Hidayat, S.Kom');

        // Filter by Jabatan
        $filterJabatanResponse = $this->actingAs($admin)->get(route('admin.kepegawaian.pegawai.index', ['jabatan_id' => $jabatanBarista->id]));
        $filterJabatanResponse->assertOk();
        $filterJabatanResponse->assertSee('Rian Hidayat, S.Kom');

        // 6. Destroy Pegawai
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.kepegawaian.pegawai.destroy', $pegawai->id));
        $deleteResponse->assertRedirect(route('admin.kepegawaian.pegawai.index'));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('pegawais', ['id' => $pegawai->id]);
    }
}
