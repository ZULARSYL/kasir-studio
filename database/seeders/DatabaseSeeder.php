<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Dasar User
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Pressent Studio',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
        // 1. Akun Dasar User
        User::firstOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Pressent Studio',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Pressent Studio',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        );

        // 2. Jalankan Seeders Terstruktur
        $this->call([
            MasterDataSeeder::class,
            MenuCafeSeeder::class,
            FilmSeeder::class,
            PenjualanSeeder::class,
        ]);
    }
}
