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
        // Create comprehensive staff users
        $users = [
            // Management
            [
                'name' => 'Dr. Patricia Collins',
                'email' => 'patricia.collins@theopencollege.ie',
                'role' => 'manager',
                'azure_id' => 'mgr-collins-'.Str::random(10),
            ],
            [
                'name' => 'James Morrison',
                'email' => 'james.morrison@theopencollege.ie',
                'role' => 'manager',
                'azure_id' => 'mgr-morrison-'.Str::random(10),
            ],

            // Student Services
            [
                'name' => 'Lisa Murphy',
                'email' => 'lisa.murphy@theopencollege.ie',
                'role' => 'student_services',
                'azure_id' => 'ss-murphy-'.Str::random(10),
            ],
            [
                'name' => 'David Walsh',
                'email' => 'david.walsh@theopencollege.ie',
                'role' => 'student_services',
                'azure_id' => 'ss-walsh-'.Str::random(10),
            ],
            [
                'name' => 'Emma O\'Brien',
                'email' => 'emma.obrien@theopencollege.ie',
                'role' => 'student_services',
                'azure_id' => 'ss-obrien-'.Str::random(10),
            ],

            // Business & Management Faculty
            [
                'name' => 'Prof. Michael Ryan',
                'email' => 'michael.ryan@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'prof-ryan-'.Str::random(10),
            ],
            [
                'name' => 'Dr. Sarah Kennedy',
                'email' => 'sarah.kennedy@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'dr-kennedy-'.Str::random(10),
            ],
            [
                'name' => 'Mark Thompson',
                'email' => 'mark.thompson@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'mark-thompson-'.Str::random(10),
            ],
            [
                'name' => 'Jennifer Clarke',
                'email' => 'jennifer.clarke@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'jen-clarke-'.Str::random(10),
            ],
            [
                'name' => 'Robert Fitzgerald',
                'email' => 'robert.fitzgerald@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'rob-fitzgerald-'.Str::random(10),
            ],

            // IT & Computing Faculty
            [
                'name' => 'Dr. Alan McCarthy',
                'email' => 'alan.mccarthy@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'dr-mccarthy-'.Str::random(10),
            ],
            [
                'name' => 'Catherine O\'Sullivan',
                'email' => 'catherine.osullivan@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'cat-osullivan-'.Str::random(10),
            ],
            [
                'name' => 'Daniel Hughes',
                'email' => 'daniel.hughes@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'dan-hughes-'.Str::random(10),
            ],
            [
                'name' => 'Michelle O\'Connor',
                'email' => 'michelle.oconnor@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'michelle-oconnor-'.Str::random(10),
            ],
            [
                'name' => 'Kevin Byrne',
                'email' => 'kevin.byrne@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'kevin-byrne-'.Str::random(10),
            ],

            // Digital Marketing Faculty
            [
                'name' => 'Sophie Walsh',
                'email' => 'sophie.walsh@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'sophie-walsh-'.Str::random(10),
            ],
            [
                'name' => 'Andrew Kelly',
                'email' => 'andrew.kelly@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'andrew-kelly-'.Str::random(10),
            ],
            [
                'name' => 'Rachel Murphy',
                'email' => 'rachel.murphy@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'rachel-murphy-'.Str::random(10),
            ],

            // Data Analytics Faculty
            [
                'name' => 'Dr. Thomas O\'Reilly',
                'email' => 'thomas.oreilly@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'dr-oreilly-'.Str::random(10),
            ],
            [
                'name' => 'Laura Doyle',
                'email' => 'laura.doyle@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'laura-doyle-'.Str::random(10),
            ],
            [
                'name' => 'Stephen Collins',
                'email' => 'stephen.collins@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'stephen-collins-'.Str::random(10),
            ],

            // General/Foundation Faculty
            [
                'name' => 'Mary O\'Donnell',
                'email' => 'mary.odonnell@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'mary-odonnell-'.Str::random(10),
            ],
            [
                'name' => 'Paul McGrath',
                'email' => 'paul.mcgrath@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'paul-mcgrath-'.Str::random(10),
            ],
            [
                'name' => 'Helen Casey',
                'email' => 'helen.casey@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'helen-casey-'.Str::random(10),
            ],
            [
                'name' => 'Brian Flanagan',
                'email' => 'brian.flanagan@theopencollege.ie',
                'role' => 'teacher',
                'azure_id' => 'brian-flanagan-'.Str::random(10),
            ],

            // Test users for development
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
                'name' => 'Test Teacher',
                'email' => 'teacher@test.local',
                'role' => 'teacher',
                'azure_id' => 'test-teacher-'.Str::random(10),
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
