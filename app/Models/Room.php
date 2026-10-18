<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'toko_cabang_id',
        'nama',
        'kapasitas',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }
}
