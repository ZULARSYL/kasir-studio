<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HakAkses extends Model
{
    use HasFactory;

    protected $table = 'hak_akses';

    protected $fillable = [
        'target_key',
        'tipe',
        'role',
        'jabatan_id',
        'menus',
    ];

    protected $casts = [
        'menus' => 'array',
    ];

    /**
     * Relasi ke data Jabatan jika tipe adalah jabatan.
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Helper untuk membuat target key unik.
     */
    public static function makeTargetKey(string $tipe, ?string $role = null, ?int $jabatanId = null): string
    {
        if ($tipe === 'role') {
            return 'role:' . strtolower(trim($role ?? ''));
        }

        return 'jabatan:' . (int) $jabatanId;
    }
}
