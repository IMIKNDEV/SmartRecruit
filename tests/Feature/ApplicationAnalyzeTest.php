<?php

use App\Agents\MatchingAgent;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('recruiter AI analysis', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create(['job_offer_id' => $this->job->id]);
        Storage::disk('public')->put($this->application->cv_path, 'PHP Laravel MySQL developer with Docker experience');
        Sanctum::actingAs($this->recruiter);
    });

    it('analyzes a CV and stores the score + keyword detail', function () {
        MatchingAgent::fake([
            [
                'score' => 83.0,
                'matched_keywords' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
                'missing_keywords' => ['Redis'],
            ],
        ]);

        $response = $this->postJson("/api/applications/{$this->application->id}/analyze");

        $response->assertStatus(200)
            ->assertJsonPath('data.analysis.matching_score', '83.00')
            ->assertJsonPath('data.analysis.matched_keywords', ['PHP', 'Laravel', 'MySQL', 'Docker'])
            ->assertJsonPath('data.analysis.missing_keywords', ['Redis']);

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 83.00,
        ]);
    });

    it('re-runs the analysis and overwrites the previous score', function () {
        ApplicationAnalysis::factory()->create([
            'application_id' => $this->application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 40.00,
        ]);

        MatchingAgent::fake([
            [
                'score' => 91.0,
                'matched_keywords' => ['PHP', 'Laravel'],
                'missing_keywords' => [],
            ],
        ]);

        $response = $this->postJson("/api/applications/{$this->application->id}/analyze");

        $response->assertStatus(200)
            ->assertJsonPath('data.analysis.matching_score', '91.00');

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'matching_score' => 91.00,
        ]);
        $this->assertSame(1, ApplicationAnalysis::where('application_id', $this->application->id)->count());
    });

    it('stores a 0 score without calling the AI when the CV has no extractable text', function () {
        MatchingAgent::fake(); // AI is never called for a text-less (scanned) PDF

        Storage::disk('public')->put($this->application->cv_path, '');

        $response = $this->postJson("/api/applications/{$this->application->id}/analyze");

        $response->assertStatus(200)
            ->assertJsonPath('data.analysis.matching_score', '0.00')
            ->assertJsonPath('data.analysis.matched_keywords', [])
            ->assertJsonPath('data.analysis.missing_keywords', []);

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'matching_score' => 0.00,
        ]);
    });

    it('forbids a candidate from running the analysis', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->postJson("/api/applications/{$this->application->id}/analyze")
            ->assertStatus(403);
    });

    it('forbids a recruiter from analyzing another recruiter application', function () {
        $otherRecruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($otherRecruiter);

        $this->postJson("/api/applications/{$this->application->id}/analyze")
            ->assertStatus(403);
    });
});

describe('recruiter CV streaming', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create(['job_offer_id' => $this->job->id]);
        Sanctum::actingAs($this->recruiter);
    });

    it('streams the CV file content', function () {
        Storage::disk('public')->put($this->application->cv_path, 'PDF content');

        $response = $this->get("/api/applications/{$this->application->id}/cv");

        $response->assertStatus(200);
        expect($response->streamedContent())->toBe('PDF content');
    });

    it('returns 404 when the CV file is missing', function () {
        $this->getJson("/api/applications/{$this->application->id}/cv")
            ->assertStatus(404);
    });

    it('forbids a candidate from downloading the CV', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        Storage::disk('public')->put($this->application->cv_path, 'PDF content');

        $this->getJson("/api/applications/{$this->application->id}/cv")
            ->assertStatus(403);
    });
});
