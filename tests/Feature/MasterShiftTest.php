<?php

namespace Tests\Feature;

use App\Models\KasirShift;
use App\Models\Shift;
use App\Models\TokoCabang;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterShiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    protected function getAdmin(): User
    {
        $cabang = TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'admin_shift_test'],
            [
                'name' => 'Admin Shift Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    protected function getKasir(string $username = 'kasir_shift_1'): User
    {
        $cabang = TokoCabang::first();
        return User::firstOrCreate(
            ['username' => $username],
            [
                'name' => 'Kasir ' . ucfirst($username),
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test: Admin dapat mengakses Master Data Shift dan melihat Shift 1 & Shift 2 default.
     * Aturan 1: Shift 1 jadwal 11.00 - 16.00 WIB
     * Aturan 2: Shift 2 jadwal 16.00 - 00.00 WIB
     */
    public function test_admin_can_view_master_shift_index_page_and_default_shifts(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.master.shift.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Data Shift');
        $response->assertSee('Operasional Kasir');
        $response->assertSee('Shift 1');
        $response->assertSee('Shift 2');
        $response->assertSee('11:00');
        $response->assertSee('16:00');
        $response->assertSee('00:00');

        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();
        $this->assertNotNull($shift1);
        $this->assertEquals('11:00', substr($shift1->jam_mulai, 0, 5));
        $this->assertEquals('16:00', substr($shift1->jam_selesai, 0, 5));
        $this->assertEquals('manual', $shift1->aturan_modal_awal);

        $shift2 = Shift::where('nama_shift', 'Shift 2')->first();
        $this->assertNotNull($shift2);
        $this->assertEquals('16:00', substr($shift2->jam_mulai, 0, 5));
        $this->assertEquals('00:00', substr($shift2->jam_selesai, 0, 5));
        $this->assertEquals('otomatis_sebelumnya', $shift2->aturan_modal_awal);
    }

    /**
     * Test: Admin dapat menambah master shift baru.
     */
    public function test_admin_can_create_new_shift(): void
    {
        $admin = $this->getAdmin();

        $payload = [
            'nama_shift' => 'Shift Malam Khusus',
            'jam_mulai' => '00:00',
            'jam_selesai' => '06:00',
            'aturan_modal_awal' => 'manual',
            'urutan' => 3,
            'status' => 'aktif',
            'keterangan' => 'Shift tambahan operasional event khusus',
        ];

        $response = $this->actingAs($admin)->post(route('admin.master.shift.store'), $payload);
        $response->assertRedirect(route('admin.master.shift.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', [
            'nama_shift' => 'Shift Malam Khusus',
            'aturan_modal_awal' => 'manual',
        ]);

        $created = Shift::where('nama_shift', 'Shift Malam Khusus')->first();
        $this->assertNotNull($created);
        $this->assertEquals('00:00', substr($created->jam_mulai, 0, 5));
        $this->assertEquals('06:00', substr($created->jam_selesai, 0, 5));
    }

    /**
     * Test: Admin dapat mengubah master shift.
     */
    public function test_admin_can_update_shift(): void
    {
        $admin = $this->getAdmin();
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();

        $updatePayload = [
            'nama_shift' => 'Shift 1 Pagi-Siang',
            'jam_mulai' => '10:30',
            'jam_selesai' => '16:00',
            'aturan_modal_awal' => 'manual',
            'urutan' => 1,
            'status' => 'aktif',
            'keterangan' => 'Shift pagi diperbarui',
        ];

        $response = $this->actingAs($admin)->put(route('admin.master.shift.update', $shift1->id), $updatePayload);
        $response->assertRedirect(route('admin.master.shift.index'));
        $response->assertSessionHas('success');

        $shift1->refresh();
        $this->assertEquals('Shift 1 Pagi-Siang', $shift1->nama_shift);
        $this->assertEquals('10:30', substr($shift1->jam_mulai, 0, 5));
    }

    /**
     * Test: Admin dapat menonaktifkan dan mengaktifkan status shift.
     */
    public function test_admin_can_toggle_shift_status(): void
    {
        $admin = $this->getAdmin();
        $shift = Shift::create([
            'nama_shift' => 'Shift Test Toggle',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'aturan_modal_awal' => 'manual',
            'urutan' => 10,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.master.shift.toggle-status', $shift->id));
        $response->assertRedirect();
        $this->assertEquals('nonaktif', $shift->fresh()->status);

        $response = $this->actingAs($admin)->post(route('admin.master.shift.toggle-status', $shift->id));
        $response->assertRedirect();
        $this->assertEquals('aktif', $shift->fresh()->status);
    }

    /**
     * Test: Admin dapat menghapus shift yang belum digunakan pada riwayat kasir_shifts.
     */
    public function test_admin_can_delete_unused_shift(): void
    {
        $admin = $this->getAdmin();
        $shift = Shift::create([
            'nama_shift' => 'Shift Temporary Delete',
            'jam_mulai' => '07:00',
            'jam_selesai' => '10:00',
            'aturan_modal_awal' => 'manual',
            'urutan' => 99,
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.master.shift.destroy', $shift->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }

    /**
     * Test: Aturan 1 & Aturan 3 - Kasir membuka Shift 1 dengan modal awal manual.
     */
    public function test_rule_1_and_3_kasir_can_open_shift_1_with_manual_modal_awal(): void
    {
        $kasir1 = $this->getKasir('kasir_shift1_user');
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();
        $cabang = TokoCabang::first();

        $response = $this->actingAs($kasir1)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift1->id,
            'modal_awal' => 250000,
            'catatan_buka' => 'Laci kasir terisi pecahan 50rb dan 20rb',
        ]);

        $response->assertRedirect(route('kasir.kasir'));
        $response->assertSessionHas('success');

        $activeShift = KasirShift::getActiveShift($kasir1->id);
        $this->assertNotNull($activeShift);
        $this->assertEquals($shift1->id, $activeShift->shift_id);
        $this->assertEquals('Shift 1', $activeShift->nama_shift);
        $this->assertEquals(250000, (float) $activeShift->modal_awal);
        $this->assertEquals('buka', $activeShift->status);
        $this->assertEquals($cabang->id, $activeShift->toko_cabang_id);
    }

    /**
     * Test: Aturan 7 - Validasi pergantian shift:
     * Shift berikutnya TIDAK BOLEH dimulai sebelum proses tutup kasir shift sebelumnya selesai.
     */
    public function test_rule_7_next_shift_cannot_be_started_before_previous_shift_is_closed(): void
    {
        $cabang = TokoCabang::first();
        $kasir1 = $this->getKasir('kasir_satu');
        $kasir2 = $this->getKasir('kasir_dua');
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();
        $shift2 = Shift::where('nama_shift', 'Shift 2')->first();

        // 1. Kasir 1 membuka Shift 1
        $this->actingAs($kasir1)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift1->id,
            'modal_awal' => 300000,
        ]);

        $this->assertNotNull(KasirShift::getActiveShiftForCabang($cabang->id));

        // 2. Kasir 2 mencoba membuka Shift 2 di cabang yang sama sebelum Kasir 1 tutup kasir
        $response = $this->actingAs($kasir2)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift2->id,
            'modal_awal' => 0,
        ]);

        // Verifikasi ditolak (redirect kembali dengan flash error)
        $response->assertSessionHas('error');
        $errorMsg = session('error');
        $this->assertStringContainsString('Shift berikutnya tidak dapat dimulai sebelum proses tutup kasir shift sebelumnya selesai', $errorMsg);

        // Pastikan Kasir 2 belum punya shift aktif
        $this->assertNull(KasirShift::getActiveShift($kasir2->id));
    }

    /**
     * Test: Aturan 4 - Modal awal Shift 2 otomatis mengambil nominal saldo akhir Shift 1.
     */
    public function test_rule_4_shift_2_automatically_takes_modal_akhir_from_shift_1_closing(): void
    {
        $cabang = TokoCabang::first();
        $kasir1 = $this->getKasir('kasir_pagi');
        $kasir2 = $this->getKasir('kasir_sore');
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();
        $shift2 = Shift::where('nama_shift', 'Shift 2')->first();

        // 1. Kasir 1 buka Shift 1 dengan modal awal 200.000
        $this->actingAs($kasir1)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift1->id,
            'modal_awal' => 200000,
        ]);

        $shiftKasir1 = KasirShift::getActiveShift($kasir1->id);

        // 2. Kasir 1 melakukan TUTUP KASIR dengan saldo fisik akhir 575.000
        $closeResponse = $this->actingAs($kasir1)->post(route('kasir.shift.tutup'), [
            'modal_akhir' => 575000,
            'catatan_tutup' => 'Serah terima ke kasir sore lancar.',
        ]);
        $closeResponse->assertRedirect(route('kasir.kasir'));
        $closeResponse->assertSessionHas('success');

        $shiftKasir1->refresh();
        $this->assertEquals('tutup', $shiftKasir1->status);
        $this->assertEquals(575000, (float) $shiftKasir1->modal_akhir);

        // 3. Kasir 2 membuka Shift 2 (tanpa input modal manual, sistem otomatis ambil dari saldo akhir Shift 1)
        $responseShift2 = $this->actingAs($kasir2)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift2->id,
            'modal_awal' => 0, // diisi 0 / default, sistem akan override dengan modal_akhir Shift 1
        ]);
        $responseShift2->assertRedirect(route('kasir.kasir'));
        $responseShift2->assertSessionHas('success');

        $activeShift2 = KasirShift::getActiveShift($kasir2->id);
        $this->assertNotNull($activeShift2);
        $this->assertEquals($shift2->id, $activeShift2->shift_id);
        $this->assertEquals('Shift 2', $activeShift2->nama_shift);
        // Assert: Modal awal Shift 2 PERSIS sama dengan modal akhir Shift 1
        $this->assertEquals(575000, (float) $activeShift2->modal_awal);
        // Assert: Relasi pergantian shift tercatat
        $this->assertEquals($shiftKasir1->id, $activeShift2->pergantian_dari_kasir_shift_id);
    }

    /**
     * Test: Aturan 5 - Jadwal shift fleksibel:
     * Kasir dapat membuka shift dengan jam kedatangan aktual, dan deviasi waktu tercatat di sistem.
     */
    public function test_rule_5_flexible_operational_hours_record_time_deviation(): void
    {
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first(); // 11:00 - 16:00

        // Uji helper hitungDeviasiWaktuMulai:
        // Jika buka pukul 11:20 (terlambat 20 menit)
        $waktuBukaTerlambat = Carbon::today()->setTime(11, 20, 0);
        $deviasiTerlambat = $shift1->hitungDeviasiWaktuMulai($waktuBukaTerlambat);
        $this->assertIsArray($deviasiTerlambat);
        $this->assertStringContainsString('terlambat 20 menit', $deviasiTerlambat['keterangan']);

        // Jika buka pukul 10:45 (lebih awal 15 menit)
        $waktuBukaAwal = Carbon::today()->setTime(10, 45, 0);
        $deviasiAwal = $shift1->hitungDeviasiWaktuMulai($waktuBukaAwal);
        $this->assertIsArray($deviasiAwal);
        $this->assertStringContainsString('15 menit lebih awal', $deviasiAwal['keterangan']);

        // Jika tepat waktu pukul 11:00
        $waktuBukaTepat = Carbon::today()->setTime(11, 0, 0);
        $deviasiTepat = $shift1->hitungDeviasiWaktuMulai($waktuBukaTepat);
        $this->assertIsArray($deviasiTepat);
        $this->assertStringContainsString('tepat waktu', $deviasiTepat['keterangan']);
    }

    /**
     * Test: Endpoint AJAX /kasir/shift/info mengembalikan status ketersediaan shift dan modal otomatis.
     */
    public function test_shift_info_endpoint_returns_availability_and_auto_modal(): void
    {
        $cabang = TokoCabang::first();
        $kasir1 = $this->getKasir('kasir_ajax_1');
        $kasir2 = $this->getKasir('kasir_ajax_2');
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();
        $shift2 = Shift::where('nama_shift', 'Shift 2')->first();

        // 1. Kasir 1 buka Shift 1
        $this->actingAs($kasir1)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift1->id,
            'modal_awal' => 200000,
        ]);

        // Cek endpoint dari Kasir 2: can_start harus FALSE karena Kasir 1 belum tutup shift
        $response = $this->actingAs($kasir2)->getJson(route('kasir.shift.info', [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift2->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'can_start' => false,
        ]);
        $this->assertNotNull($response->json('active_shift_warning'));

        // 2. Kasir 1 tutup Shift 1 dengan modal akhir 450.000
        $this->actingAs($kasir1)->post(route('kasir.shift.tutup'), [
            'modal_akhir' => 450000,
        ]);

        // Cek endpoint dari Kasir 2 setelah tutup shift:
        // can_start harus TRUE, dan modal_awal_otomatis harus 450.000
        $responseAfterClose = $this->actingAs($kasir2)->getJson(route('kasir.shift.info', [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift2->id,
        ]));

        $responseAfterClose->assertStatus(200);
        $responseAfterClose->assertJson([
            'status' => 'success',
            'can_start' => true,
            'modal_awal_otomatis' => 450000,
        ]);
        $this->assertStringContainsString('450.000', $responseAfterClose->json('modal_awal_otomatis_format'));
    }

    /**
     * Test: Aturan 6 - Integrasi Master Shift dengan menu Kasir (POS).
     */
    public function test_rule_6_active_shift_integrated_with_kasir_pos_menu(): void
    {
        $cabang = TokoCabang::first();
        $kasir = $this->getKasir('kasir_pos_tester');
        $shift1 = Shift::where('nama_shift', 'Shift 1')->first();

        // Kasir buka shift
        $this->actingAs($kasir)->post(route('kasir.shift.buka'), [
            'toko_cabang_id' => $cabang->id,
            'shift_id' => $shift1->id,
            'modal_awal' => 200000,
        ]);

        // Kasir mengunjungi POS
        $response = $this->actingAs($kasir)->get(route('kasir.kasir'));
        $response->assertStatus(200);
        $response->assertSee('Menu Kasir (POS)');
        $response->assertSee('Shift Aktif');
        $response->assertSee('Shift 1');
        $response->assertSee('Rp 200.000');
    }
}
