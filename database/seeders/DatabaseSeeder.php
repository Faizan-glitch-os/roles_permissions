<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin One',
            'email' => 'admin1@kaka.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Admin Two',
            'email' => 'admin2@kaka.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Employee One',
            'email' => 'employee1@kaka.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);
        User::create([
            'name' => 'Employee Two',
            'email' => 'employee2@kaka.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);
    }
}
