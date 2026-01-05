<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => \Hash::make('password')] // Add a default password
        );

        $this->call(RolesTableSeeder::class);
        // $this->call(LmsSeeder::class); // Replaced by CourseContentSeeder for real content
        $this->call(CourseContentSeeder::class);
        $this->call(DemoUserSeeder::class);
        $this->call(DemoSeeder::class);

    }
}
