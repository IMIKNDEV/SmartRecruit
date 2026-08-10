<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;

class SuggestionService
{
    /**
     * Suggest other candidates who match the same skills as the given
     * application, across the recruiter's other open offers. Ranked by the
     * number of overlapping matched keywords, then by matching score.
     *
     * @return Collection<int, Application>
     */
    public function forApplication(Application $application)
    {
        $skills = $this->skillsFor($application);

        if ($skills === []) {
            return collect();
        }

        $recruiterId = $application->jobOffer->recruiter_id;

        return Application::with(['candidate', 'jobOffer', 'analysis'])
            ->whereHas('jobOffer', fn ($q) => $q
                ->where('recruiter_id', $recruiterId)
                ->where('status', 'active'))
            ->whereHas('analysis')
            ->where('candidate_id', '!=', $application->candidate_id)
            ->get()
            ->filter(function (Application $candidate) use ($skills) {
                return $this->overlapCount($candidate, $skills) > 0;
            })
            ->sortByDesc(function (Application $candidate) use ($skills) {
                return [
                    $this->overlapCount($candidate, $skills),
                    (float) $candidate->analysis?->matching_score,
                ];
            })
            ->values()
            ->take(10);
    }

    /**
     * @return array<int, string>
     */
    protected function skillsFor(Application $application): array
    {
        $matched = $application->analysis?->matched_keywords;

        if (is_array($matched) && $matched !== []) {
            return array_map('strtolower', $matched);
        }

        return array_map('strtolower', $application->jobOffer->tech_stack_array);
    }

    protected function overlapCount(Application $candidate, array $skills): int
    {
        $candidateSkills = $candidate->analysis?->matched_keywords ?? [];

        return count(array_intersect(
            array_map('strtolower', $candidateSkills),
            $skills,
        ));
    }
}
