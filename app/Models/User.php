<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }


    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'kasir' => 'Kasir',
            default => ucfirst(str_replace('_', ' ', $this->role ?? '-')),
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-primary-subtle text-primary border-primary-subtle',
            'kasir' => 'bg-success-subtle text-success border-success-subtle',
            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };
    }

    public function getRoleIconAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bi-person-gear text-primary',
            'kasir' => 'bi-cash text-success',
            default => 'bi-person text-secondary',
        };
    }
}
