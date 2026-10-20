<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluarans';

    protected $fillable = [
        'tanggal',
        'toko_cabang_id',
        'jenis_biaya_id',
        'nominal',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Relasi ke Toko Cabang (Outlet)
     */
    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }

    /**
     * Relasi ke Master Jenis Biaya (Kategori Pengeluaran)
     */
    public function jenisBiaya(): BelongsTo
    {
        return $this->belongsTo(JenisBiaya::class, 'jenis_biaya_id');
    }

    /**
     * Relasi ke User / Admin pencatat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
