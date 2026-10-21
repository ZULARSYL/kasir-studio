<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = [
        'nomor_transaksi',
        'kasir_shift_id',
        'toko_cabang_id',
        'user_id',
        'film_id',
        'room_id',
        'tipe_pembayaran_id',
        'promo_id',
        'nama_pelanggan',
        'durasi_menit',
        'jumlah_penonton',
        'denda_penonton',
        'harga_film',
        'harga_room',
        'subtotal_film_room',
        'subtotal_cafe',
        'diskon',
        'total_bayar',
        'uang_diterima',
        'kembalian',
        'status',
        'waktu_transaksi',
        'catatan',
    ];

    protected $casts = [
        'durasi_menit' => 'integer',
        'jumlah_penonton' => 'integer',
        'denda_penonton' => 'decimal:2',
        'harga_film' => 'decimal:2',
        'harga_room' => 'decimal:2',
        'subtotal_film_room' => 'decimal:2',
        'subtotal_cafe' => 'decimal:2',
        'diskon' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'uang_diterima' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'waktu_transaksi' => 'datetime',
    ];

    public function kasirShift(): BelongsTo
    {
        return $this->belongsTo(KasirShift::class, 'kasir_shift_id');
    }

    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function tipePembayaran(): BelongsTo
    {
        return $this->belongsTo(TipePembayaran::class, 'tipe_pembayaran_id');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_id');
    }

    /**
     * Generate format nomor transaksi otomatis: TRX-YYYYMMDD-XXXX
     */
    public static function generateNomorTransaksi(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $lastTrx = static::where('nomor_transaksi', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTrx) {
            $lastNumber = (int) substr($lastTrx->nomor_transaksi, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
