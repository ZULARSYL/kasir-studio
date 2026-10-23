<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\TemplateStruk;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TemplateStrukTest extends TestCase
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
            ['username' => 'admin_struk_tester'],
            [
                'name' => 'Admin Struk Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function getKasir(): User
    {
        $cabang = TokoCabang::first();
        return User::firstOrCreate(
            ['username' => 'kasir_struk_tester'],
            [
                'name' => 'Kasir Struk Tester',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabang->id,
            ]
        );
    }

    /**
     * Test guest tidak dapat mengakses pengaturan template struk.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.pengaturan.struk'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test admin dapat mengakses halaman template struk dan melihat form serta preview side-by-side.
     */
    public function test_admin_can_access_template_struk_page(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('admin.pengaturan.struk'));

        $response->assertOk();
        $response->assertSee('Pengaturan Template Struk');
        $response->assertSee('Form Pengaturan Template');
        $response->assertSee('Tampilan / Review Struk');
        $response->assertSee('LIVE REAL-TIME PREVIEW');
        $response->assertSee('Test Cetak Pratinjau');
    }

    /**
     * Test admin dapat memperbarui pengaturan template struk.
     */
    public function test_admin_can_update_template_struk(): void
    {
        $admin = $this->getAdmin();
        $template = TemplateStruk::getActiveTemplate();

        $response = $this->actingAs($admin)->put(route('admin.pengaturan.struk.update', $template->id), [
            'nama_template' => 'Template Custom Cinema VIP',
            'lebar_kertas' => '58mm',
            'font_size' => 'small',
            'nama_usaha' => 'PRESSENT VIP CINEMA',
            'sub_judul' => 'Luxury Private Cinema',
            'mode_cabang' => 'manual',
            'nama_cabang_manual' => 'Cabang Mall Pekanbaru',
            'alamat' => 'Jl. Jenderal Sudirman No. 123, Pekanbaru',
            'telepon' => '0811-9988-7766',
            'website_sosmed' => 'IG: @pressentvip',
            'pesan_header' => 'Nikmati Pengalaman Sinema Premium',
            'pesan_footer_1' => 'SAMPAI JUMPA KEMBALI',
            'pesan_footer_2' => 'Terima kasih atas kunjungan Anda.',
            'catatan_kaki' => 'Tiket tidak dapat dikembalikan.',
            'tampilkan_cabang' => 1,
            'tampilkan_alamat' => 1,
            'tampilkan_kontak' => 1,
            'tampilkan_no_nota' => 1,
            'tampilkan_waktu' => 1,
            'tampilkan_kasir' => 1,
            'tampilkan_pelanggan' => 1,
            'tampilkan_detail_room' => 1,
            'tampilkan_detail_penonton' => 1,
            'tampilkan_detail_cafe' => 1,
            'tampilkan_diskon' => 1,
            'tampilkan_metode_bayar' => 1,
            'tampilkan_kembalian' => 1,
            'tampilkan_barcode' => 1,
            'tampilkan_waktu_cetak' => 1,
            'is_aktif' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('template_struks', [
            'id' => $template->id,
            'nama_template' => 'Template Custom Cinema VIP',
            'nama_usaha' => 'PRESSENT VIP CINEMA',
            'lebar_kertas' => '58mm',
            'font_size' => 'small',
        ]);
    }

    /**
     * Test admin dapat membuat template struk baru dan mengaktifkannya.
     */
    public function test_admin_can_create_new_template_struk(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->post(route('admin.pengaturan.struk.store'), [
            'nama_template' => 'Template Promo Weekend 80mm',
            'lebar_kertas' => '80mm',
            'font_size' => 'medium',
            'nama_usaha' => 'PRESSENT WEEKEND SPECIAL',
            'sub_judul' => 'Special Weekend Promo',
            'mode_cabang' => 'otomatis',
            'alamat' => 'Pekanbaru',
            'telepon' => '0812-0000-1111',
            'is_aktif' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('template_struks', [
            'nama_template' => 'Template Promo Weekend 80mm',
            'nama_usaha' => 'PRESSENT WEEKEND SPECIAL',
            'is_aktif' => 1,
        ]);
    }

    /**
     * Test admin dapat menduplikat template struk.
     */
    public function test_admin_can_duplicate_template_struk(): void
    {
        $admin = $this->getAdmin();
        $template = TemplateStruk::getActiveTemplate();

        $response = $this->actingAs($admin)->post(route('admin.pengaturan.struk.duplicate', $template->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('template_struks', [
            'nama_template' => $template->nama_template . ' (Salinan)',
        ]);
    }

    /**
     * Test struk transaksi kasir menggunakan data dari template yang aktif.
     */
    public function test_kasir_struk_renders_with_active_template_settings(): void
    {
        $kasir = $this->getKasir();
        $cabang = TokoCabang::first();
        $room = Room::first();
        $film = Film::firstOrCreate(
            ['judul' => 'Avatar Test Struk'],
            ['durasi_menit' => 120, 'harga_per_menit' => 0, 'total_harga' => 75000, 'status' => 'tersedia', 'limit_penggunaan' => 5]
        );
        $tipe = TipePembayaran::first();

        // Siapkan template aktif custom
        $template = TemplateStruk::getActiveTemplate();
        $template->update([
            'nama_usaha' => 'STUDIO MEWAH PEKANBARU',
            'pesan_footer_1' => 'TERIMA KASIH SAHABAT PRESSENT',
        ]);

        // Buat shift kasir
        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        // Buat transaksi
        $transaksi = Transaksi::create([
            'nomor_transaksi' => 'TRX-TEST-STRUK-01',
            'kasir_shift_id' => $shift->id,
            'user_id' => $kasir->id,
            'toko_cabang_id' => $cabang->id,
            'room_id' => $room->id,
            'film_id' => $film->id,
            'tipe_pembayaran_id' => $tipe->id,
            'nama_pelanggan' => 'Pelanggan Struk Tester',
            'durasi_menit' => 120,
            'subtotal_film_room' => 75000,
            'subtotal_cafe' => 0,
            'total_bayar' => 75000,
            'uang_diterima' => 100000,
            'kembalian' => 25000,
            'status' => 'selesai',
            'waktu_transaksi' => now(),
        ]);

        $response = $this->actingAs($kasir)->get(route('kasir.struk', $transaksi->id));

        $response->assertOk();
        $response->assertSee('STUDIO MEWAH PEKANBARU');
        $response->assertSee('TERIMA KASIH SAHABAT PRESSENT');
        $response->assertSee($transaksi->nomor_transaksi);
        $response->assertSee('Pelanggan Struk Tester');
    }
}
