<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiFilmHistory;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GantiFilmTest extends TestCase
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
            ['username' => 'kasir_ganti_film_tester'],
            [
                'name' => 'Kasir Ganti Film Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Helper untuk membuat transaksi aktif di room.
     */
    protected function setupActiveTransaction(): array
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $room = Room::first();
        $tipe = TipePembayaran::first();

        $filmAwal = Film::create([
            'judul' => 'Film Awal Avatar 1',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
            'limit_penggunaan' => 5,
        ]);

        $filmPengganti = Film::create([
            'judul' => 'Film Pengganti Batman 2',
            'durasi_menit' => 150,
            'harga_per_menit' => 600,
            'total_harga' => 90000,
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

        // Transaksi dimulai 30 menit yang lalu (masih berlangsung karena durasi 120 menit)
        $waktuMulai = now()->subMinutes(30);

        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-GANTI-FILM-001',
            'kasir_shift_id' => $shift->id,
            'toko_cabang_id' => $cabang->id,
            'user_id' => $kasir->id,
            'film_id' => $filmAwal->id,
            'room_id' => $room->id,
            'tipe_pembayaran_id' => $tipe->id,
            'nama_pelanggan' => 'Tamu Room VIP',
            'durasi_menit' => 120,
            'harga_film' => 60000,
            'harga_room' => 0,
            'subtotal_film_room' => 60000,
            'subtotal_cafe' => 25000,
            'diskon' => 0,
            'total_bayar' => 85000,
            'uang_diterima' => 100000,
            'kembalian' => 15000,
            'status' => 'selesai',
            'waktu_transaksi' => $waktuMulai,
            'waktu_mulai_film' => $waktuMulai,
            'catatan' => 'Pesanan awal',
        ]);

        return compact('kasir', 'cabang', 'room', 'filmAwal', 'filmPengganti', 'transaksi', 'shift');
    }

    /**
     * Syarat 1: Pergantian film hanya dapat dilakukan ketika room berstatus sedang berlangsung.
     */
    public function test_ganti_film_can_only_be_performed_when_room_is_occupied(): void
    {
        $kasir = $this->getKasir();
        $room = Room::first();

        // Belum ada transaksi di room ini
        $response = $this->actingAs($kasir)->getJson(route('kasir.ganti-film.data', $room->id));
        $response->assertStatus(404);
        $response->assertJson(['status' => 'error']);
    }

    /**
     * Syarat 2: Kasir dapat mengambil data penayangan room & daftar film pengganti yang aktif dan tersedia.
     */
    public function test_kasir_can_fetch_ganti_film_data_with_active_films(): void
    {
        $setup = $this->setupActiveTransaction();

        $response = $this->actingAs($setup['kasir'])->getJson(route('kasir.ganti-film.data', $setup['room']->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'room' => [
                'id' => $setup['room']->id,
                'nama' => $setup['room']->nama,
            ],
            'transaksi' => [
                'id' => $setup['transaksi']->id,
                'film_id' => $setup['filmAwal']->id,
                'film_judul' => $setup['filmAwal']->judul,
            ],
        ]);

        // Film pengganti harus ada di daftar films
        $films = $response->json('films');
        $this->assertNotEmpty($films);
        $filmIds = array_column($films, 'id');
        $this->assertContains($setup['filmPengganti']->id, $filmIds);
        // Film awal yang sedang diputar tidak boleh muncul di daftar pengganti
        $this->assertNotContains($setup['filmAwal']->id, $filmIds);
    }

    /**
     * Syarat 3: Sistem menghitung biaya film sebelumnya berdasarkan durasi pemutaran aktual dan selisih harga.
     */
    public function test_kalkulasi_ganti_film_calculates_actual_duration_and_difference(): void
    {
        $setup = $this->setupActiveTransaction();

        $response = $this->actingAs($setup['kasir'])->postJson(route('kasir.ganti-film.kalkulasi'), [
            'transaksi_id' => $setup['transaksi']->id,
            'film_baru_id' => $setup['filmPengganti']->id,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $data = $response->json('data');
        // Durasi pemutaran aktual sekitar 30 menit (karena waktu mulai 30 menit lalu)
        $this->assertGreaterThanOrEqual(29, $data['film_lama']['durasi_aktual_menit']);
        $this->assertLessThanOrEqual(31, $data['film_lama']['durasi_aktual_menit']);

        // Biaya terpakai film lama dihitung (30 menit x 500 = 15.000)
        $this->assertGreaterThan(0, $data['film_lama']['biaya_terpakai']);
        // Biaya film baru 90.000
        $this->assertEquals(90000, $data['film_baru']['harga']);

        // Selisih harus bernilai 'tambahan' karena film baru (90k) + biaya terpakai (15k) > biaya awal (60k)
        $this->assertEquals('tambahan', $data['kalkulasi']['tipe_selisih']);
    }

    /**
     * Syarat 4, 5, & 6:
     * - Waktu dicatat otomatis & riwayat film sebelumnya ditutup
     * - Riwayat baru dibuat
     * - Transaksi yang sama diperbarui (tidak membuat transaksi baru)
     * - Pesanan cafe dan data transaksi tetap utuh
     */
    public function test_simpan_ganti_film_updates_same_transaction_and_records_history(): void
    {
        $setup = $this->setupActiveTransaction();
        $transaksiId = $setup['transaksi']->id;

        $response = $this->actingAs($setup['kasir'])->postJson(route('kasir.ganti-film.simpan'), [
            'transaksi_id' => $transaksiId,
            'film_baru_id' => $setup['filmPengganti']->id,
            'alasan_ganti' => 'Permintaan tamu ingin film lebih seru',
            'tipe_pembayaran_tambahan_id' => $setup['transaksi']->tipe_pembayaran_id,
            'uang_diterima' => 50000,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        // 1. Pastikan transaksi ID tetap sama (tidak membuat transaksi baru - Rule 6)
        $this->assertEquals(1, Transaksi::count());
        $freshTrx = Transaksi::find($transaksiId);

        // Film di transaksi utama sudah diperbarui ke film pengganti
        $this->assertEquals($setup['filmPengganti']->id, $freshTrx->film_id);
        $this->assertEquals($setup['filmPengganti']->durasi_menit, $freshTrx->durasi_menit);
        $this->assertNotNull($freshTrx->waktu_mulai_film);

        // Pesanan kafe dan data pelanggan tetap utuh
        $this->assertEquals(25000, $freshTrx->subtotal_cafe);
        $this->assertEquals('Tamu Room VIP', $freshTrx->nama_pelanggan);
        $this->assertEquals($setup['room']->id, $freshTrx->room_id);

        // Catatan transaksi berisi log pergantian film
        $this->assertStringContainsString('Film Awal Avatar 1', $freshTrx->catatan);
        $this->assertStringContainsString('Film Pengganti Batman 2', $freshTrx->catatan);

        // 2. Pastikan riwayat pemutaran tercatat (Rule 4)
        $histories = TransaksiFilmHistory::where('transaksi_id', $transaksiId)->orderBy('id', 'asc')->get();
        $this->assertCount(2, $histories);

        // History 1: Film lama ditutup
        $historyLama = $histories->first();
        $this->assertEquals($setup['filmAwal']->id, $historyLama->film_id);
        $this->assertEquals('diganti', $historyLama->status);
        $this->assertNotNull($historyLama->waktu_selesai);
        $this->assertGreaterThan(0, $historyLama->durasi_aktual_menit);

        // History 2: Film baru berjalan
        $historyBaru = $histories->last();
        $this->assertEquals($setup['filmPengganti']->id, $historyBaru->film_id);
        $this->assertEquals('berjalan', $historyBaru->status);
        $this->assertEquals(0, $historyBaru->durasi_aktual_menit);
        $this->assertEquals($setup['filmPengganti']->durasi_menit, $historyBaru->durasi_menit_rencana);
    }

    /**
     * Test validasi limit penggunaan film pengganti saat ganti film.
     */
    public function test_ganti_film_respects_limit_penggunaan(): void
    {
        $setup = $this->setupActiveTransaction();

        // Buat film pengganti yang limit penggunaannya sudah penuh (limit: 1, terpakai: 1)
        $filmPenuh = Film::create([
            'judul' => 'Film Penuh Slot',
            'durasi_menit' => 100,
            'harga_per_menit' => 500,
            'total_harga' => 50000,
            'status' => 'tersedia',
            'limit_penggunaan' => 1,
        ]);

        $roomLain = Room::create([
            'nama' => 'Studio 2',
            'kapasitas' => 4,
            'toko_cabang_id' => $setup['cabang']->id,
        ]);

        // Buat transaksi lain yang sedang menggunakan filmPenuh ini
        Transaksi::create([
            'nomor_transaksi' => 'TRX-SLOT-FULL-01',
            'kasir_shift_id' => $setup['shift']->id,
            'toko_cabang_id' => $setup['cabang']->id,
            'user_id' => $setup['kasir']->id,
            'film_id' => $filmPenuh->id,
            'room_id' => $roomLain->id,
            'tipe_pembayaran_id' => $setup['transaksi']->tipe_pembayaran_id,
            'durasi_menit' => 100,
            'status' => 'selesai',
            'waktu_transaksi' => now(),
            'waktu_mulai_film' => now(),
        ]);

        $this->assertFalse($filmPenuh->isAvailableForBooking());

        // Coba ganti ke filmPenuh
        $response = $this->actingAs($setup['kasir'])->postJson(route('kasir.ganti-film.simpan'), [
            'transaksi_id' => $setup['transaksi']->id,
            'film_baru_id' => $filmPenuh->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => 'error']);
    }

    /**
     * Test alur UX baru: klik ganti film mengarahkan ke Menu Kasir (POS) dalam mode ganti_film.
     */
    public function test_pos_page_loads_in_ganti_film_mode_with_context(): void
    {
        $setup = $this->setupActiveTransaction();

        $response = $this->actingAs($setup['kasir'])->get(route('kasir.kasir', [
            'mode' => 'ganti_film',
            'room_id' => $setup['room']->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('gantiFilmContext');
        $context = $response->viewData('gantiFilmContext');
        $this->assertNotNull($context);
        $this->assertTrue($context['is_mode_ganti']);
        $this->assertEquals($setup['room']->id, $context['room_id']);
        $this->assertEquals($setup['transaksi']->id, $context['transaksi_id']);
        $this->assertEquals($setup['filmAwal']->id, $context['film_id_lama']);
        $this->assertGreaterThan(0, $context['biaya_aktual_terpakai']);

        // Assert view HTML renders banner Mode Pergantian Film
        $response->assertSee('Mode Pergantian Film');
        $response->assertSee($setup['room']->nama);
        $response->assertSee($setup['filmAwal']->judul);
        $response->assertSee('Konfirmasi Pergantian Film');
    }

    /**
     * Test validasi mode ganti film di POS jika room tidak sedang digunakan.
     */
    public function test_pos_page_rejects_ganti_film_mode_if_room_not_occupied(): void
    {
        $setup = $this->setupActiveTransaction();

        $roomKosong = Room::create([
            'nama' => 'Studio Kosong',
            'kapasitas' => 2,
            'toko_cabang_id' => $setup['cabang']->id,
        ]);

        $response = $this->actingAs($setup['kasir'])->get(route('kasir.kasir', [
            'mode' => 'ganti_film',
            'room_id' => $roomKosong->id,
        ]));

        $response->assertOk();
        $response->assertSessionHas('error');
        $this->assertNull($response->viewData('gantiFilmContext'));
    }
}
