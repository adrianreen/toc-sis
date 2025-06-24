<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder; // Assuming your User model is in App\Models
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void // Added the run() method
    {
        // Create test users for different roles
        $users = [
            [
                'name' => 'Test Manager',
                'email' => 'manager@test.local',
                'role' => 'manager',
                'azure_id' => 'test-manager-'.Str::random(10),
            ],
            [
                'name' => 'Test Student Services',
                'email' => 'studentservices@test.local',
                'role' => 'student_services',
                'azure_id' => 'test-ss-'.Str::random(10),
            ],
            [
                'name' => 'John Teacher',
                'email' => 'john.teacher@test.local',
                'role' => 'teacher',
                'azure_id' => 'test-teacher1-'.Str::random(10),
            ],
            [
                'name' => 'Sarah Teacher',
                'email' => 'sarah.teacher@test.local',
                'role' => 'teacher',
                'azure_id' => 'test-teacher2-'.Str::random(10),
            ],
            [
                'name' => 'Mike Teacher',
                'email' => 'mike.teacher@test.local',
                'role' => 'teacher',
                'azure_id' => 'test-teacher3-'.Str::random(10),
            ],
            [
                'name' => 'Test Student',
                'email' => 'student@test.local',
                'role' => 'student',
                'azure_id' => 'test-student-'.Str::random(10),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], // Conditions to find existing user
                [                               // Values to create or update with
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'azure_id' => $userData['azure_id'],
                    'password' => Hash::make('password123'), // Make sure to change this for production
                    'email_verified_at' => now(),
                    'azure_groups' => [], // Ensure 'azure_groups' is fillable and cast to array/json in User model if needed
                    'last_login_at' => now(),
                ]
            );
        }

        $this->command->info('Development users created successfully!');
        $this->command->table(
            ['Name', 'Email', 'Role'],
            collect($users)->map(fn ($user) => [$user['name'], $user['email'], $user['role']])->toArray()
        );
    } // Closing brace for the run() method
} // Closing brace for the UserSeeder class
