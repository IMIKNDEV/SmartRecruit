<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Interview;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterviewFactory extends Factory
{
    protected $model = Interview::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'link' => null,
            'status' => 'scheduled',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'score_technique' => fake()->numberBetween(1, 5),
            'score_communication' => fake()->numberBetween(1, 5),
            'score_motivation' => fake()->numberBetween(1, 5),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
