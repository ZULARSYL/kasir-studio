<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'nama_pegawai',
        'jabatan_id',
        'nomor_hp',
        'alamat',
    ];

    /**
     * Relasi ke data jabatan.
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Accessor alternatif untuk $pegawai->nama.
     */
    public function getNamaAttribute(): string
    {
        return $this->nama_pegawai;
    }
}
