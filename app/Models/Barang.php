<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';

    protected $fillable = [
        'nama_barang',
        'menu_cafe_id',
    ];

    public function menuCafe()
    {
        return $this->belongsTo(MenuCafe::class, 'menu_cafe_id');
    }
}
