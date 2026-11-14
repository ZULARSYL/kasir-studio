<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCafe extends Model
{
    use HasFactory;

    protected $table = 'menu_cafe';

    protected $fillable = [
        'nama',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'menu_cafe_id');
    }

    /**
     * Dapatkan icon FontAwesome / Bootstrap Icons berdasarkan kategori menu cafe:
     * - Makanan => Fork knife (fa-solid fa-utensils / bi-fork-knife)
     * - Minuman => Cup straw (bi bi-cup-straw)
     * - Snack   => Cookie (fa-solid fa-cookie / bi-cookie)
     */
    public function getIconAttribute(): string
    {
        return self::getIconForCategory($this->nama);
    }

    /**
     * Helper statis untuk mapping icon kategori cafe
     */
    public static function getIconForCategory(?string $categoryName): string
    {
        $name = strtolower(trim($categoryName ?? ''));
        if (str_contains($name, 'makan')) {
            return 'fa-solid fa-bowl-food';
        }
        if (str_contains($name, 'minum')) {
            return 'fa-solid fa-mug-hot';
        }
        if (str_contains($name, 'snack') || str_contains($name, 'kue') || str_contains($name, 'camilan') || str_contains($name, 'cookie')) {
            return 'fa-solid fa-burger';
        }
        return 'fa-solid fa-cookie';
    }

    /**
     * Helper statis untuk warna badge kategori cafe
     */
    public static function getBadgeColorForCategory(?string $categoryName): string
    {
        $name = strtolower(trim($categoryName ?? ''));
        if (str_contains($name, 'makan')) {
            return 'warning';
        }
        if (str_contains($name, 'minum')) {
            return 'info';
        }
        if (str_contains($name, 'snack') || str_contains($name, 'kue') || str_contains($name, 'camilan') || str_contains($name, 'cookie')) {
            return 'primary';
        }
        return 'secondary';
    }
}
