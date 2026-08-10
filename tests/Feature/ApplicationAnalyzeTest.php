<?php

use App\Agents\MatchingAgent;
use App\Jobs\CalculateMatchingScoreJob;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('recruiter AI analysis (async)', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create(['job_offer_id' => $this->job->id]);
        Storage::disk('public')->put($this->application->cv_path, 'PHP Laravel MySQL developer with Docker experience');
        Sanctum::actingAs($this->recruiter);
    });

    it('dispatches the queued job and returns 202 immediately', function () {
        Queue::fake();

        $response = $this->postJson("/api/applications/{$this->application->id}/analyze");

        $response->assertStatus(202)
            ->assertJsonPath('status', 'processing');

        Queue::assertPushed(CalculateMatchingScoreJob::class, fn (CalculateMatchingScoreJob $job) => $job->application->id === $this->application->id);
    });

    it('does not create the analysis row synchronously (it is created by the job)', function () {
        Queue::fake();

        $this->postJson("/api/applications/{$this->application->id}/analyze")->assertStatus(202);

        $this->assertDatabaseMissing('application_analysis', ['application_id' => $this->application->id]);
    });

    it('polling endpoint returns data null while the job is still computing', function () {
        $this->getJson("/api/applications/{$this->application->id}/analysis")
            ->assertStatus(200)
            ->assertJsonPath('data', null);
    });

    it('polling endpoint returns the stored analysis once computed', function () {
        ApplicationAnalysis::factory()->create([
            'application_id' => $this->application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 83.00,
            'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
            'missing_keywords' => ['Redis'],
            'strengths' => 'Solid Laravel experience, clean architecture.',
            'gaps' => 'Redis absent du CV.',
            'years_experience' => 5,
            'education_level' => 'Master',
            'languages' => ['Français', 'Anglais'],
            'recommendation' => 'À convoquer en entretien.',
        ]);

        $this->getJson("/api/applications/{$this->application->id}/analysis")
            ->assertStatus(200)
            ->assertJsonPath('data.matching_score', '83.00')
            ->assertJsonPath('data.matched_keywords', ['PHP', 'Laravel', 'MySQL'])
            ->assertJsonPath('data.missing_keywords', ['Redis'])
            ->assertJsonPath('data.strengths', 'Solid Laravel experience, clean architecture.')
            ->assertJsonPath('data.gaps', 'Redis absent du CV.')
            ->assertJsonPath('data.years_experience', 5)
            ->assertJsonPath('data.education_level', 'Master')
            ->assertJsonPath('data.languages', ['Français', 'Anglais'])
            ->assertJsonPath('data.recommendation', 'À convoquer en entretien.');
    });

    it('job stores the full score + keyword detail via the AI engine', function () {
        MatchingAgent::fake([
            [
                'score' => 83.0,
                'matched_keywords' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
                'missing_keywords' => ['Redis'],
                'strengths' => 'Solid Laravel experience, clean architecture.',
                'gaps' => 'Redis absent du CV.',
                'years_experience' => 5,
                'education_level' => 'Master',
                'languages' => ['Français', 'Anglais'],
                'recommendation' => 'À convoquer en entretien.',
            ],
        ]);

        (new CalculateMatchingScoreJob($this->application))->handle();

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 83.00,
            'years_experience' => 5,
            'education_level' => 'Master',
        ]);
        $this->assertSame(1, ApplicationAnalysis::where('application_id', $this->application->id)->count());
    });

    it('job re-runs the analysis and overwrites the previous score', function () {
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

        (new CalculateMatchingScoreJob($this->application))->handle();

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'matching_score' => 91.00,
        ]);
        $this->assertSame(1, ApplicationAnalysis::where('application_id', $this->application->id)->count());
    });

    it('job stores a 0 score without calling the AI when the CV has no extractable text', function () {
        MatchingAgent::fake(); // AI is never called for a text-less (scanned) PDF

        Storage::disk('public')->put($this->application->cv_path, '');

        (new CalculateMatchingScoreJob($this->application))->handle();

        $this->assertDatabaseHas('application_analysis', [
            'application_id' => $this->application->id,
            'matching_score' => 0.00,
        ]);
    });

    it('forbids a candidate from dispatching or polling the analysis', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->postJson("/api/applications/{$this->application->id}/analyze")
            ->assertStatus(403);

        $this->getJson("/api/applications/{$this->application->id}/analysis")
            ->assertStatus(403);
    });

    it('forbids a recruiter from analyzing another recruiter application', function () {
        $otherRecruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($otherRecruiter);

        $this->postJson("/api/applications/{$this->application->id}/analyze")
            ->assertStatus(403);

        $this->getJson("/api/applications/{$this->application->id}/analysis")
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
