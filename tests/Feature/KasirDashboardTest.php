<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\MenuCafe;
use App\Models\Room;
use App\Models\SatuanBarang;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiFilmHistory;
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

    /**
     * Test room yang sedang digunakan memiliki tombol 'Buat Pesanan Baru' mengarah ke pesanan cafe,
     * serta memiliki tombol 'Hentikan' pemutaran lebih awal.
     */
    public function test_occupied_room_in_dashboard_has_pesanan_cafe_link_and_hentikan_button(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Deluxe 1',
            'kapasitas' => 4,
        ]);

        $film = Film::create([
            'judul' => 'Inception',
            'durasi_menit' => 148,
            'harga_per_menit' => 1000,
            'total_harga' => 148000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();

        Carbon::setTestNow(Carbon::create(2026, 10, 24, 10, 0, 0));

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-HENTIKAN-01',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Rina Sasmita',
            'durasi_menit' => 148,
            'harga_film' => 148000,
            'harga_room' => 0,
            'subtotal_film_room' => 148000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 148000,
            'uang_diterima' => 150000,
            'kembalian' => 2000,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::now()->subMinutes(30),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.index'));
        $response->assertOk();

        // Cek link Buat Pesanan Baru mengarah ke mode pesanan_cafe
        $expectedPesananCafeUrl = route('kasir.kasir', ['mode' => 'pesanan_cafe', 'room_id' => $room->id]);
        $response->assertSee($expectedPesananCafeUrl);
        $response->assertSee('Buat Pesanan Baru');

        // Cek tombol Hentikan dan modal hentikan pemutaran
        $response->assertSee('btn-open-hentikan-pemutaran');
        $response->assertSee('Hentikan');
        $response->assertSee('modalHentikanPemutaran');

        Carbon::setTestNow();
    }

    /**
     * Test kasir dapat memberhentikan penayangan film lebih awal,
     * sehingga sesi pemutaran ditutup dan room langsung menjadi TERSEDIA kembali.
     */
    public function test_kasir_can_hentikan_pemutaran_early_and_room_becomes_available(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Deluxe 2',
            'kapasitas' => 4,
        ]);

        $film = Film::create([
            'judul' => 'Interstellar Journey',
            'durasi_menit' => 120,
            'harga_per_menit' => 1000,
            'total_harga' => 120000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();

        Carbon::setTestNow(Carbon::create(2026, 10, 24, 12, 0, 0));

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-HENTIKAN-02',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Denny Pratama',
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
            'waktu_transaksi' => Carbon::now()->subMinutes(45), // Tayang 45 menit lalu
        ]);

        // Buat riwayat pemutaran aktif
        $history = TransaksiFilmHistory::create([
            'transaksi_id' => $transaksi->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'judul_film' => $film->judul,
            'waktu_mulai' => $transaksi->waktu_transaksi,
            'durasi_menit_rencana' => 120,
            'tarif_per_menit' => 1000,
            'biaya_film' => 120000,
            'status' => 'berjalan',
        ]);

        // Panggil endpoint hentikan pemutaran
        $response = $this->actingAs($kasir)->postJson(route('kasir.room.hentikan', $room->id), [
            'alasan' => 'Penonton selesai lebih awal atas permintaan sendiri',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'room_id' => $room->id,
                'durasi_aktual_menit' => 45,
            ],
        ]);

        // Verifikasi transaksi terupdate
        $transaksi->refresh();
        $this->assertNotNull($transaksi->waktu_selesai_film);
        $this->assertEquals(45, $transaksi->durasi_menit);
        $this->assertStringContainsString('Pemutaran film dihentikan lebih awal', $transaksi->catatan);

        // Verifikasi history status ditutup
        $history->refresh();
        $this->assertEquals('selesai', $history->status);
        $this->assertEquals(45, $history->durasi_aktual_menit);

        // Verifikasi di dashboard room kini berstatus TERSEDIA
        $dashboardResponse = $this->actingAs($kasir)->get(route('kasir.index'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Studio Siap Digunakan');

        Carbon::setTestNow();
    }

    /**
     * Test halaman POS saat dibuka dalam mode pesanan cafe mengunci tab film & room,
     * sehingga kasir HANYA bisa memilih pesanan cafe makanan & minuman.
     */
    public function test_kasir_pos_in_pesanan_cafe_mode_locks_film_and_room_selection(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Mini 5',
            'kapasitas' => 2,
        ]);

        $film = Film::create([
            'judul' => 'Dune Part Two',
            'durasi_menit' => 166,
            'harga_per_menit' => 1000,
            'total_harga' => 166000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();

        Carbon::setTestNow(Carbon::create(2026, 10, 24, 15, 0, 0));

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-CAFE-01',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'kasir_shift_id' => $shift->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Maya Anggraini',
            'durasi_menit' => 166,
            'harga_film' => 166000,
            'harga_room' => 0,
            'subtotal_film_room' => 166000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 166000,
            'uang_diterima' => 166000,
            'kembalian' => 0,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::now()->subMinutes(20),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.kasir', [
            'mode' => 'pesanan_cafe',
            'room_id' => $room->id,
        ]));

        $response->assertOk();

        // Banner instruksi mode pesanan cafe muncul
        $response->assertSee('Mode Pesanan Cafe (F&amp;B) &mdash; Studio ' . $room->nama, false);
        $response->assertSee('Khusus Makanan &amp; Minuman', false);
        $response->assertSee('TRX-TEST-CAFE-01');

        // Tab film dan room terkunci (disabled)
        $response->assertSee('Film (Terkunci)');
        $response->assertSee('Room (Terkunci)');
        $response->assertSee('Pesan Makanan & Minuman Cafe', false);

        // Panel checkout khusus cafe hadir
        $response->assertSee('Simpan &amp; Bayar Pesanan Cafe', false);

        Carbon::setTestNow();
    }

    /**
     * Test kasir dapat menyimpan pesanan cafe tambahan untuk room yang sedang aktif,
     * transaksi utama bertambah nilai F&B-nya, stok berkurang, dan film/room tetap tidak berubah.
     */
    public function test_kasir_can_save_additional_cafe_order_for_occupied_room(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $kasir->update(['toko_cabang_id' => $cabang->id]);

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Cafe 7',
            'kapasitas' => 4,
        ]);

        $film = Film::create([
            'judul' => 'The Batman',
            'durasi_menit' => 175,
            'harga_per_menit' => 1000,
            'total_harga' => 175000,
            'status' => 'tersedia',
        ]);

        $tipeBayar = TipePembayaran::first();
        $kategori = MenuCafe::first();
        $satuan = SatuanBarang::first();

        // Buat barang dan faktur stok
        $barang = Barang::create([
            'nama_barang' => 'Ice Lemon Tea Jumbo',
            'menu_cafe_id' => $kategori->id,
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FB-CAFE-ADD-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => '2026-10-24',
            'total_nominal' => 100000,
            'user_id' => $kasir->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 20,
            'harga_awal' => 8000,
            'harga_jual' => 18000,
            'subtotal' => 160000,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 10, 24, 16, 0, 0));

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-ADD-01',
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'kasir_shift_id' => $shift->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Garry Sanjaya',
            'durasi_menit' => 175,
            'harga_film' => 175000,
            'harga_room' => 0,
            'subtotal_film_room' => 175000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 175000,
            'uang_diterima' => 175000,
            'kembalian' => 0,
            'status' => 'selesai',
            'waktu_transaksi' => Carbon::now()->subMinutes(15),
        ]);

        // Simpan pesanan cafe tambahan: 2 pcs @ 18000 = 36000
        $response = $this->actingAs($kasir)->postJson(route('kasir.pesanan-cafe.simpan'), [
            'transaksi_id' => $transaksi->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'uang_diterima' => 50000,
            'cafe_items' => [
                [
                    'barang_id' => $barang->id,
                    'satuan_barang_id' => $satuan->id,
                    'qty' => 2,
                    'harga_satuan' => 18000,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'transaksi_id' => $transaksi->id,
                'total_tambahan' => 36000,
            ],
        ]);

        // Verifikasi transaksi utama terupdate
        $transaksi->refresh();
        $this->assertEquals(36000, $transaksi->subtotal_cafe);
        $this->assertEquals(175000 + 36000, $transaksi->total_bayar);
        // Film dan room tetap tidak berubah
        $this->assertEquals($film->id, $transaksi->film_id);
        $this->assertEquals($room->id, $transaksi->room_id);

        // Verifikasi detail cafe tersimpan di transaksi_details
        $this->assertDatabaseHas('transaksi_details', [
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'qty' => 2,
            'harga_satuan' => 18000,
            'subtotal' => 36000,
        ]);

        Carbon::setTestNow();
    }
}

