<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiRoomHistory extends Model
{
    use HasFactory;

    protected $table = 'transaksi_room_histories';

    protected $fillable = [
        'transaksi_id',
        'room_asal_id',
        'room_tujuan_id',
        'nama_room_asal',
        'nama_room_tujuan',
        'user_id',
        'nama_user',
        'waktu_pindah',
        'durasi_berjalan_menit',
        'sisa_durasi_menit',
        'alasan_pindah',
    ];

    protected $casts = [
        'waktu_pindah' => 'datetime',
        'durasi_berjalan_menit' => 'integer',
        'sisa_durasi_menit' => 'integer',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function roomAsal(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_asal_id');
    }

    public function roomTujuan(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_tujuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
