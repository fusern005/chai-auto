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
        // ตรวจสอบว่ามี Admin นี้ในระบบหรือยัง
        if (!User::where('email', 'admin@test.com')->exists()) {
            User::create([
                'name' => 'IT Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'it_admin',
                'is_password_changed' => true,
            ]);
        }
    }
}
