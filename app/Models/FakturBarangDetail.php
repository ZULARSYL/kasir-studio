<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturBarangDetail extends Model
{
    use HasFactory;

    protected $table = 'faktur_barang_details';

    protected $fillable = [
        'faktur_barang_id',
        'barang_id',
        'satuan_barang_id',
        'qty',
        'harga_awal',
        'harga_jual',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_awal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function fakturBarang()
    {
        return $this->belongsTo(FakturBarang::class, 'faktur_barang_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function satuanBarang()
    {
        return $this->belongsTo(SatuanBarang::class, 'satuan_barang_id');
    }

    public function getHargaAwalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_awal, 0, ',', '.');
    }

    public function getHargaJualFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    public function getSubtotalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
