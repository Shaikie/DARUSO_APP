<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->sentence(),
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'priority' => Priority::Normal->value,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (): array => ['read_at' => now()]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => Priority::Urgent->value]);
    }
}
