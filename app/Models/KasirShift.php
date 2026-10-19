<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasirShift extends Model
{
    use HasFactory;

    protected $table = 'kasir_shifts';

    protected $fillable = [
        'user_id',
        'toko_cabang_id',
        'modal_awal',
        'total_penjualan',
        'total_tunai',
        'total_non_tunai',
        'total_transaksi',
        'modal_akhir',
        'status',
        'waktu_buka',
        'waktu_tutup',
        'catatan_buka',
        'catatan_tutup',
    ];

    protected $casts = [
        'modal_awal' => 'decimal:2',
        'total_penjualan' => 'decimal:2',
        'total_tunai' => 'decimal:2',
        'total_non_tunai' => 'decimal:2',
        'modal_akhir' => 'decimal:2',
        'total_transaksi' => 'integer',
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'kasir_shift_id');
    }

    /**
     * Dapatkan shift yang sedang aktif untuk kasir tertentu.
     */
    public static function getActiveShift($userId)
    {
        return static::where('user_id', $userId)
            ->where('status', 'buka')
            ->latest('waktu_buka')
            ->first();
    }
}
