<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(2, true),
            'category' => 'academic',
            'status' => ComplaintStatus::Submitted->value,
            'creator_id' => User::factory(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => ['status' => ComplaintStatus::InProgress->value]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => ComplaintStatus::Resolved->value,
            'resolved_at' => now(),
        ]);
    }
}
