<?php

namespace Database\Factories;

use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedFilterFactory extends Factory
{
    protected $model = SavedFilter::class;

    public function definition(): array
    {
        return [
            'recruiter_id' => User::factory()->recruiter(),
            'name' => fake()->words(3, true),
            'criteria' => [
                'min_score' => 80,
                'tech_stack' => ['PHP', 'Laravel'],
                'contract_type' => 'CDI',
                'status' => 'received',
            ],
        ];
    }
}
