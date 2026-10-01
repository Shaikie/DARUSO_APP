<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'registration_number' => $this->faker->unique()->regexify('REG-\d{4}-\d{4}'),
            'college' => $this->faker->randomElement([
                'College of Science',
                'College of Engineering',
                'College of Arts',
            ]),
            'school_faculty' => $this->faker->randomElement([
                'School of Engineering',
                'School of Science',
                'School of Arts',
            ]),
            'programme' => $this->faker->randomElement([
                'Computer Science',
                'Electrical Engineering',
                'Mechanical Engineering',
                'Civil Engineering',
            ]),
            'year_of_study' => $this->faker->numberBetween(1, 4),
            'hostel' => $this->faker->randomElement(['Hostel A', 'Hostel B', 'Hostel C', null]),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'status' => StudentStatus::Active->value,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => StudentStatus::Suspended->value]);
    }

    public function graduated(): static
    {
        return $this->state(fn (): array => ['status' => StudentStatus::Graduated->value]);
    }
}
