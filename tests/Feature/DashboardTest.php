<?php

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('recruiter dashboard stats', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();

        $this->job = JobOffer::factory()->create([
            'recruiter_id' => $this->recruiter->id,
            'title' => 'Laravel Developer',
        ]);
        // A job owned by another recruiter — must NOT appear in the stats.
        $this->otherJob = JobOffer::factory()->create();

        Sanctum::actingAs($this->recruiter);
    });

    it('returns the full dashboard payload for the recruiter', function () {
        $application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 85,
        ]);
        Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->otherJob->id,
            'status' => 'accepted',
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'funnels' => [['job_offer_id', 'title', 'received', 'interview', 'accepted', 'refused', 'rates']],
                'time_to_hire' => ['global_avg_days', 'by_offer'],
                'score_distribution' => ['>80', '50-80', '<50'],
                'recent_activity',
                'offer_comparison',
                'pending_tasks' => ['interviews_to_evaluate', 'applications_pending_over_7_days'],
                'applications_trend',
                'upcoming_interviews',
                'pipeline_health' => ['stale_by_offer', 'deadline_soon', 'avg_first_response_days'],
                'top_candidates',
                'applications',
            ]);

        // Own job only — the other recruiter's job is excluded.
        expect($response->json('funnels'))->toHaveCount(1)
            ->and($response->json('funnels.0.title'))->toBe('Laravel Developer');
    });

    it('computes funnel counts and score distribution', function () {
        $received = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
        ]);
        $accepted = Application::factory()->create([
            'candidate_id' => User::factory()->candidate()->create()->id,
            'job_offer_id' => $this->job->id,
            'status' => 'accepted',
        ]);

        ApplicationAnalysis::factory()->create(['application_id' => $received->id, 'job_offer_id' => $this->job->id, 'matching_score' => 90]);
        ApplicationAnalysis::factory()->create(['application_id' => $accepted->id, 'job_offer_id' => $this->job->id, 'matching_score' => 30]);

        $data = $this->getJson('/api/dashboard/stats')->json();

        expect($data['funnels'][0]['received'])->toBe(1)
            ->and($data['funnels'][0]['accepted'])->toBe(1)
            ->and($data['score_distribution']['>80'])->toBe(1)
            ->and($data['score_distribution']['<50'])->toBe(1);
    });

    it('reports pending interviews to evaluate and stale applications', function () {
        $old = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
            'created_at' => now()->subDays(10),
        ]);
        Interview::factory()->create([
            'application_id' => $old->id,
            'scheduled_at' => now()->subDay(),
            'status' => 'scheduled',
        ]);

        $data = $this->getJson('/api/dashboard/stats')->json();

        expect($data['pending_tasks']['interviews_to_evaluate'])->toBe(1)
            ->and($data['pending_tasks']['applications_pending_over_7_days'])->toBe(1);
    });

    it('builds the 30-day application trend with the latest date counted', function () {
        $recent = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'created_at' => now()->subDays(2),
        ]);

        $data = $this->getJson('/api/dashboard/stats')->json();

        expect($data['applications_trend'])->toHaveCount(30)
            ->and($data['applications_trend'][0]['date'])->toBe(now()->subDays(29)->toDateString())
            ->and(collect($data['applications_trend'])->firstWhere('date', $recent->created_at->toDateString())['count'])->toBe(1);
    });

    it('lists upcoming interviews and pipeline bottlenecks', function () {
        $app = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'interview',
            'created_at' => now()->subDays(10), // stale (received/interview older than 7 days)
        ]);
        Interview::factory()->create([
            'application_id' => $app->id,
            'scheduled_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);
        $deadlineSoon = JobOffer::factory()->create([
            'recruiter_id' => $this->recruiter->id,
            'deadline' => now()->addDays(3),
        ]);

        $data = $this->getJson('/api/dashboard/stats')->json();

        expect($data['upcoming_interviews'])->toHaveCount(1)
            ->and($data['upcoming_interviews'][0]['candidate_name'])->toBe($this->candidate->name)
            ->and($data['pipeline_health']['stale_by_offer'][0]['count'])->toBe(1)
            ->and(collect($data['pipeline_health']['deadline_soon'])->pluck('job_offer_id'))->toContain($deadlineSoon->id);
    });

    it('ranks top candidates by matching score, excluding refused', function () {
        $high = Application::factory()->create([
            'candidate_id' => User::factory()->candidate()->create()->id,
            'job_offer_id' => $this->job->id,
            'status' => 'interview',
        ]);
        $low = Application::factory()->create([
            'candidate_id' => User::factory()->candidate()->create()->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
        ]);
        $refused = Application::factory()->create([
            'candidate_id' => User::factory()->candidate()->create()->id,
            'job_offer_id' => $this->job->id,
            'status' => 'refused',
        ]);
        ApplicationAnalysis::factory()->create(['application_id' => $high->id, 'job_offer_id' => $this->job->id, 'matching_score' => 90]);
        ApplicationAnalysis::factory()->create(['application_id' => $low->id, 'job_offer_id' => $this->job->id, 'matching_score' => 40]);
        ApplicationAnalysis::factory()->create(['application_id' => $refused->id, 'job_offer_id' => $this->job->id, 'matching_score' => 99]);

        $data = $this->getJson('/api/dashboard/stats')->json();

        expect($data['top_candidates'])->toHaveCount(2)
            ->and($data['top_candidates'][0]['matching_score'])->toBe('90.00')
            ->and(collect($data['top_candidates'])->pluck('id'))->not->toContain($refused->id);
    });

    it('returns 403 for a candidate', function () {
        Sanctum::actingAs($this->candidate);

        $this->getJson('/api/dashboard/stats')->assertStatus(403);
    });
});

describe('dashboard stats auth', function () {
    it('requires authentication', function () {
        $this->getJson('/api/dashboard/stats')->assertStatus(401);
    });
});
