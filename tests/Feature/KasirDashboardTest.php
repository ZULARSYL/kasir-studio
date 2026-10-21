<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\MenuCafe;
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

class KasirDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_dashboard_tester'],
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
            ['username' => 'kasir_dashboard_tester'],
            [
                'name' => 'Budi Kasir',
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
     * Test hak akses: Admin tidak diizinkan masuk ke dashboard kasir (hanya role kasir).
     */
    public function test_admin_cannot_access_kasir_dashboard(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('kasir.index'));
        $response->assertForbidden();
    }

    /**
     * Test kasir dapat mengakses dashboard kasir dan melihat komponen metrik & navigasi.
     */
    public function test_kasir_can_access_dashboard_and_see_components(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $response = $this->actingAs($kasir)->get(route('kasir.index'));
        $response->assertOk();
        $response->assertSee('Selamat Datang, Budi Kasir!');
        $response->assertSee('TOTAL TRANSAKSI HARI INI');
        $response->assertSee('OMZET PENJUALAN HARI INI');
        $response->assertSee('ROOM STUDIO TERPAKAI');
        $response->assertSee('PRODUK CAFE TERJUAL HARI INI');
        $response->assertSee('Status &amp; Monitoring Room Studio', false);
        $response->assertSee('Riwayat Pembelian &amp; Transaksi Hari Ini (Today)', false);
        $response->assertSee('Menu Kasir (POS)');
    }

    /**
     * Test monitoring room studio: Menampilkan room terpakai dengan sisa waktunya secara akurat.
     */
    public function test_kasir_dashboard_displays_room_terpakai_and_sisa_waktu(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        // Buat 2 Room di cabang ini
        $room1 = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio VIP 1',
            'kapasitas' => 4,
        ]);

        $room2 = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Reguler 2',
            'kapasitas' => 6,
        ]);

        $film = Film::create([
            'judul' => 'Petualangan Angkasa Interstellar',
            'durasi_menit' => 120,
            'harga_per_menit' => 1000,
            'total_harga' => 120000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();

        // Bekukan waktu agar perhitungan sisa waktu tepat 90 menit (1 Jam 30 Menit)
        Carbon::setTestNow(Carbon::create(2026, 10, 21, 14, 0, 0));

        // Buat transaksi yang sedang tayang di Room 1 (mulai 30 menit yang lalu: 13:30)
        // Maka durasi 120 menit - 30 menit = 90 menit sisa waktu (1 Jam 30 Menit)
        $waktuMulai = Carbon::now()->subMinutes(30);

        Transaksi::create([
            'nomor_transaksi' => 'TRX-TODAY-0001',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room1->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Andi Wijaya',
            'durasi_menit' => 120,
            'harga_film' => 120000,
            'harga_room' => 0,
            'subtotal_film_room' => 120000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 120000,
            'uang_diterima' => 120000,
            'kembalian' => 0,
            'status' => 'selesai',
            'waktu_transaksi' => $waktuMulai,
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.index'));
        $response->assertOk();

        // Room 1 harus terpakai
        $response->assertSee('Studio VIP 1');
        $response->assertSee('SEDANG TERPAKAI');
        $response->assertSee('Petualangan Angkasa Interstellar');
        $response->assertSee('Andi Wijaya');
        $response->assertSee('Sisa Waktu Penayangan');
        $response->assertSee('1 Jam 30 Menit');

        // Room 2 harus tersedia / kosong
        $response->assertSee('Studio Reguler 2');
        $response->assertSee('TERSEDIA');
        $response->assertSee('Studio Siap Digunakan');

        // Status badge: 1 Room Terpakai
        $response->assertSee('1 Room Terpakai');

        Carbon::setTestNow(); // Reset time
    }

    /**
     * Test riwayat pembelian hari ini: Menampilkan transaksi hari ini dan mengecualikan transaksi kemarin.
     */
    public function test_kasir_dashboard_displays_today_purchases_and_excludes_yesterday(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Mini 3',
            'kapasitas' => 2,
        ]);

        $film = Film::create([
            'judul' => 'Komedi Romantis Senja',
            'durasi_menit' => 90,
            'harga_per_menit' => 1000,
            'total_harga' => 90000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();
        $satuan = SatuanBarang::first();

        $barang = Barang::create([
            'nama_barang' => 'Popcorn Manis',
        ]);

        // 1. Transaksi Hari Ini
        $trxHariIni = Transaksi::create([
            'nomor_transaksi' => 'TRX-' . date('Ymd') . '-9991',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Citra Lestari',
            'durasi_menit' => 90,
            'harga_film' => 90000,
            'harga_room' => 0,
            'subtotal_film_room' => 90000,
            'subtotal_cafe' => 25000,
            'diskon' => 0,
            'total_bayar' => 115000,
            'uang_diterima' => 150000,
            'kembalian' => 35000,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::today()->setHour(11),
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $trxHariIni->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 1,
            'harga_satuan' => 25000,
            'subtotal' => 25000,
        ]);

        // 2. Transaksi Kemarin (Tidak boleh muncul di riwayat hari ini)
        Transaksi::create([
            'nomor_transaksi' => 'TRX-KEMARIN-0001',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Doni Kemarin',
            'durasi_menit' => 90,
            'harga_film' => 90000,
            'harga_room' => 0,
            'subtotal_film_room' => 90000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 90000,
            'uang_diterima' => 100000,
            'kembalian' => 10000,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::yesterday()->setHour(14),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.index'));
        $response->assertOk();

        // Memastikan transaksi hari ini muncul
        $response->assertSee('TRX-' . date('Ymd') . '-9991');
        $response->assertSee('Citra Lestari');
        $response->assertSee('Popcorn Manis');
        $response->assertSee('115.000');

        // Memastikan transaksi kemarin TIDAK muncul di riwayat hari ini
        $response->assertDontSee('TRX-KEMARIN-0001');
        $response->assertDontSee('Doni Kemarin');
    }
}
