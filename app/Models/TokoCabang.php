<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TokoCabang extends Model
{
    use HasFactory;

    protected $table = 'toko_cabang';

    protected $fillable = [
        'nama_cabang',
        'alamat',
        'kontak',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'toko_cabang_id');
    }

    public function pemasukans(): HasMany
    {
        return $this->hasMany(Pemasukan::class, 'toko_cabang_id');
    }

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'toko_cabang_id');
    }

    public function filmStokCabangs(): HasMany
    {
        return $this->hasMany(FilmStokCabang::class, 'toko_cabang_id');
    }

    public function films()
    {
        return $this->belongsToMany(Film::class, 'film_stok_cabangs', 'toko_cabang_id', 'film_id')
            ->withPivot('stok')
            ->withTimestamps();
    }
}
