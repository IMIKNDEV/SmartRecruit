<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Services\MatchingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateMatchingScoreJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
    ) {}

    public function handle(): void
    {
        try {
            $result = (new MatchingService)->calculateScore($this->application);

            ApplicationAnalysis::updateOrCreate(
                ['application_id' => $this->application->id],
                [
                    'job_offer_id' => $this->application->job_offer_id,
                    'matching_score' => $result['score'],
                    'matched_keywords' => $result['matched'],
                    'missing_keywords' => $result['missing'],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Matching score failed', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
