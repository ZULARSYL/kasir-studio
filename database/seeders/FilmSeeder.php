<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\FilmStokCabang;
use App\Models\GenreFilm;
use App\Models\TokoCabang;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan genre lengkap sudah tersedia
        $genreAction = GenreFilm::firstOrCreate(['nama_genre' => 'Action']);
        $genreHorror = GenreFilm::firstOrCreate(['nama_genre' => 'Horror']);
        $genreComedy = GenreFilm::firstOrCreate(['nama_genre' => 'Comedy']);
        $genreDrama = GenreFilm::firstOrCreate(['nama_genre' => 'Drama']);
        $genreSciFi = GenreFilm::firstOrCreate(['nama_genre' => 'Sci-Fi']);
        $genreAnimation = GenreFilm::firstOrCreate(['nama_genre' => 'Animation']);
        $genreThriller = GenreFilm::firstOrCreate(['nama_genre' => 'Thriller']);
        $genreRomance = GenreFilm::firstOrCreate(['nama_genre' => 'Romance']);
        $genreAdventure = GenreFilm::firstOrCreate(['nama_genre' => 'Adventure']);
        $genreFantasy = GenreFilm::firstOrCreate(['nama_genre' => 'Fantasy']);

        // Koleksi Film-Film Terbaru Box Office (2023 - 2026) & Favorit Penonton
        $latestFilms = [
            [
                'judul' => 'Dune: Part Two',
                'sutradara' => 'Denis Villeneuve',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 166,
                'limit_penggunaan' => 6,
                'harga_per_menit' => 500,
                'sinopsis' => 'Paul Atreides bersatu dengan Chani dan suku Fremen di gurun Arrakis sambil membalas dendam terhadap para konspirator yang menghancurkan keluarganya, dihadapkan pada pilihan antara cinta dan takdir alam semesta.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreSciFi->id, $genreAdventure->id, $genreDrama->id],
            ],
            [
                'judul' => 'Deadpool & Wolverine',
                'sutradara' => 'Shawn Levy',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 128,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 550,
                'sinopsis' => 'Wade Wilson yang telah pensiun sebagai pembunuh bayaran ditarik oleh TVA untuk misi penyelamatan semesta. Demi misinya, ia terpaksa merekrut Wolverine varian lain yang penuh penyesalan.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreComedy->id, $genreSciFi->id],
            ],
            [
                'judul' => 'Inside Out 2',
                'sutradara' => 'Kelsey Mann',
                'tahun_rilis' => 2024,
                'usia_rating' => 'SU',
                'durasi_menit' => 96,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Markas Besar di pikiran Riley yang baru menginjak usia remaja mengalami renovasi mendadak untuk memberi ruang bagi emosi baru: Anxiety, Envy, Ennui, dan Embarrassment yang menggeser Joy dan kawan-kawan.',
                'status' => 'tersedia',
                'genres' => [$genreAnimation->id, $genreComedy->id, $genreDrama->id],
            ],
            [
                'judul' => 'Oppenheimer',
                'sutradara' => 'Christopher Nolan',
                'tahun_rilis' => 2023,
                'usia_rating' => '17+',
                'durasi_menit' => 180,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Kisah epik biografi J. Robert Oppenheimer, fisikawan teoretis Amerika yang memimpin Proyek Manhattan di Los Alamos untuk mengembangkan bom atom pertama selama Perang Dunia II.',
                'status' => 'tersedia',
                'genres' => [$genreDrama->id, $genreThriller->id],
            ],
            [
                'judul' => 'Agak Laen',
                'sutradara' => 'Muhadkly Acho',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 119,
                'limit_penggunaan' => 6,
                'harga_per_menit' => 500,
                'sinopsis' => 'Empat sekawan pengelola wahana rumah hantu di pasar malam panik luar biasa ketika seorang pengunjung calon politisi meninggal akibat serangan jantung, membuat mereka nekat menguburnya di dalam wahana.',
                'status' => 'tersedia',
                'genres' => [$genreComedy->id, $genreHorror->id],
            ],
            [
                'judul' => 'Siksa Kubur',
                'sutradara' => 'Joko Anwar',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 117,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Setelah kehilangan orang tuanya dalam aksi terorisme, Sita menghabiskan hidupnya untuk membuktikan bahwa siksa kubur tidak ada dengan mencari orang paling berdosa dan ikut terkubur bersamanya.',
                'status' => 'tersedia',
                'genres' => [$genreHorror->id, $genreDrama->id, $genreThriller->id],
            ],
            [
                'judul' => 'Spider-Man: Across the Spider-Verse',
                'sutradara' => 'Joaquim Dos Santos',
                'tahun_rilis' => 2023,
                'usia_rating' => 'SU',
                'durasi_menit' => 140,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Miles Morales melintasi Multiverse bersama Gwen Stacy dan bertemu dengan Spider-Society yang bertugas melindungi garis waktu, namun perselisihan terjadi mengenai bagaimana mengatasi takdir.',
                'status' => 'tersedia',
                'genres' => [$genreAnimation->id, $genreAction->id, $genreSciFi->id, $genreAdventure->id],
            ],
            [
                'judul' => 'Godzilla x Kong: The New Empire',
                'sutradara' => 'Adam Wingard',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 115,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Dua raksasa Titan legendaris, Godzilla dan Kong, terpaksa bersekutu demi melawan Skar King, ancaman penguasa bengis dari kedalaman Hollow Earth yang berniat menghancurkan permukaan bumi.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreSciFi->id, $genreAdventure->id],
            ],
            [
                'judul' => 'Exhuma',
                'sutradara' => 'Jang Jae-hyun',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 134,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Dua dukun muda, seorang ahli geomansi, dan pemulasara jenazah disewa oleh keluarga kaya untuk memindahkan makam leluhur mereka yang ternyata menyimpan kutukan jahat dan rahasia kelam sejarah.',
                'status' => 'tersedia',
                'genres' => [$genreHorror->id, $genreThriller->id, $genreDrama->id],
            ],
            [
                'judul' => 'Furiosa: A Mad Max Saga',
                'sutradara' => 'George Miller',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 148,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Kisah epik masa muda Imperator Furiosa saat diculik dari Green Place of Many Mothers oleh kelompok Biker Horde yang dipimpin oleh Warlord Dementus melintasi Wasteland yang kejam.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreSciFi->id, $genreThriller->id],
            ],
            [
                'judul' => 'Avatar: The Way of Water',
                'sutradara' => 'James Cameron',
                'tahun_rilis' => 2023,
                'usia_rating' => '13+',
                'durasi_menit' => 192,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Jake Sully dan Neytiri harus meninggalkan hutan Pandora dan mencari perlindungan bersama klan pesisir Metkayina ketika ancaman manusia kembali datang membawa dendam lama.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreSciFi->id, $genreAdventure->id, $genreDrama->id],
            ],
            [
                'judul' => 'John Wick: Chapter 4',
                'sutradara' => 'Chad Stahelski',
                'tahun_rilis' => 2023,
                'usia_rating' => '21+',
                'durasi_menit' => 169,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 550,
                'sinopsis' => 'John Wick menemukan cara untuk membebaskan dirinya dari High Table melalui duel terhormat, tetapi harus menghadapi pembunuh bayaran terbaik dari New York, Paris, Osaka, hingga Berlin.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreThriller->id],
            ],
            [
                'judul' => 'Vina: Sebelum 7 Hari',
                'sutradara' => 'Anggy Umbara',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 100,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Kisah tragis berdasarkan peristiwa nyata di Cirebon, di mana arwah mendiang Vina merasuki sahabatnya untuk mengungkap kebenaran atas pembunuhan keji geng motor yang direkayasa sebagai kecelakaan.',
                'status' => 'tersedia',
                'genres' => [$genreHorror->id, $genreDrama->id],
            ],
            [
                'judul' => 'Sekawan Limo',
                'sutradara' => 'Bayu Skak',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 112,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Lima sekawan mendaki Gunung Madyopuro dan melanggar mitos larangan gunung. Dalam perjalanan, mereka menyadari kejanggalan dan teror horor karena salah satu dari mereka bukanlah manusia.',
                'status' => 'tersedia',
                'genres' => [$genreComedy->id, $genreHorror->id],
            ],
            [
                'judul' => 'Twisters',
                'sutradara' => 'Lee Isaac Chung',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 122,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Mantan pemburu badai Kate Cooper bekerja sama dengan influencer pemburu tornado Tyler Owens untuk menguji inovasi sistem peringatan badai di tengah musim tornado paling destruktif di Oklahoma.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreAdventure->id, $genreThriller->id],
            ],
            [
                'judul' => 'Kung Fu Panda 4',
                'sutradara' => 'Mike Mitchell',
                'tahun_rilis' => 2024,
                'usia_rating' => 'SU',
                'durasi_menit' => 94,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Po ditunjuk menjadi Pemimpin Spiritual Lembah Damai dan harus menemukan serta melatih Dragon Warrior baru sembari menghadapi Chameleon, penyihir bengis yang mampu menyerap kekuatan para pendekar kuno.',
                'status' => 'tersedia',
                'genres' => [$genreAnimation->id, $genreAction->id, $genreComedy->id],
            ],
            [
                'judul' => 'Mission: Impossible – Dead Reckoning',
                'sutradara' => 'Christopher McQuarrie',
                'tahun_rilis' => 2023,
                'usia_rating' => '13+',
                'durasi_menit' => 163,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Ethan Hunt dan tim IMF memulai misi paling berbahaya: melacak senjata AI "The Entity" yang dapat mengancam peradaban dunia sebelum jatuh ke tangan penguasa korup.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreThriller->id, $genreAdventure->id],
            ],
            [
                'judul' => 'Ipar Adalah Maut',
                'sutradara' => 'Hanung Bramantyo',
                'tahun_rilis' => 2024,
                'usia_rating' => '17+',
                'durasi_menit' => 131,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Kehidupan rumah tangga Nisa dan Aris yang harmonis perlahan hancur ketika adik kandung Nisa yang kuliah tinggal bersama mereka dan memicu perselingkuhan yang menghancurkan keluarga.',
                'status' => 'tersedia',
                'genres' => [$genreDrama->id, $genreRomance->id],
            ],
            [
                'judul' => 'Kingdom of the Planet of the Apes',
                'sutradara' => 'Wes Ball',
                'tahun_rilis' => 2024,
                'usia_rating' => '13+',
                'durasi_menit' => 145,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 500,
                'sinopsis' => 'Beberapa generasi setelah era Caesar, seekor kera muda bernama Noa memulai perjalanan berbahaya bersama seorang gadis manusia liar yang membawanya menentang tirani kera baru.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreSciFi->id, $genreAdventure->id],
            ],
            [
                'judul' => 'Despicable Me 4',
                'sutradara' => 'Chris Renaud',
                'tahun_rilis' => 2024,
                'usia_rating' => 'SU',
                'durasi_menit' => 94,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Gru dan Lucy menyambut anggota keluarga baru, Gru Jr., sambil menghadapi musuh bebuyutan baru Maxime Le Mal yang kabur dari penjara dengan rencana balas dendam unik didukung Mega Minions.',
                'status' => 'tersedia',
                'genres' => [$genreAnimation->id, $genreComedy->id, $genreAdventure->id],
            ],
            [
                'judul' => 'The Dark Knight',
                'sutradara' => 'Christopher Nolan',
                'tahun_rilis' => 2008,
                'usia_rating' => '13+',
                'durasi_menit' => 152,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Ketika ancaman psikopat yang dikenal sebagai Joker meneror Gotham City, Batman harus menerima salah satu ujian psikologis dan fisik terbesar dalam hidupnya untuk melawan anarki.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreThriller->id, $genreDrama->id],
            ],
            [
                'judul' => 'Interstellar',
                'sutradara' => 'Christopher Nolan',
                'tahun_rilis' => 2014,
                'usia_rating' => '13+',
                'durasi_menit' => 169,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Di masa depan ketika bumi perlahan mati akibat wabah debu, mantan pilot NASA Cooper memimpin misi berbahaya melewati lubang cacing dekat Saturnus demi mencari rumah baru bagi umat manusia.',
                'status' => 'tersedia',
                'genres' => [$genreSciFi->id, $genreDrama->id, $genreAdventure->id],
            ],
        ];

        $cabangs = TokoCabang::all();

        foreach ($latestFilms as $data) {
            $genreIds = $data['genres'];
            unset($data['genres']);

            $data['total_harga'] = round($data['durasi_menit'] * $data['harga_per_menit'], 2);

            $film = Film::updateOrCreate(
                ['judul' => $data['judul']],
                $data
            );

            $film->genres()->sync($genreIds);

            foreach ($cabangs as $cabang) {
                FilmStokCabang::updateOrCreate(
                    [
                        'film_id' => $film->id,
                        'toko_cabang_id' => $cabang->id,
                    ],
                    [
                        'stok' => $film->limit_penggunaan ?? 5,
                    ]
                );
            }
        }
    }
}
