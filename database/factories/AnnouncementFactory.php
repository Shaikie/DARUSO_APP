<?php

namespace Database\Factories;

use App\Enums\AnnouncementStatus;
use App\Enums\Priority;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),
            'author_id' => User::factory(),
            'priority' => Priority::Normal->value,
            'status' => AnnouncementStatus::Draft->value,
            'requires_approval' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => AnnouncementStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => Priority::Urgent->value]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => AnnouncementStatus::Published->value,
            'published_at' => now()->subDays(10),
            'expires_at' => now()->subDay(),
        ]);
    }
}
