<?php

use App\Jobs\CalculateMatchingScoreJob;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('candidate applies', function () {
    beforeEach(function () {
        Storage::fake('public');
        Queue::fake();
        $this->candidate = User::factory()->candidate()->create();
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        Sanctum::actingAs($this->candidate);
    });

    it('candidate can apply to a job with CV upload', function () {
        $response = $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'cover_letter' => 'I am very interested in this position and believe my skills match your requirements perfectly.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'status', 'cv_path', 'cover_letter']]);

        $this->assertDatabaseHas('applications', [
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
        ]);
    });

    it('candidate cannot apply twice to the same job', function () {
        Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
        ]);

        $response = $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'cover_letter' => 'I am very interested in this position and believe my skills match your requirements perfectly.',
        ]);

        $response->assertStatus(422);
    });

    it('validates CV is required and must be PDF', function () {
        $response = $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cover_letter' => 'Cover letter text here for testing validation purposes.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cv']);
    });

    it('validates cover letter is required', function () {
        $response = $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cover_letter']);
    });

    it('dispatches CalculateMatchingScoreJob on apply', function () {
        $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'cover_letter' => 'I am very interested in this position and believe my skills match your requirements perfectly.',
        ]);

        Queue::assertPushed(CalculateMatchingScoreJob::class);
    });

    it('recruiter cannot apply to their own job', function () {
        Sanctum::actingAs($this->recruiter);

        $response = $this->postJson("/api/job-offers/{$this->job->id}/apply", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'cover_letter' => 'I am applying to my own job post.',
        ]);

        $response->assertStatus(403);
    });
});

describe('viewing applications', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->otherRecruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
        ]);
    });

    it('recruiter can view applications for own job', function () {
        Sanctum::actingAs($this->recruiter);

        $response = $this->getJson("/api/job-offers/{$this->job->id}/applications");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    });

    it('recruiter cannot view applications for anothers job', function () {
        Sanctum::actingAs($this->otherRecruiter);

        $response = $this->getJson("/api/job-offers/{$this->job->id}/applications");

        $response->assertStatus(403);
    });

    it('recruiter can view single application detail', function () {
        Sanctum::actingAs($this->recruiter);

        $response = $this->getJson("/api/applications/{$this->application->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'status', 'cv_path', 'cover_letter']]);
    });

    it('candidate can view own application', function () {
        Sanctum::actingAs($this->candidate);

        $response = $this->getJson("/api/applications/{$this->application->id}");

        $response->assertStatus(200);
    });

    it('candidate can list own applications', function () {
        Sanctum::actingAs($this->candidate);

        $response = $this->getJson('/api/applications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    });

    it('candidate cannot view anothers application', function () {
        $otherCandidate = User::factory()->candidate()->create();
        Sanctum::actingAs($otherCandidate);

        $response = $this->getJson("/api/applications/{$this->application->id}");

        $response->assertStatus(403);
    });

    it('unauthenticated user cannot view applications', function () {
        $response = $this->getJson("/api/applications/{$this->application->id}");

        $response->assertStatus(401);
    });
});

describe('status transitions', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'received',
        ]);
        Sanctum::actingAs($this->recruiter);
    });

    it('recruiter can update status from received to interview', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'interview',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'interview');
    });

    it('recruiter can update status from received to refused', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'refused',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'refused');
    });

    it('cannot go backwards from interview to received', function () {
        $this->application->update(['status' => 'interview']);

        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'received',
        ]);

        $response->assertStatus(422);
    });

    it('cannot transition from accepted terminal state', function () {
        $this->application->update(['status' => 'accepted']);

        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'refused',
        ]);

        $response->assertStatus(422);
    });

    it('cannot transition from refused terminal state', function () {
        $this->application->update(['status' => 'refused']);

        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'interview',
        ]);

        $response->assertStatus(422);
    });

    it('can transition from interview to accepted', function () {
        $this->application->update(['status' => 'interview']);

        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'accepted');
    });

    it('can transition from interview to refused', function () {
        $this->application->update(['status' => 'interview']);

        $response = $this->putJson("/api/applications/{$this->application->id}/status", [
            'status' => 'refused',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'refused');
    });

    it('validates status is required and valid', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    });
});

describe('notes and tags', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
        ]);
        Sanctum::actingAs($this->recruiter);
    });

    it('recruiter can add notes to an application', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/notes", [
            'notes' => 'Strong candidate with good Laravel experience.',
            'comments' => 'We will contact you soon.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.notes', 'Strong candidate with good Laravel experience.')
            ->assertJsonPath('data.comments', 'We will contact you soon.');
    });

    it('recruiter can update tags', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/tags", [
            'tags' => ['prioritaire', 'a_relancer'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.tags', ['prioritaire', 'a_relancer']);
    });

    it('validates tags are from predefined list', function () {
        $response = $this->putJson("/api/applications/{$this->application->id}/tags", [
            'tags' => ['invalid_tag'],
        ]);

        $response->assertStatus(422);
    });
});

describe('sorting applications by matching score', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);

        $this->appLow = Application::factory()->create(['job_offer_id' => $this->job->id]);
        $this->appHigh = Application::factory()->create(['job_offer_id' => $this->job->id]);
        $this->appMid = Application::factory()->create(['job_offer_id' => $this->job->id]);
        $this->appPending = Application::factory()->create(['job_offer_id' => $this->job->id]);

        ApplicationAnalysis::factory()->create([
            'application_id' => $this->appHigh->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 90.00,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $this->appMid->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 60.00,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $this->appLow->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 30.00,
        ]);

        Sanctum::actingAs($this->recruiter);
    });

    it('returns applications sorted by matching score descending', function () {
        $response = $this->getJson("/api/job-offers/{$this->job->id}/applications");

        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids)->toBe([
            $this->appHigh->id,
            $this->appMid->id,
            $this->appLow->id,
            $this->appPending->id,
        ]);
    });

    it('includes matched and missing keywords per application', function () {
        $response = $this->getJson("/api/job-offers/{$this->job->id}/applications");

        $response->assertJsonPath('data.0.analysis.matching_score', '90.00')
            ->assertJsonPath('data.0.analysis.matched_keywords', ['PHP', 'Laravel'])
            ->assertJsonPath('data.0.analysis.missing_keywords', ['Docker']);
    });
});
