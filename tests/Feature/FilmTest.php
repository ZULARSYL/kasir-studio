<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\GenreFilm;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\ImageCompressionService;
use Carbon\Carbon;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilmTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        Storage::fake('public');
    }

    /**
     * Test halaman katalog film dapat diakses dengan sukses.
     */
    public function test_film_index_page_loads_successfully(): void
    {
        $admin = $this->getAdmin();
        $film = Film::create([
            'judul' => 'Film Detail Test',
            'sinopsis' => 'Sinopsis untuk detail film.',
            'durasi_menit' => 120,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'tahun_rilis' => 2024,
            'sutradara' => 'Sutradara Test',
            'usia_rating' => 'SU',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.film.index'));
        $response->assertOk();
        $response->assertSee('Katalog & List Film Studio', false)
            ->assertSee('modal-dialog-scrollable', false)
            ->assertSee('film-modal-form', false)
            ->assertSee('bi-eye', false)
            ->assertSee('modalDetailFilm' . $film->id, false)
            ->assertSee('Sinopsis untuk detail film.');
    }

    /**
     * Test pembuatan film dengan kalkulasi harga durasi per menit dan multi-genre.
     */
    public function test_create_film_with_duration_price_and_multi_genre(): void
    {
        $admin = $this->getAdmin();

        $genreHorror = GenreFilm::where('nama_genre', 'Horror')->first();
        $genreComedy = GenreFilm::where('nama_genre', 'Comedy')->first();

        // Durasi 120 menit, tarif Rp 500 per menit -> Total harus Rp 60.000
        $data = [
            'judul' => 'Komedi Horor Malam Jumat',
            'sutradara' => 'Joko Anwar',
            'tahun_rilis' => 2024,
            'usia_rating' => '17+',
            'durasi_menit' => 120,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'sinopsis' => 'Kisah lucu dan seram di sebuah bioskop tua.',
            'status' => 'tersedia',
            'genres' => [$genreHorror->id, $genreComedy->id],
        ];

        $response = $this->actingAs($admin)->post(route('admin.film.store'), $data);
        $response->assertRedirect(route('admin.film.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('films', [
            'judul' => 'Komedi Horor Malam Jumat',
            'durasi_menit' => 120,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
        ]);

        $film = Film::where('judul', 'Komedi Horor Malam Jumat')->first();
        $this->assertCount(2, $film->genres);
        $this->assertTrue($film->genres->contains($genreHorror));
        $this->assertTrue($film->genres->contains($genreComedy));
    }

    /**
     * Test upload cover film dengan autokompresi di bawah 500 KB.
     */
    public function test_upload_cover_image_autocompresses_to_under_500kb(): void
    {
        $admin = $this->getAdmin();
        $genreAction = GenreFilm::where('nama_genre', 'Action')->first();

        // Buat file gambar dummy asli (1000 x 1000 pixels)
        $realImage = UploadedFile::fake()->image('poster_large.jpg', 1200, 1600)->size(1500); // 1.5 MB (> 500KB)

        $data = [
            'judul' => 'Film Aksi Spektakuler',
            'durasi_menit' => 90,
            'limit_penggunaan' => 3,
            'harga_per_menit' => 600,
            'usia_rating' => '13+',
            'status' => 'tersedia',
            'genres' => [$genreAction->id],
            'poster' => $realImage,
        ];

        $response = $this->actingAs($admin)->post(route('admin.film.store'), $data);
        $response->assertRedirect(route('admin.film.index'));

        $film = Film::where('judul', 'Film Aksi Spektakuler')->first();
        $this->assertNotNull($film->poster);
        Storage::disk('public')->assertExists($film->poster);

        // Verifikasi ukuran berkas yang tersimpan di storage adalah <= 500 KB (512,000 bytes)
        $storedSize = strlen(Storage::disk('public')->get($film->poster));
        $this->assertLessThanOrEqual(512000, $storedSize, 'Ukuran poster harus otomatis dikompres ke <= 500KB');
    }

    /**
     * VALIDASI 1: Berkas palsu yang di-rename menjadi .jpg / non-image HARUS DITOLAK.
     */
    public function test_fake_renamed_image_file_is_strictly_rejected(): void
    {
        $admin = $this->getAdmin();
        $genreAction = GenreFilm::where('nama_genre', 'Action')->first();

        // Buat file teks yang sengaja di-rename menjadi .jpg
        $fakeRenamedFile = UploadedFile::fake()->createWithContent('malicious_script.jpg', '<?php echo "fake image payload"; ?>');

        $data = [
            'judul' => 'Film Percobaan Hacking File',
            'durasi_menit' => 100,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'usia_rating' => 'SU',
            'status' => 'tersedia',
            'genres' => [$genreAction->id],
            'poster' => $fakeRenamedFile,
        ];

        $response = $this->actingAs($admin)->post(route('admin.film.store'), $data);
        // Server harus menolak dan mengembalikan error validasi poster
        $response->assertSessionHasErrors('poster');
        $this->assertDatabaseMissing('films', ['judul' => 'Film Percobaan Hacking File']);
    }

    /**
     * VALIDASI 2: Opsi select yang dimanipulasi via Chrome DevTools / Inspect Element HARUS DITOLAK.
     */
    public function test_manipulated_select_options_via_devtools_are_rejected(): void
    {
        $admin = $this->getAdmin();
        $validGenre = GenreFilm::first();

        // 1. Uji manipulasi genre option ID lewat DevTools (misal ID 999999 tidak terdaftar)
        $tamperedGenreData = [
            'judul' => 'Film Uji Tamper Genre',
            'durasi_menit' => 100,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'usia_rating' => 'SU',
            'status' => 'tersedia',
            'genres' => [999999], // Nilai manipulasi devtools
        ];
        $response1 = $this->actingAs($admin)->post(route('admin.film.store'), $tamperedGenreData);
        $response1->assertSessionHasErrors('genres.0');

        // 2. Uji manipulasi status option lewat DevTools (misal value diubah menjadi 'status_palsu')
        $tamperedStatusData = [
            'judul' => 'Film Uji Tamper Status',
            'durasi_menit' => 100,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'usia_rating' => 'SU',
            'status' => 'status_palsu_devtools', // Nilai manipulasi devtools
            'genres' => [$validGenre->id],
        ];
        $response2 = $this->actingAs($admin)->post(route('admin.film.store'), $tamperedStatusData);
        $response2->assertSessionHasErrors('status');

        // 3. Uji manipulasi rating usia lewat DevTools (misal value diubah menjadi '99+')
        $tamperedUsiaData = [
            'judul' => 'Film Uji Tamper Rating Usia',
            'durasi_menit' => 100,
            'limit_penggunaan' => 5,
            'harga_per_menit' => 500,
            'usia_rating' => '99+', // Nilai manipulasi devtools
            'status' => 'tersedia',
            'genres' => [$validGenre->id],
        ];
        $response3 = $this->actingAs($admin)->post(route('admin.film.store'), $tamperedUsiaData);
        $response3->assertSessionHasErrors('usia_rating');
    }

    /**
     * Test update data film dan kalkulasi ulang harga serta genre.
     */
    public function test_update_film_and_recalculate_price(): void
    {
        $admin = $this->getAdmin();
        $genre1 = GenreFilm::where('nama_genre', 'Horror')->first();
        $genre2 = GenreFilm::where('nama_genre', 'Comedy')->first();

        $film = Film::create([
            'judul' => 'Film Original',
            'durasi_menit' => 60,
            'harga_per_menit' => 400,
            'total_harga' => 24000,
            'usia_rating' => 'SU',
            'status' => 'tersedia',
        ]);
        $film->genres()->sync([$genre1->id]);

        $updateData = [
            'judul' => 'Film Updated Title',
            'durasi_menit' => 90,
            'limit_penggunaan' => 4,
            'harga_per_menit' => 500, // 90 * 500 = 45000
            'usia_rating' => '13+',
            'status' => 'tersedia',
            'genres' => [$genre1->id, $genre2->id],
        ];

        $response = $this->actingAs($admin)->put(route('admin.film.update', $film->id), $updateData);
        $response->assertRedirect(route('admin.film.index'));

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'judul' => 'Film Updated Title',
            'durasi_menit' => 90,
            'limit_penggunaan' => 4,
            'harga_per_menit' => 500,
            'total_harga' => 45000,
            'usia_rating' => '13+',
        ]);

        $film->refresh();
        $this->assertCount(2, $film->genres);
    }

    /**
     * Test delete film dan pembersihan relasi.
     */
    public function test_delete_film_and_detach_genres(): void
    {
        $admin = $this->getAdmin();
        $genre = GenreFilm::first();

        $film = Film::create([
            'judul' => 'Film Akan Dihapus',
            'durasi_menit' => 60,
            'limit_penggunaan' => 2,
            'harga_per_menit' => 400,
            'total_harga' => 24000,
            'usia_rating' => 'SU',
            'status' => 'tersedia',
        ]);
        $film->genres()->sync([$genre->id]);

        $response = $this->actingAs($admin)->delete(route('admin.film.destroy', $film->id));
        $response->assertRedirect(route('admin.film.index'));

        $this->assertDatabaseMissing('films', ['id' => $film->id]);
        $this->assertDatabaseMissing('film_genre', ['film_id' => $film->id]);
    }

    /**
     * Test fitur limit penggunaan film seperti stok room bersamaan.
     */
    public function test_film_concurrent_room_limit_and_slot_availability(): void
    {
        $admin = $this->getAdmin();
        $cabang = TokoCabang::first();
        $metodeBayar = TipePembayaran::first();

        // 1. Buat Film dengan Limit Penggunaan = 2 Room Bersamaan
        $film = Film::create([
            'judul' => 'Avatar: The Way of Water',
            'durasi_menit' => 120,
            'limit_penggunaan' => 2,
            'harga_per_menit' => 500,
            'total_harga' => 60000,
            'status' => 'tersedia',
        ]);

        $room1 = Room::create(['toko_cabang_id' => $cabang->id, 'nama' => 'Studio 1', 'kapasitas' => 4]);
        $room2 = Room::create(['toko_cabang_id' => $cabang->id, 'nama' => 'Studio 2', 'kapasitas' => 4]);
        $room3 = Room::create(['toko_cabang_id' => $cabang->id, 'nama' => 'Studio 3', 'kapasitas' => 4]);

        $shift = KasirShift::create([
            'user_id' => $admin->id,
            'toko_cabang_id' => $cabang->id,
            'modal_awal' => 200000,
            'status' => 'buka',
            'waktu_buka' => now(),
        ]);

        // Kondisi Awal: Belum ada room yang menggunakan film
        $this->assertSame(0, $film->getActiveRoomsCount());
        $this->assertSame(2, $film->sisa_slot);
        $this->assertTrue($film->isAvailableForBooking());

        // Transaksi 1: Room 1 memesan film ini (Durasi 120 menit)
        $response1 = $this->actingAs($admin)->postJson(route('admin.kasir.checkout'), [
            'film_id' => $film->id,
            'room_id' => $room1->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
        ]);
        $response1->assertOk();
        $this->assertSame('success', $response1->json('status'));

        // Cek slot setelah 1 room aktif
        $this->assertSame(1, $film->getActiveRoomsCount());
        $this->assertSame(1, $film->sisa_slot);
        $this->assertTrue($film->isAvailableForBooking());

        // Transaksi 2: Room 2 memesan film yang sama (Limit 2 tercapai!)
        $response2 = $this->actingAs($admin)->postJson(route('admin.kasir.checkout'), [
            'film_id' => $film->id,
            'room_id' => $room2->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
        ]);
        $response2->assertOk();
        $this->assertSame('success', $response2->json('status'));

        // Cek status slot: sekarang 2 dari 2 terpakai, sisa 0 slot
        $this->assertSame(2, $film->getActiveRoomsCount());
        $this->assertSame(0, $film->sisa_slot);
        $this->assertFalse($film->isAvailableForBooking());

        // Transaksi 3: Room 3 mencoba memesan film yang sama saat slot penuh -> HARUS DITOLAK 422
        $response3 = $this->actingAs($admin)->postJson(route('admin.kasir.checkout'), [
            'film_id' => $film->id,
            'room_id' => $room3->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
        ]);
        $response3->assertStatus(422);
        $this->assertStringContainsString('sedang mencapai batas maksimal', $response3->json('message'));

        // Simulasi: Sesi Room 1 telah selesai menonton (mundurkan waktu transaksi room 1 lebih dari 120 menit yang lalu)
        $trx1 = Transaksi::where('room_id', $room1->id)->first();
        $trx1->update([
            'waktu_transaksi' => Carbon::now()->subMinutes(125),
        ]);

        // Cek kembali: Slot kembali bertambah 1 karena Room 1 sudah selesai!
        $this->assertSame(1, $film->getActiveRoomsCount());
        $this->assertSame(1, $film->sisa_slot);
        $this->assertTrue($film->isAvailableForBooking());

        // Sekarang Room 3 dapat memesan film tersebut dengan sukses!
        $response4 = $this->actingAs($admin)->postJson(route('admin.kasir.checkout'), [
            'film_id' => $film->id,
            'room_id' => $room3->id,
            'tipe_pembayaran_id' => $metodeBayar->id,
        ]);
        $response4->assertOk();
        $this->assertSame('success', $response4->json('status'));
    }
}
