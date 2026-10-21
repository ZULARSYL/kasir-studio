<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'role',
        'username',
        'password',
        'toko_cabang_id',
        'pegawai_id',
        'jabatan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke master Toko Cabang (Penempatan Outlet).
     */
    public function tokoCabang(): BelongsTo
    {
        return $this->belongsTo(TokoCabang::class, 'toko_cabang_id');
    }

    /**
     * Relasi ke data Pegawai.
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /**
     * Relasi ke data Jabatan.
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin';
    }

    public function isKasir(): bool
    {
        return strtolower($this->role) === 'kasir';
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->jabatan) {
            return $this->jabatan->nama_jabatan;
        }

        return match (strtolower($this->role ?? '')) {
            'admin' => 'Admin',
            'kasir' => 'Kasir',
            default => ucfirst(str_replace('_', ' ', $this->role ?? '-')),
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match (strtolower($this->role ?? '')) {
            'admin' => 'bg-primary-subtle text-primary border-primary-subtle',
            'kasir' => 'bg-success-subtle text-success border-success-subtle',
            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };
    }

    public function getRoleIconAttribute(): string
    {
        return match (strtolower($this->role ?? '')) {
            'admin' => 'bi-person-gear text-primary',
            'kasir' => 'bi-cash text-success',
            default => 'bi-person text-secondary',
        };
    }

    /**
     * Cek apakah user memiliki hak akses ke menu atau submenu tertentu.
     */
    public function canAccessMenu(string $menuKey): bool
    {
        return \App\Services\HakAksesService::canAccess($this, $menuKey);
    }
}
