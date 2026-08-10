<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'candidate_id' => User::factory()->candidate(),
            'job_offer_id' => JobOffer::factory(),
            'cv_path' => 'cvs/'.fake()->numberBetween(1, 1000).'/cv.pdf',
            'cover_letter' => fake()->paragraph(3),
            'tags' => null,
            'status' => 'received',
        ];
    }

    public function interview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'interview',
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    public function refused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refused',
        ]);
    }
}
