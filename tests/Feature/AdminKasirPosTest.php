<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\GenreFilm;
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

class AdminKasirPosTest extends TestCase
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
            ['username' => 'admin_kasir_urgent_tester'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'toko_cabang_id' => null, // Admin tidak memiliki cabang tetap
            ]
        );
    }

    protected function getKasir(TokoCabang $cabang): User
    {
        return User::firstOrCreate(
            ['username' => 'kasir_outlet_tester'],
            [
                'name' => 'Staf Kasir Cabang',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test hak akses: Staf kasir biasa dilarang mengakses menu kasir admin (/admin/kasir).
     */
    public function test_kasir_cannot_access_admin_kasir(): void
    {
        $cabang = TokoCabang::first();
        $kasir = $this->getKasir($cabang);

        $response = $this->actingAs($kasir)->get(route('admin.kasir.index'));
        $response->assertForbidden();
    }

    /**
     * Test admin tanpa shift aktif dapat melihat form buka kasir dengan pemilihan outlet cabang (Mode Darurat).
     */
    public function test_admin_without_active_shift_sees_outlet_selector_and_urgent_notice(): void
    {
        $admin = $this->getAdmin();
        $cabangs = TokoCabang::all();

        $response = $this->actingAs($admin)->get(route('admin.kasir.index'));
        $response->assertOk();
        $response->assertSee('Mode Kasir Darurat (Administrator)');
        $response->assertSee('Pilih Outlet Cabang Operasional');

        // Pastikan seluruh cabang muncul dalam opsi dropdown
        foreach ($cabangs as $cabang) {
            $response->assertSee($cabang->nama_cabang);
        }
    }

    /**
     * Test admin dapat memilih cabang manapun dan membuka shift kasir darurat.
     */
    public function test_admin_can_select_any_outlet_and_open_shift(): void
    {
        $admin = $this->getAdmin();
        // Pilih cabang kedua untuk membuktikan fleksibilitas pemilihan outlet
        $selectedCabang = TokoCabang::skip(1)->first() ?: TokoCabang::first();

        $response = $this->actingAs($admin)->post(route('admin.kasir.shift.buka'), [
            'toko_cabang_id' => $selectedCabang->id,
            'modal_awal' => 300000,
            'catatan_buka' => 'Kasir berhalangan hadir sakit mendadak, admin menggantikan operasional kasir.',
        ]);

        $response->assertRedirect(route('admin.kasir.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kasir_shifts', [
            'user_id' => $admin->id,
            'toko_cabang_id' => $selectedCabang->id,
            'modal_awal' => 300000,
            'status' => 'buka',
            'catatan_buka' => 'Kasir berhalangan hadir sakit mendadak, admin menggantikan operasional kasir.',
        ]);
    }

    /**
     * Test tampilan aktif POS untuk admin menampilkan indikator Mode Darurat Admin dan katalog outlet terpilih.
     */
    public function test_admin_active_shift_displays_selected_outlet_and_urgent_badge(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();

        KasirShift::create([
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kasir.index'));
        $response->assertOk();
        $response->assertSee('Mode Darurat Admin');
        $response->assertSee($cabang->nama_cabang);
        $response->assertSee('Menu Kasir (POS)');
        $response->assertSee('Pilih Film Studio');
        $response->assertSee('Pilih Room Studio Tersedia');
        $response->assertSee('Pesan Makanan & Minuman Cafe', false);
    }

    /**
     * Test admin dapat melakukan transaksi checkout lengkap (Film, Room, Cafe, Denda Penonton > 2, Promo) dan cetak struk.
     */
    public function test_admin_can_process_checkout_transaction_and_view_struk(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();

        $shift = KasirShift::create([
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        $film = Film::create([
            'judul' => 'Interstellar Horizon',
            'durasi_menit' => 150,
            'harga_per_menit' => 500,
            'total_harga' => 75000,
            'status' => 'tersedia',
        ]);

        $room = Room::create([
            'toko_cabang_id' => $cabang->id,
            'nama' => 'Studio Premiere 01',
            'kapasitas' => 8,
        ]);

        $kategori = MenuCafe::first();
        $satuan = SatuanBarang::first();
        $barang = Barang::create([
            'nama_barang' => 'Iced Coffee Caramel',
            'menu_cafe_id' => $kategori->id,
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-ADMIN-001',
            'toko_cabang_id' => $cabang->id,
            'tanggal_faktur' => now()->toDateString(),
            'total_nominal' => 200000,
            'user_id' => $admin->id,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barang->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 20,
            'harga_awal' => 8000,
            'harga_jual' => 15000,
            'subtotal' => 160000,
        ]);

        $tipePembayaran = TipePembayaran::create([
            'metode_pembayaran' => 'Tunai Cash',
        ]);

        // Checkout: 4 penonton (2 standar + 2 orang denda @ 5.000 = +10.000), 2 Ice Coffee (@ 15.000 = +30.000)
        // Subtotal = 75.000 (Film) + 10.000 (Denda 2 penonton) + 30.000 (Cafe) = 115.000
        $payload = [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipePembayaran->id,
            'nama_pelanggan' => 'Tuan Hendra (Pelanggan Urgent)',
            'jumlah_penonton' => 4,
            'uang_diterima' => 150000,
            'cafe_items' => [
                [
                    'barang_id' => $barang->id,
                    'satuan_barang_id' => $satuan->id,
                    'qty' => 2,
                    'harga_satuan' => 15000,
                ],
            ],
        ];

        $checkoutResponse = $this->actingAs($admin)->postJson(route('admin.kasir.checkout'), $payload);
        $checkoutResponse->assertOk();
        $checkoutResponse->assertJson(['status' => 'success']);

        $transaksiId = $checkoutResponse->json('transaksi_id');
        $this->assertNotNull($transaksiId);

        $transaksi = Transaksi::find($transaksiId);
        $this->assertEquals(75000, (float) $transaksi->harga_film);
        $this->assertEquals(4, $transaksi->jumlah_penonton);
        $this->assertEquals(10000, (float) $transaksi->denda_penonton);
        $this->assertEquals(30000, (float) $transaksi->subtotal_cafe);
        $this->assertEquals(115000, (float) $transaksi->total_bayar);
        $this->assertEquals(35000, (float) $transaksi->kembalian);
        $this->assertEquals($admin->id, $transaksi->user_id);
        $this->assertEquals($cabang->id, $transaksi->toko_cabang_id);

        // Cetak Struk POS Admin
        $strukResponse = $this->actingAs($admin)->get(route('admin.kasir.struk', $transaksi->id));
        $strukResponse->assertOk();
        $strukResponse->assertSee('Tuan Hendra (Pelanggan Urgent)');
        $strukResponse->assertSee('Studio Premiere 01');
        $strukResponse->assertSee('Penonton: 4 Orang');
        $strukResponse->assertSee('(Extra +Rp 10.000)');
        $strukResponse->assertSee('Iced Coffee Caramel');
        $strukResponse->assertSee('115.000');
    }

    /**
     * Test admin dapat menutup shift kasir lalu membuka shift untuk cabang lainnya.
     */
    public function test_admin_can_close_shift_and_switch_outlet(): void
    {
        $admin = $this->getAdmin();
        $cabangs = TokoCabang::all();
        $cabang1 = $cabangs->first();
        $cabang2 = $cabangs->skip(1)->first() ?: $cabang1;

        // Buka shift di cabang 1
        $shift1 = KasirShift::create([
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabang1->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now()->subHours(3),
        ]);

        // Tutup shift cabang 1
        $tutupResponse = $this->actingAs($admin)->post(route('admin.kasir.shift.tutup'), [
            'modal_akhir' => 200000,
            'catatan_tutup' => 'Tutup shift cabang 1 selesai, persiapan ganti cabang.',
        ]);

        $tutupResponse->assertRedirect(route('admin.kasir.index'));
        $this->assertEquals('tutup', $shift1->fresh()->status);

        // Buka shift baru di cabang 2
        $bukaCabang2 = $this->actingAs($admin)->post(route('admin.kasir.shift.buka'), [
            'toko_cabang_id' => $cabang2->id,
            'modal_awal' => 250000,
            'catatan_buka' => 'Buka kasir darurat di cabang 2.',
        ]);

        $bukaCabang2->assertRedirect(route('admin.kasir.index'));
        $this->assertDatabaseHas('kasir_shifts', [
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabang2->id,
            'modal_awal' => 250000,
            'status' => 'buka',
        ]);
    }
}
