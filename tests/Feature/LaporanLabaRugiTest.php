<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\JenisBiaya;
use App\Models\MenuCafe;
use App\Models\Pengeluaran;
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

class LaporanLabaRugiTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_laba_rugi_tester'],
            [
                'name' => 'Admin Laba Rugi Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir()
    {
        return User::firstOrCreate(
            ['username' => 'kasir_laba_rugi_tester'],
            [
                'name' => 'Kasir Laba Rugi Tester',
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
     * Test hak akses kasir: kasir tidak boleh mengakses laporan laba rugi.
     */
    public function test_kasir_cannot_access_laporan_laba_rugi(): void
    {
        $kasir = $this->getKasir();

        $this->actingAs($kasir)->get(route('admin.laporan.laba-rugi'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.laporan.laba-rugi.export'))->assertForbidden();
    }

    /**
     * Test admin dapat melihat menu laporan laba rugi di sidebar dan halaman berhasil dimuat.
     */
    public function test_admin_sees_laba_rugi_in_sidebar_and_page_loads(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi'));
        $response->assertOk();
        $response->assertSee('Laporan Laba &amp; Rugi', false);
        $response->assertSee('A. PENDAPATAN USAHA');
        $response->assertSee('Pendapatan Film');
        $response->assertSee('Pendapatan Kafe');
        $response->assertSee('Diskon Penjualan');
        $response->assertSee('B. HARGA POKOK PENJUALAN (HPP)');
        $response->assertSee('HPP Makanan dan Minuman');
        $response->assertSee('LABA KOTOR');
        $response->assertSee('C. BEBAN OPERASIONAL');
        $response->assertSee('Gaji Karyawan');
        $response->assertSee('Listrik dan Air');
        $response->assertSee('Internet');
        $response->assertSee('Sewa Gedung');
        $response->assertSee('TOTAL NETTO');
        $response->assertSee('Ekspor Excel');
    }

    /**
     * Test kalkulasi lengkap Pendapatan Usaha, HPP, Beban Operasional, dan Total Netto.
     */
    public function test_laba_rugi_comprehensive_calculation(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();
        $tipeBayar = TipePembayaran::first();
        $room = Room::where('toko_cabang_id', $outlet->id)->first();
        $film = Film::first();
        $kategoriMakanan = MenuCafe::first();
        $satuan = SatuanBarang::first();

        // 1. Buat master barang & faktur pembelian untuk mencatat harga awal (HPP)
        $barangSnack = Barang::create([
            'nama_barang' => 'Popcorn Caramel Regular',
            'menu_cafe_id' => $kategoriMakanan->id,
            'satuan_default_id' => $satuan->id,
        ]);

        $faktur = FakturBarang::create([
            'nomor_faktur' => 'FKT-LBRG-001',
            'toko_cabang_id' => $outlet->id,
            'tanggal_faktur' => '2026-10-01',
            'nama_supplier' => 'Supplier Snack Utama',
            'total_nominal' => 250000,
        ]);

        FakturBarangDetail::create([
            'faktur_barang_id' => $faktur->id,
            'barang_id' => $barangSnack->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 50,
            'harga_awal' => 10000, // HPP modal Rp 10.000 per unit
            'harga_jual' => 25000, // Harga jual Rp 25.000
            'subtotal' => 500000,
        ]);

        // 2. Buat transaksi penjualan kasir pada tanggal 2026-10-15:
        // - Tiket Film: Rp 50.000
        // - Cafe: 4 x Popcorn @ 25.000 = Rp 100.000 (Modal HPP: 4 x 10.000 = 40.000)
        // - Diskon Promo: Rp 15.000
        // - Total Bayar: (50.000 + 100.000) - 15.000 = Rp 135.000
        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-LBRG-20261015-001',
            'toko_cabang_id' => $outlet->id,
            'user_id' => $admin->id,
            'film_id' => $film?->id,
            'room_id' => $room?->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Andi Wijaya',
            'durasi_menit' => 120,
            'harga_film' => 50000,
            'harga_room' => 0,
            'subtotal_film_room' => 50000,
            'subtotal_cafe' => 100000,
            'diskon' => 15000,
            'total_bayar' => 135000,
            'status' => 'selesai',
            'waktu_transaksi' => '2026-10-15 14:30:00',
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barangSnack->id,
            'satuan_barang_id' => $satuan->id,
            'qty' => 4,
            'harga_satuan' => 25000,
            'subtotal' => 100000,
        ]);

        // 3. Buat pengeluaran operasional di bulan Oktober 2026:
        $jbGaji = JenisBiaya::where('kategori', 'Gaji Karyawan')->first();
        $jbListrik = JenisBiaya::where('kategori', 'Listrik dan Air')->first();
        $jbInternet = JenisBiaya::where('kategori', 'Internet')->first();
        $jbSewa = JenisBiaya::where('kategori', 'Sewa Gedung')->first();

        Pengeluaran::create([
            'tanggal' => '2026-10-10',
            'toko_cabang_id' => $outlet->id,
            'jenis_biaya_id' => $jbListrik->id,
            'nominal' => 25000,
            'keterangan' => 'Tagihan listrik studio',
            'user_id' => $admin->id,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-10-12',
            'toko_cabang_id' => $outlet->id,
            'jenis_biaya_id' => $jbInternet->id,
            'nominal' => 15000,
            'keterangan' => 'Tagihan WiFi Indihome',
            'user_id' => $admin->id,
        ]);

        // Ekspektasi:
        // A. Total Pendapatan Usaha = 50.000 + 100.000 - 15.000 = 135.000
        // B. HPP Makanan & Minuman = 4 x 10.000 = 40.000
        //    Laba Kotor = 135.000 - 40.000 = 95.000
        // C. Beban Operasional = 25.000 + 15.000 = 40.000
        // Total Netto = 95.000 - 40.000 = 55.000 (Laba Bersih)

        $response = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi', [
            'periode' => 'perbulan',
            'bulan' => 10,
            'tahun' => 2026,
            'toko_cabang_id' => $outlet->id,
        ]));

        $response->assertOk();
        $response->assertSee('50.000'); // Pendapatan Film
        $response->assertSee('100.000'); // Pendapatan Kafe
        $response->assertSee('15.000'); // Diskon
        $response->assertSee('135.000'); // Total Pendapatan
        $response->assertSee('40.000'); // HPP
        $response->assertSee('95.000'); // Laba Kotor
        $response->assertSee('25.000'); // Beban Listrik
        $response->assertSee('55.000'); // Total Netto Laba Bersih
        $response->assertSee('LABA BERSIH');

        // 4. Test Ekspor Excel
        $exportResponse = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi.export', [
            'periode' => 'perbulan',
            'bulan' => 10,
            'tahun' => 2026,
            'toko_cabang_id' => $outlet->id,
        ]));

        $exportResponse->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $exportResponse->headers->get('content-type'));
        $this->assertStringContainsString('Laporan_Laba_Rugi', $exportResponse->headers->get('content-disposition'));
        $exportResponse->assertSee('LAPORAN LABA &amp; RUGI', false);
        $exportResponse->assertSee('135.000');
        $exportResponse->assertSee('40.000');
        $exportResponse->assertSee('55.000');
    }

    /**
     * Test filter periode: perhari, perminggu, perbulan, pertahun.
     */
    public function test_laba_rugi_filter_periods(): void
    {
        $admin = $this->getAdmin();
        $outlet = TokoCabang::first();

        // Filter perhari
        $resHari = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi', [
            'periode' => 'perhari',
            'tanggal' => '2026-10-20',
        ]));
        $resHari->assertOk();
        $resHari->assertSee('Hari: 20 Oktober 2026');

        // Filter perminggu
        $resMinggu = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi', [
            'periode' => 'perminggu',
            'start_date' => '2026-10-19',
            'end_date' => '2026-10-25',
        ]));
        $resMinggu->assertOk();
        $resMinggu->assertSee('Minggu: 19 Okt 2026 s/d 25 Okt 2026');

        // Filter perbulan
        $resBulan = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi', [
            'periode' => 'perbulan',
            'bulan' => 10,
            'tahun' => 2026,
        ]));
        $resBulan->assertOk();
        $resBulan->assertSee('Bulan: Oktober 2026');

        // Filter pertahun
        $resTahun = $this->actingAs($admin)->get(route('admin.laporan.laba-rugi', [
            'periode' => 'pertahun',
            'tahun' => 2026,
        ]));
        $resTahun->assertOk();
        $resTahun->assertSee('Tahun: 2026');
    }
}
