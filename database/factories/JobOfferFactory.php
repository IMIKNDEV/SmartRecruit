<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobOfferFactory extends Factory
{
    protected $model = JobOffer::class;

    public function definition(): array
    {
        return [
            'recruiter_id' => User::factory()->recruiter(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'tech_stack' => 'PHP, Laravel, MySQL',
            'contract_type' => 'CDI',
            'salary' => fake()->optional()->numberBetween(20000, 100000),
            'deadline' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'status' => 'active',
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
