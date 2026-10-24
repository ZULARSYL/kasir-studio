<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\MenuCafe;
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

class KasirPosTest extends TestCase
{
    use RefreshDatabase;

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_pos_tester'],
            [
                'name' => 'Kasir POS Tester',
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
     * Test kasir yang belum membuka shift melihat tampilan input modal awal (hari pertama / belum buka).
     */
    public function test_kasir_without_active_shift_sees_modal_awal_opening_form(): void
    {
        $kasir = $this->getKasir();

        $response = $this->actingAs($kasir)->get(route('kasir.kasir'));
        $response->assertOk();
        $response->assertSee('Buka Shift Kasir Hari Ini');
        $response->assertSee('Nominal Modal Awal Kas (Rp)');
        $response->assertSee('Pilih Outlet Cabang');
        $response->assertSee('Buka Kasir & Mulai Transaksi', false);
    }

    /**
     * Test kasir berhasil membuka shift dengan memasukkan modal awal dan memilih outlet.
     */
    public function test_kasir_can_open_shift_with_modal_awal(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();

        $response = $this->actingAs($kasir)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 250000,
            'catatan_buka' => 'Modal awal uang kembalian pecahan kecil',
        ]);

        $response->assertRedirect(route('kasir.kasir'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kasir_shifts', [
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 250000,
            'status' => 'buka',
        ]);
    }

    /**
     * Test kasir yang sudah membuka shift dapat melihat katalog film (movie.png), room studio, dan menu cafe.
     */
    public function test_kasir_with_active_shift_sees_movie_catalog_and_rooms(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();

        // Buka shift terlebih dahulu
        KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        // Buat film dan room untuk testing
        $film = Film::create([
            'judul' => 'Avatar Fire and Ash',
            'durasi_menit' => 150,
            'harga_per_menit' => 500,
            'total_harga' => 75000,
            'tahun_rilis' => 2026,
            'usia_rating' => '13+',
            'status' => 'tersedia',
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Room Premiere 1',
            'kapasitas' => 4,
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.kasir'));
        $response->assertOk();

        // Cek header shift aktif
        $response->assertSee('Shift Aktif');
        $response->assertSee($cabang->nama_cabang);

        // Cek katalog film (sesuai movie.png)
        $response->assertSee('STEP 1');
        $response->assertSee('Pilih Film Studio');
        $response->assertSee('Avatar Fire and Ash');

        // Cek room studio
        $response->assertSee('STEP 2');
        $response->assertSee('Pilih Room Studio Tersedia');
        $response->assertSee('Studio Room Premiere 1');

        // Cek menu cafe
        $response->assertSee('STEP 3');
        $response->assertSee('Pesan Makanan & Minuman Cafe', false);

        // Cek panel checkout
        $response->assertSee('Metode Pembayaran');
        $response->assertSee('Bayar & Cetak Struk', false);
    }

    public function test_kasir_pos_displays_today_sales_total_instead_of_shift_total(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $tipePembayaran = TipePembayaran::first();

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'total_penjualan' => 900000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        Transaksi::create([
            'nomor_transaksi' => 'TRX-POS-TODAY-001',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'tipe_pembayaran_id' => $tipePembayaran->id,
            'total_bayar' => 125000,
            'waktu_transaksi' => now(),
        ]);

        Transaksi::create([
            'nomor_transaksi' => 'TRX-POS-YESTERDAY-001',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'tipe_pembayaran_id' => $tipePembayaran->id,
            'total_bayar' => 500000,
            'waktu_transaksi' => now()->subDay(),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.kasir'));

        $response->assertOk();
        $response->assertSee('GRAND TOTAL PEMBELIAN HARI INI');
        $response->assertSee('Rp 125.000');
        $response->assertDontSee('Rp 900.000');
    }

    /**
     * Test validasi kode voucher promo pada menu kasir via AJAX.
     */
    public function test_kasir_can_check_promo_code(): void
    {
        $kasir = $this->getKasir();

        $promo = Promo::create([
            'kode_voucher' => 'DISC20K',
            'nama_promo' => 'Diskon 20 Ribu',
            'nilai' => 'Rp 20.000',
            'periode_berlaku' => '2026-01-01 - 2026-12-31',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($kasir)->postJson(route('kasir.cek-promo'), [
            'kode_voucher' => 'DISC20K',
            'subtotal' => 100000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'promo_id' => $promo->id,
            'diskon' => 20000,
            'total_setelah_diskon' => 80000,
        ]);
    }

    /**
     * Test alur checkout lengkap: film + room + cafe + pembayaran dari master data + cetak struk.
     */
    public function test_kasir_can_checkout_transaction_and_view_receipt(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $metodeBayar = TipePembayaran::first();
        $kategoriCafe = MenuCafe::first();
        $satuanPcs = SatuanBarang::first();

        // 1. Shift Aktif
        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        // 2. Film & Room
        $film = Film::create([
            'judul' => 'The Batman Part II',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio VIP 03',
            'kapasitas' => 6,
        ]);

        // 3. Cafe Item dengan Stok dari Faktur Masuk
        $barang = Barang::create([
            'nama_barang' => 'Popcorn Caramel Jumbo',
            'menu_cafe_id' => $kategoriCafe->id,
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-KASIR-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => '2026-10-19',
            'total_nominal' => 200000,
            'user_id' => $kasir->id,
        ]);

        $fakturDetail = FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuanPcs->id,
            'qty' => 10,
            'harga_awal' => 15000,
            'harga_jual' => 25000,
            'subtotal' => 150000,
        ]);

        // 4. Lakukan Checkout Transaksi (Beli 2 Popcorn @ 25.000 = 50.000 + Film 60.000 = 110.000)
        $checkoutData = [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
            'nama_pelanggan' => 'Budi Santoso',
            'uang_diterima' => 150000,
            'cafe_items' => [
                [
                    'barang_id' => $barang->id,
                    'satuan_barang_id' => $satuanPcs->id,
                    'qty' => 2,
                    'harga_satuan' => 25000,
                ],
            ],
        ];

        $response = $this->actingAs($kasir)->postJson(route('kasir.checkout'), $checkoutData);
        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
        ]);

        $transaksiId = $response->json('transaksi_id');

        // Pastikan transaksi tersimpan dengan benar
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksiId,
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
            'subtotal_film_room' => 60000,
            'subtotal_cafe' => 50000,
            'total_bayar' => 110000,
            'uang_diterima' => 150000,
            'kembalian' => 40000,
        ]);

        // Pastikan detail cafe tersimpan
        $this->assertDatabaseHas('transaksi_details', [
            'transaksi_id' => $transaksiId,
            'barang_id' => $barang->id,
            'qty' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        // Pastikan stok berkurang dari 10 menjadi 8
        $this->assertDatabaseHas('faktur_barang_details', [
            'id' => $fakturDetail->id,
            'qty' => 8,
        ]);

        // Pastikan shift kasir terakumulasi
        $this->assertDatabaseHas('kasir_shifts', [
            'id' => $shift->id,
            'total_penjualan' => 110000,
            'total_transaksi' => 1,
        ]);

        // 5. Cetak Struk Transaksi
        $strukResponse = $this->actingAs($kasir)->get(route('kasir.struk', $transaksiId));
        $strukResponse->assertOk();
        $strukResponse->assertSee('PRESSENT STUDIO & CAFE', false);
        $strukResponse->assertSee('The Batman Part II');
        $strukResponse->assertSee('Studio VIP 03');
        $strukResponse->assertSee('Popcorn Caramel Jumbo');
        $strukResponse->assertSee('Rp 110.000');
        $strukResponse->assertSee('Rp 40.000'); // Kembalian
    }

    /**
     * Test kasir dapat melakukan tutup toko / tutup shift.
     */
    public function test_kasir_can_close_shift_tutup_toko(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'total_penjualan' => 500000,
            'total_tunai' => 500000,
            'total_transaksi' => 3,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $response = $this->actingAs($kasir)->post(route('kasir.shift.tutup'), [
            'modal_akhir' => 700000, // 200k modal awal + 500k penjualan
            'catatan_tutup' => 'Laci kasir cocok dan seimbang',
        ]);

        $response->assertRedirect(route('kasir.kasir'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kasir_shifts', [
            'id' => $shift->id,
            'modal_akhir' => 700000,
            'status' => 'tutup',
        ]);

        // Setelah tutup toko, jika kasir mengakses menu kasir lagi, muncul form input modal awal
        $nextResponse = $this->actingAs($kasir)->get(route('kasir.kasir'));
        $nextResponse->assertOk();
        $nextResponse->assertSee('Buka Shift Kasir Hari Ini');
    }

    /**
     * Test kasir yang sudah ditetapkan toko cabangnya otomatis menggunakan outlet tersebut saat buka shift.
     */
    public function test_kasir_with_assigned_outlet_automatically_opens_shift_without_dropdown(): void
    {
        $cabang = TokoCabang::first();
        $kasir = User::create([
            'name' => 'Kasir Cabang Khusus',
            'username' => 'kasir_cabang_khusus',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'toko_cabang_id' => $cabang->id,
        ]);

        // Saat mengakses kasir, kasir melihat outlet yang sudah ditetapkan secara otomatis
        $response = $this->actingAs($kasir)->get(route('kasir.kasir'));
        $response->assertOk();
        $response->assertSee($cabang->nama_cabang);
        $response->assertSee('Outlet otomatis ditetapkan');

        // Buka shift tanpa mengirimkan toko_cabang_id dari form dropdown
        $bukaResponse = $this->actingAs($kasir)->post(route('kasir.shift.buka'), [
            'modal_awal' => 150000,
            'catatan_buka' => 'Buka kasir dengan outlet bawaan user',
        ]);

        $bukaResponse->assertRedirect(route('kasir.kasir'));
        $bukaResponse->assertSessionHas('success');

        $this->assertDatabaseHas('kasir_shifts', [
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 150000,
            'status' => 'buka',
        ]);
    }

    /**
     * Test checkout dengan jumlah penonton lebih dari 2 orang dikenakan denda Rp 5.000 per orang.
     */
    public function test_kasir_checkout_with_extra_penonton_calculates_denda_correctly(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 100000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $film = Film::create([
            'judul' => 'Avatar Way of Water',
            'durasi_menit' => 180,
            'limit_penggunaan' => 10,
            'harga_per_menit' => 500,
            'total_harga' => 50000,
            'status' => 'tersedia',
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Deluxe 01',
            'kapasitas' => 8,
        ]);

        $metodeBayar = TipePembayaran::first();

        // KASUS 1: 2 Penonton -> Tanpa Denda (denda = 0)
        $checkoutData2 = [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'jumlah_penonton' => 2,
            'tipe_pembayaran_id' => $metodeBayar->id,
            'nama_pelanggan' => 'Pasangan Penonton',
            'uang_diterima' => 50000,
            'cafe_items' => [],
        ];
        $res2 = $this->actingAs($kasir)->postJson(route('kasir.checkout'), $checkoutData2);
        $res2->assertOk();
        $this->assertDatabaseHas('transaksis', [
            'id' => $res2->json('transaksi_id'),
            'jumlah_penonton' => 2,
            'denda_penonton' => 0,
            'subtotal_film_room' => 50000,
            'total_bayar' => 50000,
        ]);

        // KASUS 2: 3 Penonton -> 1 orang lebih -> Denda Rp 5.000
        $checkoutData3 = [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'jumlah_penonton' => 3,
            'tipe_pembayaran_id' => $metodeBayar->id,
            'nama_pelanggan' => 'Tiga Sahabat',
            'uang_diterima' => 60000,
            'cafe_items' => [],
        ];
        $res3 = $this->actingAs($kasir)->postJson(route('kasir.checkout'), $checkoutData3);
        $res3->assertOk();
        $this->assertDatabaseHas('transaksis', [
            'id' => $res3->json('transaksi_id'),
            'jumlah_penonton' => 3,
            'denda_penonton' => 5000,
            'subtotal_film_room' => 55000, // 50.000 + 5.000
            'total_bayar' => 55000,
        ]);

        // KASUS 3: 5 Penonton -> 3 orang lebih -> Denda 3 x Rp 5.000 = Rp 15.000
        $checkoutData5 = [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'jumlah_penonton' => 5,
            'tipe_pembayaran_id' => $metodeBayar->id,
            'nama_pelanggan' => 'Grup Rombongan 5 Orang',
            'uang_diterima' => 100000,
            'cafe_items' => [],
        ];
        $res5 = $this->actingAs($kasir)->postJson(route('kasir.checkout'), $checkoutData5);
        $res5->assertOk();
        $transaksiId5 = $res5->json('transaksi_id');

        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksiId5,
            'jumlah_penonton' => 5,
            'denda_penonton' => 15000,
            'subtotal_film_room' => 65000, // 50.000 + 15.000
            'total_bayar' => 65000,
            'uang_diterima' => 100000,
            'kembalian' => 35000,
        ]);

        // Cek cetak struk untuk kasus 5 penonton
        $strukRes = $this->actingAs($kasir)->get(route('kasir.struk', $transaksiId5));
        $strukRes->assertOk();
        $strukRes->assertSee('5 Orang');
        $strukRes->assertSee('Extra +Rp 15.000');
        $strukRes->assertSee('Rp 65.000');
    }
}
