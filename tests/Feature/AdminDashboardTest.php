<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\MenuCafe;
use App\Models\Pengeluaran;
use App\Models\Room;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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
            ['username' => 'admin_dashboard_spec_tester'],
            [
                'name' => 'Admin Dashboard Spec Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir(TokoCabang $cabang): User
    {
        return User::firstOrCreate(
            ['username' => 'kasir_dashboard_spec_tester'],
            [
                'name' => 'Kasir Spec Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test admin dapat mengakses dashboard dan melihat 4 card KPI utama.
     */
    public function test_admin_dashboard_displays_four_metric_cards(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // 1. Card Jumlah Penjualan Film Per Bulan
        $response->assertSee('JUMLAH PENJUALAN FILM / BULAN');

        // 2. Card Jumlah Barang di Cafe
        $response->assertSee('JUMLAH BARANG DI CAFE');

        // 3. Card Laba Bersih Per Bulan
        $response->assertSee('LABA BERSIH PER BULAN');

        // 4. Card Pengeluaran Per Bulan
        $response->assertSee('PENGELUARAN PER BULAN');
    }

    /**
     * Test admin dashboard memuat komponen Sales Statistics Area Chart dengan 2 Garis Outlet dan tombol periode.
     */
    public function test_admin_dashboard_displays_sales_statistics_chart_with_dual_outlets_and_period_buttons(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // Section Chart
        $response->assertSee('Sales Statistics Area Chart');
        $response->assertSee('Perhari');
        $response->assertSee('Perminggu');
        $response->assertSee('Perbulan');

        // Dual Outlet Legends
        $response->assertSee('Garis 1 (Outlet A):');
        $response->assertSee('Garis 2 (Outlet B):');
        $response->assertSee('salesStatisticsChart');
    }

    /**
     * Test admin dashboard menampilkan Top 5 Film Terlaris untuk Outlet A (sebelah kiri) dan Outlet B (sebelah kanan).
     */
    public function test_admin_dashboard_displays_top_5_film_terlaris_for_outlet_a_and_outlet_b(): void
    {
        $admin = $this->getAdmin();
        $cabangs = TokoCabang::orderBy('id', 'asc')->get();
        $cabangA = $cabangs->get(0);
        $cabangB = $cabangs->get(1) ?? $cabangA;

        // Buat film contoh
        $filmA = Film::create([
            'judul' => 'Petualangan Rimba Raya',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'tahun_rilis' => 2026,
            'usia_rating' => '13+',
            'status' => 'tersedia',
        ]);

        $filmB = Film::create([
            'judul' => 'Misteri Kamar Gelap',
            'durasi_menit' => 90,
            'harga_per_menit' => 500,
            'total_harga' => 45000,
            'tahun_rilis' => 2026,
            'usia_rating' => '17+',
            'status' => 'tersedia',
        ]);

        $roomA = Room::where('toko_cabang_id', $cabangA->id)->first() ?? Room::first();
        $tipeBayar = TipePembayaran::first();

        $shiftA = KasirShift::create([
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabangA->id,
            'modal_awal' => 100000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        // Transaksi Film A di Cabang A
        Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-A1',
            'kasir_shift_id' => $shiftA->id,
            'toko_cabang_id' => $cabangA->id,
            'user_id' => $admin->id,
            'film_id' => $filmA->id,
            'room_id' => $roomA->id,
            'tipe_pembayaran_id' => $tipeBayar->id,
            'nama_pelanggan' => 'Pelanggan Outlet A',
            'durasi_menit' => 120,
            'jumlah_penonton' => 2,
            'harga_film' => 60000,
            'harga_room' => 50000,
            'subtotal_film_room' => 110000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 110000,
            'uang_diterima' => 110000,
            'kembalian' => 0,
            'status' => 'selesai',
            'waktu_transaksi' => now(),
        ]);

        // Transaksi Film B di Cabang B
        if ($cabangB && $cabangB->id !== $cabangA->id) {
            $roomB = Room::where('toko_cabang_id', $cabangB->id)->first() ?? Room::first();
            $shiftB = KasirShift::create([
                'user_id' => $admin->id,
                'toko_cabang_id' => $cabangB->id,
                'modal_awal' => 100000,
                'status' => 'buka',
                'waktu_buka' => now(),
            ]);

            Transaksi::create([
                'nomor_transaksi' => 'TRX-TEST-B1',
                'kasir_shift_id' => $shiftB->id,
                'toko_cabang_id' => $cabangB->id,
                'user_id' => $admin->id,
                'film_id' => $filmB->id,
                'room_id' => $roomB->id,
                'tipe_pembayaran_id' => $tipeBayar->id,
                'nama_pelanggan' => 'Pelanggan Outlet B',
                'durasi_menit' => 90,
                'jumlah_penonton' => 3,
                'harga_film' => 45000,
                'harga_room' => 50000,
                'subtotal_film_room' => 95000,
                'subtotal_cafe' => 0,
                'diskon' => 0,
                'total_bayar' => 95000,
                'uang_diterima' => 100000,
                'kembalian' => 5000,
                'status' => 'selesai',
                'waktu_transaksi' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // Memastikan label Outlet A dan Outlet B muncul
        $response->assertSee('Outlet A');
        $response->assertSee('Outlet B');

        // Memastikan film terlaris terdaftar
        $response->assertSee('Petualangan Rimba Raya');
        $response->assertSee('Misteri Kamar Gelap');
    }

    /**
     * Test kasir biasa dilarang mengakses dashboard admin.
     */
    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        $cabang = TokoCabang::first();
        $kasir = $this->getKasir($cabang);

        $response = $this->actingAs($kasir)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }
}
