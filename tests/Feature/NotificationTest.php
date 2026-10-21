<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\JenisBiaya;
use App\Models\KasirShift;
use App\Models\MenuCafe;
use App\Models\Pengeluaran;
use App\Models\Promo;
use App\Models\Room;
use App\Models\SatuanBarang;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationTest extends TestCase
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
            ['username' => 'admin_notif_tester'],
            [
                'name' => 'Admin Notif Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir(?int $cabangId = null): User
    {
        $cabang = $cabangId ? TokoCabang::find($cabangId) : TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'kasir_notif_tester'],
            [
                'name' => 'Kasir Notif Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test tamu tanpa login tidak dapat mengakses notifikasi.
     */
    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get(route('notifikasi.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test notifikasi untuk role ADMIN hanya menampilkan hal urgent hari ini: Buka Kasir, Tutup Kasir, dan Stok (< 10).
     */
    public function test_admin_sees_admin_specific_notifications_and_not_kasir_prompts(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();

        // 1. Kasir buka shift hari ini (Fitur Buka Kasir)
        $kasirBuka = User::create([
            'name' => 'Kasir Budi Open',
            'username' => 'kasir_budi_open',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'toko_cabang_id' => $cabang->id,
        ]);

        KasirShift::create([
            'user_id' => $kasirBuka->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 250000,
            'status' => 'buka',
            'waktu_buka' => now()->subHours(2),
        ]);

        // 2. Kasir tutup shift hari ini (Fitur Tutup Kasir)
        $kasirTutup = User::create([
            'name' => 'Kasir Ahmad Close',
            'username' => 'kasir_ahmad_close',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'toko_cabang_id' => $cabang->id,
        ]);

        KasirShift::create([
            'user_id' => $kasirTutup->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'modal_akhir' => 1200000,
            'total_penjualan' => 1000000,
            'status' => 'tutup',
            'waktu_buka' => now()->subHours(8),
            'waktu_tutup' => now()->subMinutes(15),
        ]);

        // 3. Stok cafe menipis kurang dari 10 (< 10)
        $kategori = MenuCafe::first();
        $satuan = SatuanBarang::first();
        $barangKritis = Barang::create([
            'nama_barang' => 'Kentang Goreng Keju',
            'menu_cafe_id' => $kategori->id,
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-NOTIF-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => now()->toDateString(),
            'total_nominal' => 350000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barangKritis->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 4, // Kurang dari 10 (< 10)
            'harga_awal' => 10000,
            'harga_jual' => 15000,
            'subtotal' => 40000,
        ]);

        // Admin mengakses halaman notifikasi
        $response = $this->actingAs($admin)->get(route('notifikasi.index'));
        $response->assertOk();
        $response->assertSee('Notifikasi Administrator');
        $response->assertSee('Buka Kasir: Kasir Budi Open');
        $response->assertSee('Tutup Kasir: Kasir Ahmad Close');
        $response->assertSee('Stok Menipis (< 10): Kentang Goreng Keju');

        // Pastikan admin TIDAK melihat fitur kasir (Room Studio & Promo Voucher)
        $response->assertDontSee('Room Studio');
        $response->assertDontSee('Promo Voucher');
    }

    /**
     * Test notifikasi untuk role KASIR hanya menampilkan fitur ROOM dan PROMO saja.
     */
    public function test_kasir_sees_kasir_specific_notifications_and_not_admin_reports(): void
    {
        $cabang = TokoCabang::first();
        $kasir = $this->getKasir($cabang->id);

        // 1. Promo voucher aktif hari ini
        $promo = Promo::create([
            'nama_promo' => 'Diskon Nonton Hemat Spesial',
            'kode_voucher' => 'SPECIAL50K',
            'nilai' => 'Rp 50.000',
            'periode_berlaku' => '2026-01-01 - 2026-12-31',
            'status' => 'aktif',
        ]);

        // Kasir membuka shift
        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 150000,
            'status' => 'buka',
            'waktu_buka' => now()->subHours(2),
        ]);

        // 2. Setup Room dengan penayangan film hari ini (sisa waktu <= 15 menit)
        $film = Film::create([
            'judul' => 'Inception Reloaded',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Deluxe 02',
            'kapasitas' => 6,
        ]);

        $metode = TipePembayaran::first();

        // Transaksi dimulai 110 menit lalu (dari durasi 120 menit) -> sisa waktu ~10 menit!
        Transaksi::create([
            'nomor_transaksi' => 'TRX-NOTIF-001',
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $metode->id,
            'subtotal_film_room' => 60000,
            'subtotal_cafe' => 0,
            'total_bayar' => 60000,
            'waktu_transaksi' => now()->subMinutes(110),
            'status' => 'selesai',
            'durasi_menit' => 120,
        ]);

        // Kasir mengakses halaman notifikasi
        $response = $this->actingAs($kasir)->get(route('notifikasi.index'));
        $response->assertOk();
        $response->assertSee('Notifikasi Kasir POS');
        
        // Kasir melihat fitur ROOM & PROMO saja
        $response->assertSee('Room Studio');
        $response->assertSee('Studio Deluxe 02 Hampir Selesai');
        $response->assertSee('Inception Reloaded');
        $response->assertSee('Promo Voucher');
        $response->assertSee('SPECIAL50K');

        // Pastikan kasir TIDAK melihat fitur admin (Tutup Kasir kasir lain, Buka Kasir, Notifikasi Stok)
        $response->assertDontSee('Tutup Kasir');
        $response->assertDontSee('Notifikasi Stok');
    }

    /**
     * Test fitur "Tandai Semua Sudah Dibaca" (Mark All Read) bekerja dan menghapus status unread.
     */
    public function test_mark_all_notifications_as_read(): void
    {
        $kasir = $this->getKasir();

        // Sebelum ditandai dibaca: ada notifikasi belum buka shift
        $responseBefore = $this->actingAs($kasir)->getJson(route('notifikasi.json'));
        $responseBefore->assertOk();
        $this->assertGreaterThan(0, $responseBefore->json('unread_count'));

        // Kirim permintaan tandai semua dibaca via POST
        $markResponse = $this->actingAs($kasir)->postJson(route('notifikasi.mark-all-read'));
        $markResponse->assertOk();
        $markResponse->assertJson([
            'status' => 'success',
            'unread_count' => 0,
        ]);

        // Cek kembali: unread_count harus menjadi 0
        $responseAfter = $this->actingAs($kasir)->getJson(route('notifikasi.json'));
        $responseAfter->assertOk();
        $this->assertEquals(0, $responseAfter->json('unread_count'));
    }

    /**
     * Test bell dropdown pada template/navbar merender notifikasi dinamis sesuai role.
     */
    public function test_navbar_bell_dropdown_renders_role_specific_badge(): void
    {
        $admin = $this->getAdmin();
        $adminView = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminView->assertOk();
        $adminView->assertSee('Khusus Admin');
        $adminView->assertSee('Lihat Semua Notifikasi');

        $kasir = $this->getKasir();
        $kasirView = $this->actingAs($kasir)->get(route('kasir.dashboard'));
        $kasirView->assertOk();
        $kasirView->assertSee('Khusus Kasir');
        $kasirView->assertSee('Lihat Semua Notifikasi');
    }
}
