<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make(env('DEMO_PASSWORD', 'Password123!'));

        // Create admin if they don't exist
        User::firstOrCreate(
            ['email' => 'admin@learnsphere.com'],
            [
                'name' => 'Admin User',
                'password' => $password,
            ]
        )->assignRole('admin');

        // Create instructor if they don't exist
        User::firstOrCreate(
            ['email' => 'instructor@learnsphere.com'],
            [
                'name' => 'Instructor User',
                'password' => $password,
            ]
        )->assignRole('instructor');

        // Create student if they don't exist
        User::firstOrCreate(
            ['email' => 'student@learnsphere.com'],
            [
                'name' => 'Student User',
                'password' => $password,
            ]
        )->assignRole('student');
    }
}
