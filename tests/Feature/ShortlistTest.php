<?php

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('shortlist', function () {
    it('returns the top-5 applications sorted by matching score', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $apps = Application::factory()->count(6)->create(['job_offer_id' => $job->id]);
        foreach ($apps as $index => $app) {
            ApplicationAnalysis::factory()->create([
                'application_id' => $app->id,
                'job_offer_id' => $job->id,
                'matching_score' => $index + 1,
            ]);
        }

        Sanctum::actingAs($recruiter);

        $response = $this->getJson("/api/job-offers/{$job->id}/shortlist");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        $scores = collect($response->json('data'))->pluck('analysis.matching_score')->map(fn ($s) => (float) $s)->values();
        expect($scores->all())->toBe([6.0, 5.0, 4.0, 3.0, 2.0]);
    });

    it('forbids a recruiter from viewing another recruiters shortlist', function () {
        $other = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $other->id]);

        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->getJson("/api/job-offers/{$job->id}/shortlist")
            ->assertStatus(403);
    });

    it('forbids a candidate from viewing a shortlist', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->getJson("/api/job-offers/{$job->id}/shortlist")
            ->assertStatus(403);
    });
});

describe('shortlist export', function () {
    it('streams a CSV file with the shortlisted candidates', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $app = Application::factory()->create(['job_offer_id' => $job->id]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $app->id,
            'job_offer_id' => $job->id,
            'matching_score' => 92.5,
            'matched_keywords' => ['PHP', 'Laravel'],
            'missing_keywords' => ['Docker'],
        ]);

        Sanctum::actingAs($recruiter);

        $response = $this->get("/api/job-offers/{$job->id}/shortlist/export");

        $response->assertStatus(200);
        expect($response->headers->get('content-type'))->toContain('text/csv');
        expect($response->headers->get('content-disposition'))->toContain('shortlist-'.$job->id);

        $content = $response->streamedContent();
        expect($content)->toContain($app->candidate->name);
        expect($content)->toContain('92.50');
        expect($content)->toContain('PHP, Laravel');
        expect($content)->toContain('Docker');
    });

    it('forbids a recruiter from exporting another recruiters shortlist', function () {
        $other = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $other->id]);

        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->get("/api/job-offers/{$job->id}/shortlist/export")
            ->assertStatus(403);
    });
});
