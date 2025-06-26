<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $this->command->info('Creating 200 test students...');

        for ($i = 1; $i <= 200; $i++) {
            // Generate student data
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = strtolower($firstName.'.'.$lastName.$i.'@student.test.local');

            // Create user account
            $user = User::create([
                'name' => $firstName.' '.$lastName,
                'email' => $email,
                'role' => 'student',
                'azure_id' => 'test-student-'.$i.'-'.Str::random(8),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'azure_groups' => [],
                'last_login_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);

            // Create student record
            $student = Student::create([
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-18 years'),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'county' => $faker->randomElement(['Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford', 'Kilkenny', 'Wexford', 'Kerry', 'Mayo', 'Donegal', 'Meath', 'Kildare', 'Wicklow', 'Louth', 'Tipperary', 'Clare', 'Sligo', 'Carlow', 'Laois', 'Offaly']),
                'eircode' => $faker->postcode(),
                'status' => $faker->randomElement(['active', 'active', 'active', 'active', 'active', 'inactive', 'graduated', 'withdrawn']), // Weight towards active
                'notes' => $faker->optional(0.3)->sentence(),
                'created_by' => 1,
            ]);

            // Link user to student
            $user->update(['student_id' => $student->id]);

            if ($i % 50 === 0) {
                $this->command->info("Created {$i} students...");
            }
        }

        $this->command->info('Successfully created 200 test students!');
        $this->command->info('Student IDs: S000001 to S000200');
        $this->command->info('Login credentials: email/password123');
    }

    /**
     * Generate a realistic Irish PPS number
     */
    private function generatePPSNumber(): string
    {
        $digits = '';
        for ($i = 0; $i < 7; $i++) {
            $digits .= rand(0, 9);
        }

        $letters = chr(rand(65, 90)).chr(rand(65, 90)); // Two random uppercase letters

        return $digits.$letters;
    }
}
