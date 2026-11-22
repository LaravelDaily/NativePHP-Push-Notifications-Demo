<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'title' => fake()->sentence(3),
            'starts_at' => now()->setTime(10, 0),
            'duration_minutes' => 60,
            'location' => 'Workspace',
            'category' => 'WORK',
            'description' => fake()->paragraph(),
        ];
    }
}
