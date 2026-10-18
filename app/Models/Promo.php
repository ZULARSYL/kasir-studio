<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'promos';

    protected $fillable = [
        'kode_voucher',
        'nama_promo',
        'nilai',
        'periode_berlaku',
        'status',
    ];

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'aktif' 
            ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Aktif</span>'
            : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Nonaktif</span>';
    }
}
