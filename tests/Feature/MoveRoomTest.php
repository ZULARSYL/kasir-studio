<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Film;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiFilmHistory;
use App\Models\TransaksiRoomHistory;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MoveRoomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    protected function getKasir(): User
    {
        $cabang = TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'kasir_move_room_tester'],
            [
                'name' => 'Kasir Move Room Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    protected function getAdmin(): User
    {
        $cabang = TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'admin_move_room_tester'],
            [
                'name' => 'Admin Move Room Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Helper untuk membuat setup transaksi aktif di Room 01 dengan pesanan kafe.
     */
    protected function setupActiveTransaction(): array
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $tipe = TipePembayaran::first();

        // Siapkan Ruangan 01 dan Ruangan 05 pada cabang yang sama
        $room01 = Room::firstOrCreate(
            ['nama' => 'Ruangan 01', 'toko_cabang_id' => $cabang->id],
            ['kapasitas' => 4, 'harga_per_jam' => 0]
        );

        $room05 = Room::firstOrCreate(
            ['nama' => 'Ruangan 05', 'toko_cabang_id' => $cabang->id],
            ['kapasitas' => 6, 'harga_per_jam' => 0]
        );

        $film = Film::create([
            'judul' => 'Inception Super Movie',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
            'limit_penggunaan' => 5,
        ]);

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now()->subHours(2),
        ]);

        // Mulai 30 menit yang lalu (durasi 120 menit, sisa 90 menit)
        $waktuMulai = now()->subMinutes(30);

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-MOVE-ROOM-001',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $film->id,
            'room_id' => $room01->id,
            'tipe_pembayaran_id' => $tipe->id,
            'nama_pelanggan' => 'Pelanggan Move Test',
            'durasi_menit' => 120,
            'harga_film' => 60000,
            'harga_room' => 0,
            'subtotal_film_room' => 60000,
            'subtotal_cafe' => 35000,
            'diskon' => 0,
            'total_bayar' => 95000,
            'uang_diterima' => 100000,
            'kembalian' => 5000,
            'status' => 'selesai',
            'waktu_transaksi' => $waktuMulai,
            'waktu_mulai_film' => $waktuMulai,
            'catatan' => 'Booking awal Ruangan 01',
        ]);

        // Tambahkan item pesanan kafe
        $barang = Barang::first();
        if ($barang) {
            TransaksiDetail::create([
                'transaksi_id' => $transaksi->id,
                'barang_id' => $barang->id,
                'qty' => 2,
                'harga_satuan' => 17500,
                'subtotal' => 35000,
            ]);
        }

        return compact('kasir', 'cabang', 'room01', 'room05', 'film', 'shift', 'transaksi', 'waktuMulai');
    }

    /**
     * Test mendapatkan daftar room yang tersedia untuk pemindahan.
     */
    public function test_get_available_rooms_for_move_success(): void
    {
        $data = $this->setupActiveTransaction();

        $response = $this->actingAs($data['kasir'])
            ->getJson(route('kasir.move-room.rooms', ['roomId' => $data['room01']->id]));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'room_asal' => [
                    'id' => $data['room01']->id,
                    'nama' => $data['room01']->nama,
                ],
                'transaksi' => [
                    'id' => $data['transaksi']->id,
                    'nomor_transaksi' => $data['transaksi']->nomor_transaksi,
                    'film_judul' => 'Inception Super Movie',
                    'nama_pelanggan' => 'Pelanggan Move Test',
                ],
            ]);

        $this->assertNotEmpty($response->json('available_rooms'));
    }

    /**
     * Test sukses melakukan perpindahan ruangan dari Ruangan 01 ke Ruangan 05
     * dan memastikan seluruh 7 aturan dipenuhi.
     */
    public function test_move_room_preserves_start_time_duration_film_cafe_and_updates_rooms(): void
    {
        $data = $this->setupActiveTransaction();
        $transaksi = $data['transaksi'];
        $waktuMulaiAwal = $data['waktuMulai'];
        $filmIdAwal = $data['film']->id;
        $cafeSubtotalAwal = (float) $transaksi->subtotal_cafe;
        $totalBayarAwal = (float) $transaksi->total_bayar;

        $response = $this->actingAs($data['kasir'])
            ->postJson(route('kasir.move-room.save'), [
                'transaksi_id' => $transaksi->id,
                'room_tujuan_id' => $data['room05']->id,
                'alasan_pindah' => 'Kendala AC pada Ruangan 01, pindah ke Ruangan 05',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'room_asal' => 'Ruangan 01',
                    'room_tujuan' => 'Ruangan 05',
                ],
            ]);

        $transaksi->refresh();

        // Aturan 1: Waktu mulai transaksi tetap waktu pada saat dia pertama kali memesan film
        $this->assertEquals(
            $waktuMulaiAwal->toDateTimeString(),
            $transaksi->waktu_transaksi->toDateTimeString(),
            'Waktu transaksi harus tetap seperti awal pemesanan'
        );
        $this->assertEquals(
            $waktuMulaiAwal->toDateTimeString(),
            $transaksi->waktu_mulai_film->toDateTimeString(),
            'Waktu mulai film harus tetap seperti awal pemesanan'
        );

        // Aturan 2: Durasi yang telah berjalan tetap dihitung (durasi total tetap 120 menit)
        $this->assertEquals(120, $transaksi->durasi_menit);

        // Aturan 3: Film yang sedang diputar tetap Film yang dia pesan
        $this->assertEquals($filmIdAwal, $transaksi->film_id);

        // Aturan 4: Pesanan kafe tetap tersimpan
        $this->assertEquals($cafeSubtotalAwal, (float) $transaksi->subtotal_cafe);
        $this->assertEquals($totalBayarAwal, (float) $transaksi->total_bayar);
        $this->assertGreaterThan(0, $transaksi->details()->count());

        // Aturan 5 & 6: Transaksi sekarang berada di Ruangan 05 (Ruangan 01 berpindah)
        $this->assertEquals($data['room05']->id, $transaksi->room_id);

        // Aturan 7: Riwayat perpindahan disimpan agar bisa diperiksa di laporan
        $this->assertDatabaseHas('transaksi_room_histories', [
            'transaksi_id' => $transaksi->id,
            'room_asal_id' => $data['room01']->id,
            'room_tujuan_id' => $data['room05']->id,
            'nama_room_asal' => 'Ruangan 01',
            'nama_room_tujuan' => 'Ruangan 05',
            'alasan_pindah' => 'Kendala AC pada Ruangan 01, pindah ke Ruangan 05',
        ]);

        $history = TransaksiRoomHistory::where('transaksi_id', $transaksi->id)->first();
        $this->assertNotNull($history);
        $this->assertGreaterThanOrEqual(29, $history->durasi_berjalan_menit);
        $this->assertLessThanOrEqual(91, $history->sisa_durasi_menit);

        // Verifikasi pada Dashboard Kasir: Ruangan 01 TERSEDIA, Ruangan 05 TERISI
        $dashboardResponse = $this->actingAs($data['kasir'])
            ->get(route('kasir.dashboard'));

        $dashboardResponse->assertStatus(200);

        // Verifikasi melalui JSON status room dashboard
        $statusResponse = $this->actingAs($data['kasir'])
            ->getJson(route('kasir.dashboard', ['mode' => 'rooms_status']));

        $statusResponse->assertStatus(200);
        $rooms = collect($statusResponse->json('rooms'));

        $room01Status = $rooms->firstWhere('id', $data['room01']->id);
        $room05Status = $rooms->firstWhere('id', $data['room05']->id);

        $this->assertNotNull($room01Status);
        $this->assertNotNull($room05Status);

        // Ruangan 01 menjadi tersedia setelah perpindahan berhasil
        $this->assertFalse($room01Status['is_occupied'], 'Ruangan 01 harus menjadi TERSEDIA setelah dipindahkan.');

        // Ruangan 05 menjadi terisi dengan film & sisa waktu penayangan yang sama
        $this->assertTrue($room05Status['is_occupied'], 'Ruangan 05 harus menjadi TERISI setelah pemindahan.');
        $this->assertEquals('Inception Super Movie', $room05Status['film_judul']);
        $this->assertEquals('Pelanggan Move Test', $room05Status['nama_pelanggan']);
        $this->assertGreaterThan(0, $room05Status['sisa_menit']);
    }

    /**
     * Test laporan riwayat penjualan menampilkan riwayat perpindahan room.
     */
    public function test_laporan_riwayat_detail_includes_room_histories(): void
    {
        $data = $this->setupActiveTransaction();
        $transaksi = $data['transaksi'];

        // Lakukan perpindahan room
        $this->actingAs($data['kasir'])
            ->postJson(route('kasir.move-room.save'), [
                'transaksi_id' => $transaksi->id,
                'room_tujuan_id' => $data['room05']->id,
                'alasan_pindah' => 'Pindah untuk upgrade studio',
            ])
            ->assertStatus(200);

        // Cek endpoint detail transaksi di laporan
        $admin = $this->getAdmin();
        $response = $this->actingAs($admin)
            ->getJson(route('admin.laporan.riwayat.detail', ['id' => $transaksi->id]));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $roomHistories = $response->json('transaksi.room_histories');
        $this->assertNotEmpty($roomHistories);
        $this->assertEquals('Ruangan 01', $roomHistories[0]['nama_room_asal']);
        $this->assertEquals('Ruangan 05', $roomHistories[0]['nama_room_tujuan']);
        $this->assertEquals('Pindah untuk upgrade studio', $roomHistories[0]['alasan_pindah']);
    }

    /**
     * Test validasi gagal jika pindah ke ruangan yang sama.
     */
    public function test_cannot_move_to_same_room(): void
    {
        $data = $this->setupActiveTransaction();

        $response = $this->actingAs($data['kasir'])
            ->postJson(route('kasir.move-room.save'), [
                'transaksi_id' => $data['transaksi']->id,
                'room_tujuan_id' => $data['room01']->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ruangan tujuan tidak boleh sama dengan ruangan asal.',
            ]);
    }

    /**
     * Test validasi gagal jika pindah ke ruangan yang sedang terpakai oleh transaksi aktif lain.
     */
    public function test_cannot_move_to_occupied_room(): void
    {
        $data = $this->setupActiveTransaction();

        // Buat transaksi lain yang sedang aktif di Ruangan 05
        Transaksi::create([
            'nomor_transaksi' => 'TRX-OCCUPIED-005',
            'kasir_shift_id' => $data['shift']->id,
            'toko_cabang_id' => $data['cabang']->id,
            'user_id' => $data['kasir']->id,
            'film_id' => $data['film']->id,
            'room_id' => $data['room05']->id,
            'tipe_pembayaran_id' => TipePembayaran::first()->id,
            'nama_pelanggan' => 'Tamu Lain di Room 5',
            'durasi_menit' => 120,
            'harga_film' => 60000,
            'harga_room' => 0,
            'subtotal_film_room' => 60000,
            'subtotal_cafe' => 0,
            'diskon' => 0,
            'total_bayar' => 60000,
            'uang_diterima' => 60000,
            'kembalian' => 0,
            'status' => 'selesai',
            'waktu_transaksi' => now()->subMinutes(10),
            'waktu_mulai_film' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($data['kasir'])
            ->postJson(route('kasir.move-room.save'), [
                'transaksi_id' => $data['transaksi']->id,
                'room_tujuan_id' => $data['room05']->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);

        $this->assertStringContainsString('saat ini sedang terpakai', $response->json('message'));
    }
}
