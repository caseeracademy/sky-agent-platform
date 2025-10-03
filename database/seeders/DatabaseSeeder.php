<?php

namespace Database\Seeders;

use App\Models\SystemSettings;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default system settings
        SystemSettings::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Sky Blue Consulting',
                'company_email' => 'info@skyblue.com',
            ]
        );

        // Add this block to create a default Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Use a secure default password
                'role' => 'super_admin',
            ]
        );

        $this->call([
            UserSeeder::class,
            UniversitySeeder::class,
            StudentSeeder::class,
            ApplicationSeeder::class,
        ]);
    }
}
