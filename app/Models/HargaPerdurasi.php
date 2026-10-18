<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaPerdurasi extends Model
{
    use HasFactory;

    protected $table = 'harga_perdurasi';

    protected $fillable = [
        'nama',
        'durasi',
        'kalkulasi_harga',
    ];

    protected $casts = [
        'kalkulasi_harga' => 'decimal:2',
    ];

    public function getKalkulasiHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->kalkulasi_harga ?? 0, 0, ',', '.');
    }

    public function getDurasiMenitAttribute(): ?int
    {
        $durasi = trim((string) $this->durasi);

        if (preg_match('/\((\d+)\s*menit\)/i', $durasi, $matches)) {
            return (int) $matches[1];
        }

        preg_match('/(\d+)\s*jam/i', $durasi, $jamMatches);
        preg_match('/(\d+)\s*menit/i', $durasi, $menitMatches);

        if ($jamMatches || $menitMatches) {
            return ((int) ($jamMatches[1] ?? 0) * 60) + (int) ($menitMatches[1] ?? 0);
        }

        return is_numeric($durasi) ? (int) $durasi : null;
    }

    public function getHargaPerMenitAttribute(): ?float
    {
        $durasiMenit = $this->durasi_menit;

        if (!$durasiMenit || $durasiMenit < 1) {
            return null;
        }

        return (float) $this->kalkulasi_harga / $durasiMenit;
    }

    public function getHargaPerMenitFormatAttribute(): string
    {
        $hargaPerMenit = $this->harga_per_menit;

        return $hargaPerMenit === null
            ? '-'
            : 'Rp ' . number_format($hargaPerMenit, 2, ',', '.');
    }
}
