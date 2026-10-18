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
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        );

        $this->call(MasterDataSeeder::class);
    }
}
