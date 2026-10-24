<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiFilmHistory extends Model
{
    use HasFactory;

    protected $table = 'transaksi_film_histories';

    protected $fillable = [
        'transaksi_id',
        'film_id',
        'room_id',
        'judul_film',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit_rencana',
        'durasi_aktual_menit',
        'tarif_per_menit',
        'biaya_film',
        'status',
        'alasan_ganti',
        'selisih_biaya',
        'tipe_selisih',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_menit_rencana' => 'integer',
        'durasi_aktual_menit' => 'integer',
        'tarif_per_menit' => 'decimal:2',
        'biaya_film' => 'decimal:2',
        'selisih_biaya' => 'decimal:2',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
