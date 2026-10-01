<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->regexify('REG-\d{4}-\d{4}'),
            'college' => 'College of Engineering',
            'school_faculty' => 'School of Engineering',
            'programme' => $this->faker->randomElement(['Computer Science', 'Electrical Engineering', 'Mechanical Engineering']),
            'year_of_study' => $this->faker->numberBetween(1, 4),
            'hostel' => $this->faker->randomElement(['Hostel A', 'Hostel B', 'Hostel C']),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'status' => 'active',
        ];
    }
}
