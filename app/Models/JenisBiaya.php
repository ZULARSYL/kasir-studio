<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBiaya extends Model
{
    use HasFactory;

    protected $table = 'jenis_biayas';

    protected $fillable = [
        'kategori',
    ];

    public function pemasukans()
    {
        return $this->hasMany(Pemasukan::class, 'jenis_biaya_id');
    }

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'jenis_biaya_id');
    }
}
