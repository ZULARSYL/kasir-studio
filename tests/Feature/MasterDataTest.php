<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TipePembayaran;
use App\Models\HargaPerdurasi;
use App\Models\TokoCabang;
use App\Models\Room;
use App\Models\Promo;
use App\Models\MenuCafePerjenis;
use App\Models\GenreFilm;
use App\Models\JenisBiaya;
use App\Models\Jabatan;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
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

    public function test_all_master_data_pages_load_successfully(): void
    {
        $this->seed(MasterDataSeeder::class);
        $admin = $this->getAdmin();

        $routes = [
            'admin.master.tipe-pembayaran.index',
            'admin.master.harga-perdurasi.index',
            'admin.master.toko-cabang.index',
            'admin.master.room.index',
            'admin.master.promo.index',
            'admin.master.menu-cafe-perjenis.index',
            'admin.master.genre-film.index',
            'admin.master.jenis-biaya.index',
            'admin.master.jabatan.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_tipe_pembayaran_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // 1. Store
        $postData = ['metode_pembayaran' => 'OVO Wallet'];
        $response = $this->actingAs($admin)->post(route('admin.master.tipe-pembayaran.store'), $postData);
        $response->assertRedirect(route('admin.master.tipe-pembayaran.index'));
        $this->assertDatabaseHas('tipe_pembayarans', ['metode_pembayaran' => 'OVO Wallet']);

        $item = TipePembayaran::where('metode_pembayaran', 'OVO Wallet')->first();

        // 2. Update
        $response = $this->actingAs($admin)->put(route('admin.master.tipe-pembayaran.update', $item->id), [
            'metode_pembayaran' => 'OVO Merchant Updated',
        ]);
        $response->assertRedirect(route('admin.master.tipe-pembayaran.index'));
        $this->assertDatabaseHas('tipe_pembayarans', ['metode_pembayaran' => 'OVO Merchant Updated']);

        // 3. Destroy
        $response = $this->actingAs($admin)->delete(route('admin.master.tipe-pembayaran.destroy', $item->id));
        $response->assertRedirect(route('admin.master.tipe-pembayaran.index'));
        $this->assertDatabaseMissing('tipe_pembayarans', ['id' => $item->id]);
    }

    public function test_harga_perdurasi_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // 1. Store
        $postData = [
            'nama' => 'Paket Hemat Siang',
            'durasi_menit' => 60,
            'kalkulasi_harga' => 50000,
        ];
        $response = $this->actingAs($admin)->post(route('admin.master.harga-perdurasi.store'), $postData);
        $response->assertRedirect(route('admin.master.harga-perdurasi.index'));
        $this->assertDatabaseHas('harga_perdurasi', [
            'nama' => 'Paket Hemat Siang',
            'durasi' => '60 Menit',
            'kalkulasi_harga' => 50000,
        ]);

        $item = HargaPerdurasi::where('nama', 'Paket Hemat Siang')->first();
        $this->assertSame('Rp 833,33', $item->harga_per_menit_format);
        $legacyItem = new HargaPerdurasi([
            'durasi' => '1 Jam (60 Menit)',
            'kalkulasi_harga' => 50000,
        ]);
        $this->assertSame(60, $legacyItem->durasi_menit);
        $this->assertSame('Rp 833,33', $legacyItem->harga_per_menit_format);
        $this->actingAs($admin)
            ->get(route('admin.master.harga-perdurasi.index'))
            ->assertOk()
            ->assertSee('HARGA PER MENIT')
            ->assertSee('Rp 833,33');

        // 2. Update
        $response = $this->actingAs($admin)->put(route('admin.master.harga-perdurasi.update', $item->id), [
            'nama' => 'Paket Hemat Sore',
            'durasi_menit' => 60,
            'kalkulasi_harga' => 85000,
        ]);
        $response->assertRedirect(route('admin.master.harga-perdurasi.index'));
        $this->assertDatabaseHas('harga_perdurasi', [
            'nama' => 'Paket Hemat Sore',
            'durasi' => '60 Menit',
            'kalkulasi_harga' => 85000,
        ]);

        // 3. Destroy
        $response = $this->actingAs($admin)->delete(route('admin.master.harga-perdurasi.destroy', $item->id));
        $response->assertRedirect(route('admin.master.harga-perdurasi.index'));
        $this->assertDatabaseMissing('harga_perdurasi', ['id' => $item->id]);
    }

    public function test_room_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // Create branch first
        $cabang = TokoCabang::create([
            'nama_cabang' => 'Cabang Test',
            'alamat' => 'Jl. Test No. 1',
            'kontak' => '081234567890',
        ]);

        // 1. Store
        $postData = [
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Room 10',
            'tipe' => 'VIP',
            'kapasitas' => 5,
        ];
        $response = $this->actingAs($admin)->post(route('admin.master.room.store'), $postData);
        $response->assertRedirect(route('admin.master.room.index'));
        $this->assertDatabaseHas('rooms', ['nama' => 'Studio Room 10']);

        $room = Room::where('nama', 'Studio Room 10')->first();

        // 2. Update
        $response = $this->actingAs($admin)->put(route('admin.master.room.update', $room->id), [
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Room 10 Ultra',
            'tipe' => 'VVIP',
            'kapasitas' => 6,
        ]);
        $response->assertRedirect(route('admin.master.room.index'));
        $this->assertDatabaseHas('rooms', ['nama' => 'Studio Room 10 Ultra']);

        // 3. Destroy
        $response = $this->actingAs($admin)->delete(route('admin.master.room.destroy', $room->id));
        $response->assertRedirect(route('admin.master.room.index'));
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_promo_crud_operations(): void
    {
        $admin = $this->getAdmin();

        // 1. Store
        $postData = [
            'kode_voucher' => 'DISC99',
            'nama_promo' => 'Diskon 99 Persen',
            'jenis_diskon' => 'potongan',
            'jumlah_diskon' => 50000,
            'periode_mulai' => '2026-10-01',
            'periode_selesai' => '2026-12-31',
            'status' => 'aktif',
        ];
        $response = $this->actingAs($admin)->post(route('admin.master.promo.store'), $postData);
        $response->assertRedirect(route('admin.master.promo.index'));
        $this->assertDatabaseHas('promos', ['kode_voucher' => 'DISC99']);
        $this->assertDatabaseHas('promos', [
            'kode_voucher' => 'DISC99',
            'nilai' => 'Rp 50.000',
            'periode_berlaku' => '2026-10-01 - 2026-12-31',
        ]);
        $this->actingAs($admin)
            ->get(route('admin.master.promo.index'))
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertSee('Potongan Harga (Rp)')
            ->assertDontSee('name="jumlah_diskon" class="form-control rounded-3 js-jumlah-diskon" min="0.01" step="any" placeholder="Pilih jenis diskon terlebih dahulu" disabled', false);

        $this->actingAs($admin)
            ->from(route('admin.master.promo.index'))
            ->post(route('admin.master.promo.store'), array_merge($postData, [
                'kode_voucher' => 'DISCINVALID',
                'jenis_diskon' => 'persentase',
                'jumlah_diskon' => 101,
            ]))
            ->assertRedirect(route('admin.master.promo.index'))
            ->assertSessionHasErrors('jumlah_diskon');
        $this->assertDatabaseMissing('promos', ['kode_voucher' => 'DISCINVALID']);
        $this->actingAs($admin)
            ->get(route('admin.master.promo.index'))
            ->assertOk()
            ->assertSee('Promo belum dapat disimpan')
            ->assertSee('bootstrap.Modal.getOrCreateInstance');

        $promo = Promo::where('kode_voucher', 'DISC99')->first();

        // 2. Update
        $response = $this->actingAs($admin)->put(route('admin.master.promo.update', $promo->id), [
            'kode_voucher' => 'DISC99',
            'nama_promo' => 'Diskon 99 Persen Super',
            'jenis_diskon' => 'persentase',
            'jumlah_diskon' => 75,
            'periode_mulai' => '2027-01-01',
            'periode_selesai' => '2027-12-31',
            'status' => 'nonaktif',
        ]);
        $response->assertRedirect(route('admin.master.promo.index'));
        $this->assertDatabaseHas('promos', [
            'nama_promo' => 'Diskon 99 Persen Super',
            'nilai' => '75%',
            'periode_berlaku' => '2027-01-01 - 2027-12-31',
        ]);

        // 3. Destroy
        $response = $this->actingAs($admin)->delete(route('admin.master.promo.destroy', $promo->id));
        $response->assertRedirect(route('admin.master.promo.index'));
        $this->assertDatabaseMissing('promos', ['id' => $promo->id]);
    }

    public function test_cafe_genre_jenis_biaya_jabatan_crud(): void
    {
        $admin = $this->getAdmin();

        // Cafe (Makanan, Minuman, Snack)
        $resp = $this->actingAs($admin)->post(route('admin.master.menu-cafe-perjenis.store'), ['nama' => 'Dessert']);
        $resp->assertRedirect(route('admin.master.menu-cafe-perjenis.index'));
        $this->assertDatabaseHas('menu_cafe_jenis', ['nama' => 'Dessert']);

        // Genre Film
        $resp = $this->actingAs($admin)->post(route('admin.master.genre-film.store'), ['nama_genre' => 'Cyberpunk']);
        $resp->assertRedirect(route('admin.master.genre-film.index'));
        $this->assertDatabaseHas('genre_films', ['nama_genre' => 'Cyberpunk']);

        // Jenis Biaya (kategori)
        $resp = $this->actingAs($admin)->post(route('admin.master.jenis-biaya.store'), ['kategori' => 'Biaya Logistik']);
        $resp->assertRedirect(route('admin.master.jenis-biaya.index'));
        $this->assertDatabaseHas('jenis_biayas', ['kategori' => 'Biaya Logistik']);

        // Jabatan (nama_jabatan)
        $resp = $this->actingAs($admin)->post(route('admin.master.jabatan.store'), ['nama_jabatan' => 'Sound Engineer']);
        $resp->assertRedirect(route('admin.master.jabatan.index'));
        $this->assertDatabaseHas('jabatans', ['nama_jabatan' => 'Sound Engineer']);
    }
}
