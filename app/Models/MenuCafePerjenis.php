<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCafePerjenis extends Model
{
    use HasFactory;

    protected $table = 'menu_cafe_jenis';

    protected $fillable = [
        'nama',
    ];
}
