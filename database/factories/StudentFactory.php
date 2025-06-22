<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_number' => $this->faker->unique()->numerify('2024###'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->randomElement(['Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford']),
            'eircode' => $this->faker->regexify('[A-Z]\d{2}[A-Z]{2}\d{2}'),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'graduated', 'withdrawn']),
        ];
    }
}
