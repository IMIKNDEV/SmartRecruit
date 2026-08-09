<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Analytical dashboard for the authenticated recruiter.
     *
     * All metrics are scoped to the recruiter's own job offers.
     *
     * GET /api/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        /** @var User $recruiter */
        $recruiter = auth()->user();

        $jobs = JobOffer::where('recruiter_id', $recruiter->id)
            ->withCount('applications')
            ->orderByDesc('created_at')
            ->get();

        $jobIds = $jobs->pluck('id');

        // withTrashed(): soft-deleted applications keep counting in the
        // analytics (funnels, time-to-hire, score distribution, trends…).
        $applications = Application::withTrashed()
            ->with(['candidate.badges', 'jobOffer', 'analysis', 'interviews'])
            ->whereIn('job_offer_id', $jobIds)
            ->orderByDesc('created_at')
            ->get();

        $interviews = Interview::with('application')
            ->whereHas('application', fn ($q) => $q->withTrashed()->whereIn('job_offer_id', $jobIds))
            ->get();

        return response()->json([
            'funnels' => $this->funnels($jobs, $applications),
            'time_to_hire' => $this->timeToHire($jobs, $applications),
            'score_distribution' => $this->scoreDistribution($applications),
            'recent_activity' => $this->recentActivity($applications, $interviews),
            'offer_comparison' => $this->offerComparison($jobs, $applications),
            'pending_tasks' => $this->pendingTasks($applications, $interviews),
            'applications_trend' => $this->applicationsTrend($applications),
            'upcoming_interviews' => $this->upcomingInterviews($interviews),
            'pipeline_health' => $this->pipelineHealth($jobs, $applications, $interviews),
            'top_candidates' => $this->topCandidates($applications),
            'applications' => $this->applications($applications),
        ]);
    }

    /** Conversion funnel per offer (received → interview → accepted/refused). */
    private function funnels($jobs, $applications): array
    {
        return $jobs->map(function (JobOffer $job) use ($applications) {
            $apps = $applications->where('job_offer_id', $job->id);
            $counts = [
                'received' => $apps->where('status', 'received')->count(),
                'interview' => $apps->where('status', 'interview')->count(),
                'accepted' => $apps->where('status', 'accepted')->count(),
                'refused' => $apps->where('status', 'refused')->count(),
            ];
            $total = max(1, array_sum($counts));

            return array_merge([
                'job_offer_id' => $job->id,
                'title' => $job->title,
                'rates' => [
                    'received' => round($counts['received'] / $total * 100, 1),
                    'interview' => round($counts['interview'] / $total * 100, 1),
                    'accepted' => round($counts['accepted'] / $total * 100, 1),
                    'refused' => round($counts['refused'] / $total * 100, 1),
                ],
            ], $counts);
        })->values()->all();
    }

    /** Average days from application to decision (accepted only). */
    private function timeToHire($jobs, $applications): array
    {
        $accepted = $applications->where('status', 'accepted');
        $global = $this->avgDays($accepted);
        $byOffer = $jobs->map(function (JobOffer $job) use ($accepted) {
            return [
                'job_offer_id' => $job->id,
                'avg_days' => $this->avgDays($accepted->where('job_offer_id', $job->id)),
            ];
        })->values()->all();

        return ['global_avg_days' => $global, 'by_offer' => $byOffer];
    }

    private function avgDays($applications): float
    {
        if ($applications->isEmpty()) {
            return 0.0;
        }

        $days = $applications->map(function (Application $a) {
            $from = $a->created_at;
            $to = $a->updated_at ?: now();
            if (! $from) {
                return 0;
            }

            // Carbon 3's diffInDays is signed — use from->diffInDays(to) for a positive value.
            return max(0, round($from->diffInDays($to), 1));
        });

        return round($days->avg(), 1);
    }

    /** Score buckets from the AI matching analysis (recruiter's offers). */
    private function scoreDistribution($applications): array
    {
        $scores = $applications->map(fn (Application $a) => (float) ($a->analysis?->matching_score ?? 0));

        return [
            '>80' => $scores->filter(fn ($s) => $s > 80)->count(),
            '50-80' => $scores->filter(fn ($s) => $s >= 50 && $s <= 80)->count(),
            '<50' => $scores->filter(fn ($s) => $s < 50)->count(),
        ];
    }

    /** Latest activity feed (applications, interviews, decisions). */
    private function recentActivity($applications, $interviews): array
    {
        $items = collect();

        foreach ($applications as $a) {
            $items->push([
                'type' => $a->status === 'accepted' ? 'acceptance'
                    : ($a->status === 'refused' ? 'refusal' : 'application'),
                'label' => ($a->candidate?->name ?? 'Candidate').' '.$this->actionLabel($a),
                'at' => $a->created_at?->toISOString(),
            ]);
        }

        foreach ($interviews as $iv) {
            if ($iv->status === 'completed') {
                $items->push([
                    'type' => 'interview',
                    'label' => 'Interview completed for '.($iv->application?->candidate?->name ?? 'candidate'),
                    'at' => $iv->updated_at?->toISOString(),
                ]);
            }
        }

        return $items->filter(fn ($i) => $i['at'])
            ->sortByDesc('at')
            ->take(8)
            ->values()
            ->all();
    }

    private function actionLabel(Application $a): string
    {
        return match ($a->status) {
            'accepted' => 'accepted for '.($a->jobOffer?->title ?? 'the position'),
            'refused' => 'refused for '.($a->jobOffer?->title ?? 'the position'),
            default => 'applied to '.($a->jobOffer?->title ?? 'a position'),
        };
    }

    /** Interview → acceptance conversion per offer, vs the recruiter's average. */
    private function offerComparison($jobs, $applications): array
    {
        $interview = $applications->where('status', 'interview')->count();
        $accepted = $applications->where('status', 'accepted')->count();
        $recruiterAvg = $interview + $accepted > 0
            ? round($accepted / ($interview + $accepted) * 100, 1)
            : 0.0;

        return $jobs->map(function (JobOffer $job) use ($applications, $recruiterAvg) {
            $apps = $applications->where('job_offer_id', $job->id);
            $iv = $apps->where('status', 'interview')->count();
            $ac = $apps->where('status', 'accepted')->count();

            return [
                'job_offer_id' => $job->id,
                'interview_to_accepted' => $iv + $ac > 0 ? round($ac / ($iv + $ac) * 100, 1) : 0.0,
                'recruiter_avg' => $recruiterAvg,
            ];
        })->values()->all();
    }

    private function pendingTasks($applications, $interviews): array
    {
        return [
            'interviews_to_evaluate' => $interviews
                ->where('status', 'scheduled')
                ->where('scheduled_at', '<=', now())
                ->count(),
            'applications_pending_over_7_days' => $applications
                ->whereIn('status', ['received', 'interview'])
                ->filter(fn (Application $a) => $a->created_at && $a->created_at->lt(now()->subDays(7)))
                ->count(),
        ];
    }

    /** Daily application volume over the last 30 days (zero-filled). */
    private function applicationsTrend($applications): array
    {
        $trend = collect(range(29, 0))->mapWithKeys(function (int $daysAgo) {
            return [now()->subDays($daysAgo)->toDateString() => 0];
        });

        foreach ($applications as $application) {
            if ($application->created_at) {
                $date = $application->created_at->toDateString();
                if ($trend->has($date)) {
                    $trend->put($date, $trend[$date] + 1);
                }
            }
        }

        return $trend
            ->map(fn (int $count, string $date) => ['date' => $date, 'count' => $count])
            ->values()
            ->all();
    }

    /** Interviews scheduled from now onward (next 14 days), soonest first. */
    private function upcomingInterviews($interviews): array
    {
        return $interviews
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->sortBy('scheduled_at')
            ->take(6)
            ->values()
            ->map(function (Interview $iv) {
                return [
                    'id' => $iv->id,
                    'scheduled_at' => $iv->scheduled_at?->toISOString(),
                    'link' => $iv->link,
                    'candidate_name' => $iv->application?->candidate?->name ?? 'Candidate',
                    'job_title' => $iv->application?->jobOffer?->title ?? '—',
                ];
            })
            ->all();
    }

    /**
     * Actionable bottlenecks: stale applications per offer, closing deadlines
     * and the recruiter's average time to schedule the first interview.
     */
    private function pipelineHealth($jobs, $applications, $interviews): array
    {
        $staleByOffer = $jobs->map(function (JobOffer $job) use ($applications) {
            $count = $applications
                ->where('job_offer_id', $job->id)
                ->whereIn('status', ['received', 'interview'])
                ->filter(fn (Application $a) => $a->created_at && $a->created_at->lt(now()->subDays(7)))
                ->count();

            return ['job_offer_id' => $job->id, 'title' => $job->title, 'count' => $count];
        })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values()
            ->all();

        $deadlineSoon = $jobs
            ->filter(fn (JobOffer $job) => $job->status === 'active'
                && $job->deadline
                && $job->deadline->between(now()->startOfDay(), now()->addDays(7)->endOfDay()))
            ->map(fn (JobOffer $job) => [
                'job_offer_id' => $job->id,
                'title' => $job->title,
                'deadline' => $job->deadline->toDateString(),
            ])
            ->values()
            ->all();

        $firstInterviewDays = [];
        foreach ($applications as $application) {
            if (! $application->created_at) {
                continue;
            }
            $first = $application->interviews->sortBy('scheduled_at')->first();
            if ($first && $first->scheduled_at) {
                $firstInterviewDays[] = max(0, round($application->created_at->diffInDays($first->scheduled_at), 1));
            }
        }

        return [
            'stale_by_offer' => $staleByOffer,
            'deadline_soon' => $deadlineSoon,
            'avg_first_response_days' => count($firstInterviewDays)
                ? round(array_sum($firstInterviewDays) / count($firstInterviewDays), 1)
                : 0.0,
        ];
    }

    /** Top 5 applications by matching score (refused candidates excluded). */
    private function topCandidates($applications): array
    {
        return $applications
            ->where('status', '!==', 'refused')
            ->sortByDesc(fn (Application $a) => (float) ($a->analysis?->matching_score ?? 0))
            ->take(5)
            ->values()
            ->map(function (Application $a) {
                return [
                    'id' => $a->id,
                    'matching_score' => $a->analysis?->matching_score,
                    'matched_keywords' => $a->analysis?->matched_keywords ?? [],
                    'missing_keywords' => $a->analysis?->missing_keywords ?? [],
                    'status' => $a->status,
                    'tags' => $a->tags ?? [],
                    'candidate' => $a->candidate ? [
                        'id' => $a->candidate->id,
                        'name' => $a->candidate->name,
                        'badges' => $a->candidate->badges->pluck('type')->all(),
                    ] : null,
                    'job_offer' => $a->jobOffer ? [
                        'id' => $a->jobOffer->id,
                        'title' => $a->jobOffer->title,
                    ] : null,
                ];
            })
            ->all();
    }

    /** Full application list for the recruiter's Kanban (score + keywords included). */
    private function applications($applications): array
    {
        return $applications->map(function (Application $a) {
            return [
                'id' => $a->id,
                'matching_score' => $a->analysis?->matching_score,
                'matched_keywords' => $a->analysis?->matched_keywords,
                'missing_keywords' => $a->analysis?->missing_keywords,
                'tags' => $a->tags ?? [],
                'status' => $a->status,
                'cv_path' => $a->cv_path,
                'cover_letter' => $a->cover_letter,
                'notes' => $a->notes,
                'comments' => $a->comments,
                'candidate' => $a->candidate ? [
                    'id' => $a->candidate->id,
                    'name' => $a->candidate->name,
                    'email' => $a->candidate->email,
                    'role' => 'candidate',
                    'avatar' => $a->candidate->avatar,
                    'badges' => $a->candidate->badges->map(fn ($b) => [
                        'type' => $b->type,
                        'awarded_at' => $b->awarded_at?->toISOString(),
                    ])->all(),
                ] : null,
                'job_offer' => $a->jobOffer ? [
                    'id' => $a->jobOffer->id,
                    'title' => $a->jobOffer->title,
                ] : null,
                'interviews' => $a->interviews->map(fn ($iv) => [
                    'id' => $iv->id,
                    'scheduled_at' => $iv->scheduled_at?->toISOString(),
                    'status' => $iv->status,
                ])->all(),
                'created_at' => $a->created_at?->toISOString(),
            ];
        })->values()->all();
    }
}
