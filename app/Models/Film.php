<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    protected $table = 'films';

    protected $fillable = [
        'judul',
        'poster',
        'sinopsis',
        'durasi_menit',
        'harga_per_menit',
        'total_harga',
        'tahun_rilis',
        'sutradara',
        'usia_rating',
        'status',
    ];

    protected $casts = [
        'durasi_menit' => 'integer',
        'harga_per_menit' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'tahun_rilis' => 'integer',
    ];

    public function genres()
    {
        return $this->belongsToMany(GenreFilm::class, 'film_genre', 'film_id', 'genre_film_id');
    }

    public function getPosterUrlAttribute(): string
    {
        if ($this->poster && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->poster)) {
            return asset('storage/' . $this->poster);
        }

        // Return a default placeholder if no poster uploaded
        return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="420" viewBox="0 0 300 420" fill="%23f1f5f9"><rect width="300" height="420" fill="%23e2e8f0"/><text x="50%" y="48%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="16" font-weight="bold" fill="%2364748b">NO POSTER</text><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="12" fill="%2394a3b8">Pressent Studio</text></svg>';
    }

    public function getDurasiFormattedAttribute(): string
    {
        $menit = (int) $this->durasi_menit;
        $jam = floor($menit / 60);
        $sisaMenit = $menit % 60;

        if ($jam > 0) {
            return "{$menit} Menit ({$jam}j {$sisaMenit}m)";
        }

        return "{$menit} Menit";
    }

    public function getHargaPerMenitFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_per_menit, 0, ',', '.');
    }

    public function getTotalHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'tersedia') {
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Tersedia</span>';
        }

        return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-slash-circle me-1"></i>Tidak Tersedia</span>';
    }

    public function getUsiaRatingBadgeAttribute(): string
    {
        $color = match ($this->usia_rating) {
            'SU' => 'bg-info-subtle text-info border-info-subtle',
            '13+' => 'bg-primary-subtle text-primary border-primary-subtle',
            '17+' => 'bg-warning-subtle text-warning border-warning-subtle',
            '21+' => 'bg-danger-subtle text-danger border-danger-subtle',
            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };

        return "<span class=\"badge {$color} border px-2 py-1 font-monospace fw-bold\">{$this->usia_rating}</span>";
    }
}
