<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\GenreFilm;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan genre sudah tersedia
        $genreAction = GenreFilm::firstOrCreate(['nama_genre' => 'Action']);
        $genreHorror = GenreFilm::firstOrCreate(['nama_genre' => 'Horror']);
        $genreComedy = GenreFilm::firstOrCreate(['nama_genre' => 'Comedy']);
        $genreDrama = GenreFilm::firstOrCreate(['nama_genre' => 'Drama']);
        $genreSciFi = GenreFilm::firstOrCreate(['nama_genre' => 'Sci-Fi']);
        $genreAnimation = GenreFilm::firstOrCreate(['nama_genre' => 'Animation']);
        $genreThriller = GenreFilm::firstOrCreate(['nama_genre' => 'Thriller']);

        $sampleFilms = [
            [
                'judul' => 'The Dark Knight',
                'sutradara' => 'Christopher Nolan',
                'tahun_rilis' => 2008,
                'usia_rating' => '13+',
                'durasi_menit' => 152,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Ketika ancaman yang dikenal sebagai Joker meneror Gotham City, Batman harus menerima salah satu ujian psikologis dan fisik terbesar untuk melawan ketidakadilan.',
                'status' => 'tersedia',
                'genres' => [$genreAction->id, $genreThriller->id, $genreDrama->id],
            ],
            [
                'judul' => 'Scary Movie Express',
                'sutradara' => 'Keenen Ivory Wayans',
                'tahun_rilis' => 2000,
                'usia_rating' => '17+',
                'durasi_menit' => 88,
                'limit_penggunaan' => 3,
                'harga_per_menit' => 450,
                'sinopsis' => 'Parodi kocak kombinasi film horor ikonik dan komedi satir remaja.',
                'status' => 'tersedia',
                'genres' => [$genreHorror->id, $genreComedy->id],
            ],
            [
                'judul' => 'Interstellar',
                'sutradara' => 'Christopher Nolan',
                'tahun_rilis' => 2014,
                'usia_rating' => '13+',
                'durasi_menit' => 169,
                'limit_penggunaan' => 5,
                'harga_per_menit' => 500,
                'sinopsis' => 'Tim penjelajah melakukan perjalanan melewati lubang cacing di luar angkasa dalam upaya memastikan kelangsungan hidup umat manusia.',
                'status' => 'tersedia',
                'genres' => [$genreSciFi->id, $genreDrama->id],
            ],
            [
                'judul' => 'Spirited Away',
                'sutradara' => 'Hayao Miyazaki',
                'tahun_rilis' => 2001,
                'usia_rating' => 'SU',
                'durasi_menit' => 125,
                'limit_penggunaan' => 4,
                'harga_per_menit' => 400,
                'sinopsis' => 'Seorang gadis muda yang masuk ke dunia magis roh setelah orang tuanya diubah menjadi babi oleh penyihir.',
                'status' => 'tersedia',
                'genres' => [$genreAnimation->id, $genreDrama->id],
            ],
        ];

        foreach ($sampleFilms as $data) {
            $genreIds = $data['genres'];
            unset($data['genres']);

            $data['total_harga'] = round($data['durasi_menit'] * $data['harga_per_menit'], 2);

            $film = Film::updateOrCreate(
                ['judul' => $data['judul']],
                $data
            );

            $film->genres()->sync($genreIds);

            $cabangs = \App\Models\TokoCabang::all();
            foreach ($cabangs as $cabang) {
                \App\Models\FilmStokCabang::firstOrCreate(
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
