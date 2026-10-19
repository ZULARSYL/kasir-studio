<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenreFilm extends Model
{
    use HasFactory;

    protected $table = 'genre_films';

    protected $fillable = [
        'nama_genre',
    ];

    public function films()
    {
        return $this->belongsToMany(Film::class, 'film_genre', 'genre_film_id', 'film_id');
    }
}
