<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDays($this->faker->numberBetween(1, 30));

        return [
            'title' => $this->faker->sentence(4).' meeting',
            'description' => $this->faker->paragraphs(2, true),
            'organizer_id' => User::factory(),
            'meeting_date' => $startsAt->toDateString(),
            'meeting_time' => $startsAt->setTime(10, 0)->format('H:i:s'),
            'venue' => $this->faker->randomElement(['Boardroom', 'Faculty Hall', 'Main Auditorium']),
            'status' => MeetingStatus::Scheduled->value,
            'agenda' => $this->faker->paragraph(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => MeetingStatus::Completed->value,
            'meeting_date' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => MeetingStatus::Cancelled->value]);
    }
}
