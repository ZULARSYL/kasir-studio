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
}
