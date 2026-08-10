<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Services\BadgeService;
use App\Services\MatchingService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class CalculateMatchingScoreJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Keep the unique lock (cache) for 30s after dispatch so a double-click
     * on "Analyser" cannot enqueue a second Groq call for the same
     * application while the first one is queued or running.
     */
    public $uniqueFor = 30;

    /**
     * One queued/running matching job per application.
     */
    public function uniqueId(): string
    {
        return (string) $this->application->id;
    }

    /**
     * Extra safety net: never run two matching jobs for the same
     * application at the same time; retry in 10s if blocked.
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('matching:'.$this->application->id))->releaseAfter(10)];
    }

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
                    'strengths' => $result['strengths'],
                    'gaps' => $result['gaps'],
                    'years_experience' => $result['years_experience'],
                    'education_level' => $result['education_level'],
                    'languages' => $result['languages'],
                    'recommendation' => $result['recommendation'],
                ]
            );

            (new BadgeService)->checkAndAward($this->application->load('analysis'));
        } catch (\Throwable $e) {
            Log::error('Matching score failed', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
