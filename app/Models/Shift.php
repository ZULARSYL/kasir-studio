<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'nama_shift',
        'jam_mulai',
        'jam_selesai',
        'aturan_modal_awal',
        'urutan',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function kasirShifts(): HasMany
    {
        return $this->hasMany(KasirShift::class, 'shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif')->orderBy('urutan', 'asc');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('jam_mulai', 'asc');
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function isManualModal(): bool
    {
        return $this->aturan_modal_awal === 'manual';
    }

    public function isOtomatisModal(): bool
    {
        return $this->aturan_modal_awal === 'otomatis_sebelumnya';
    }

    /**
     * Format rentang jam jadwal operasional (misal: "11.00 – 16.00 WIB").
     */
    public function getFormattedJadwalAttribute(): string
    {
        $mulai = str_replace(':', '.', substr($this->jam_mulai, 0, 5));
        $selesai = str_replace(':', '.', substr($this->jam_selesai, 0, 5));
        return "{$mulai} – {$selesai} WIB";
    }

    /**
     * Label representasi aturan modal awal.
     */
    public function getAturanModalLabelAttribute(): string
    {
        return match ($this->aturan_modal_awal) {
            'otomatis_sebelumnya' => 'Otomatis Saldo Akhir Shift 1',
            default => 'Input Manual oleh Kasir',
        };
    }

    /**
     * Evaluasi deviasi waktu aktual terhadap jam mulai jadwal operasional (Rule 5).
     */
    public function hitungDeviasiWaktuMulai(?Carbon $waktuBuka = null): array
    {
        $waktu = $waktuBuka ? $waktuBuka->copy() : Carbon::now();
        $jamMulaiParts = explode(':', $this->jam_mulai);
        $targetMulai = $waktu->copy()->setTime((int) $jamMulaiParts[0], (int) ($jamMulaiParts[1] ?? 0), 0);

        $selisihMenit = (int) $targetMulai->diffInMinutes($waktu, false);

        if (abs($selisihMenit) <= 5) {
            $status = 'tepat_waktu';
            $keterangan = "Dibuka tepat waktu (Jadwal {$this->jam_mulai} WIB, Buka {$waktu->format('H:i')} WIB)";
            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
        } elseif ($selisihMenit < 0) {
            $menitAwal = abs($selisihMenit);
            $status = 'lebih_awal';
            $keterangan = "Dibuka {$menitAwal} menit lebih awal dari jadwal {$this->jam_mulai} WIB ({$waktu->format('H:i')} WIB)";
            $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
        } else {
            $status = 'terlambat';
            $keterangan = "Dibuka terlambat {$selisihMenit} menit dari jadwal {$this->jam_mulai} WIB ({$waktu->format('H:i')} WIB)";
            $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        }

        return [
            'status' => $status,
            'selisih_menit' => $selisihMenit,
            'keterangan' => $keterangan,
            'badge_class' => $badgeClass,
        ];
    }
}
