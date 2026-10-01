<?php

namespace Database\Factories;

use App\Models\LeadershipTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadershipTerm>
 */
class LeadershipTermFactory extends Factory
{
    protected $model = LeadershipTerm::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->numerify('####/####'),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }
}
