<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmStokCabang extends Model
{
    use HasFactory;

    protected $table = 'film_stok_cabangs';

    protected $fillable = [
        'film_id',
        'toko_cabang_id',
        'stok',
    ];

    protected $casts = [
        'stok' => 'integer',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id');
    }

    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }
}
