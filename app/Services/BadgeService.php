<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Badge;
use App\Models\User;

class BadgeService
{
    public function checkAndAward(Application $application): void
    {
        $candidate = $application->candidate;

        $this->awardIfNotExists($candidate, 'cv_complet');

        if ($application->analysis && $application->analysis->matching_score > 80) {
            $this->awardIfNotExists($candidate, 'high_match');
        }
    }

    public function checkInterviewBadge(Application $application): void
    {
        if ($application->status === 'accepted') {
            $interview = $application->interviews()
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->first();

            if ($interview && $interview->average_score > 3) {
                $this->awardIfNotExists($application->candidate, 'interview_passed');
            }
        }
    }

    protected function awardIfNotExists(User $candidate, string $type): void
    {
        Badge::firstOrCreate([
            'candidate_id' => $candidate->id,
            'type' => $type,
        ], [
            'awarded_at' => now(),
        ]);
    }
}
