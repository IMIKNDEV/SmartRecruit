<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'candidate_id' => User::factory()->candidate(),
            'type' => 'cv_complet',
            'awarded_at' => now(),
        ];
    }
}
