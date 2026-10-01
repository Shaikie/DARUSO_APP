<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4).' event',
            'description' => $this->faker->paragraphs(2, true),
            'organizer_id' => User::factory(),
            'event_date' => now()->addDays($this->faker->numberBetween(1, 60))->toDateString(),
            'venue' => $this->faker->randomElement(['University Grounds', 'Faculty Hall', 'Main Auditorium']),
            'status' => EventStatus::Upcoming->value,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => EventStatus::Completed->value,
            'event_date' => now()->subDays(10)->toDateString(),
        ]);
    }
}
