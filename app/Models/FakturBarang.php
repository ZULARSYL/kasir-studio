<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturBarang extends Model
{
    use HasFactory;

    protected $table = 'faktur_barangs';

    protected $fillable = [
        'nomor_faktur',
        'toko_cabang_id',
        'tanggal_faktur',
        'pemasok',
        'catatan',
        'total_nominal',
        'user_id',
    ];

    protected $casts = [
        'tanggal_faktur' => 'date',
        'total_nominal' => 'decimal:2',
    ];

    public function tokoCabang()
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(FakturBarangDetail::class, 'faktur_barang_id');
    }

    public function getTotalNominalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_nominal, 0, ',', '.');
    }

    public static function generateNomorFaktur(): string
    {
        $prefix = 'FKT-' . date('Ymd') . '-';
        $last = self::where('nomor_faktur', 'like', $prefix . '%')->latest('id')->first();

        if ($last) {
            $lastNumber = (int) substr($last->nomor_faktur, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
