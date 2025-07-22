<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Amdin Pasar Niten',
            'email' => 'admin.niten@gmail.com',
            'password' => Hash::make('rahasia123'),
            'is_admin' => 1,
            'is_petugas_pasar' => 0
        ]);
    }
}