<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationAnalysis>
 */
class ApplicationAnalysisFactory extends Factory
{
    protected $model = ApplicationAnalysis::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'job_offer_id' => JobOffer::factory(),
            'matching_score' => fake()->randomFloat(2, 30, 95),
            'matched_keywords' => ['PHP', 'Laravel'],
            'missing_keywords' => ['Docker'],
        ];
    }
}
