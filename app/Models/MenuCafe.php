<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCafe extends Model
{
    use HasFactory;

    protected $table = 'menu_cafe';

    protected $fillable = [
        'nama',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'menu_cafe_id');
    }
}
