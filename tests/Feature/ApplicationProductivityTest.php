<?php

use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('application comparison', function () {
    it('compares 2-4 applications of the same offer side by side', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $apps = Application::factory()->count(3)->create(['job_offer_id' => $job->id]);

        foreach ($apps as $app) {
            ApplicationAnalysis::factory()->create([
                'application_id' => $app->id,
                'job_offer_id' => $job->id,
                'matching_score' => 85,
                'matched_keywords' => ['PHP', 'Laravel'],
            ]);
        }

        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/applications/compare', [
            'ids' => $apps->pluck('id')->all(),
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'candidate', 'analysis']]]);
    });

    it('rejects ids belonging to different job offers', function () {
        $recruiter = User::factory()->recruiter()->create();
        $jobA = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $jobB = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $appA = Application::factory()->create(['job_offer_id' => $jobA->id]);
        $appB = Application::factory()->create(['job_offer_id' => $jobB->id]);

        Sanctum::actingAs($recruiter);

        $this->postJson('/api/applications/compare', [
            'ids' => [$appA->id, $appB->id],
        ])->assertStatus(422);
    });

    it('forbids comparing applications of another recruiters offer', function () {
        $other = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $other->id]);
        $apps = Application::factory()->count(2)->create(['job_offer_id' => $job->id]);

        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->postJson('/api/applications/compare', [
            'ids' => $apps->pluck('id')->all(),
        ])->assertStatus(403);
    });

    it('requires between 2 and 4 ids', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $app = Application::factory()->create(['job_offer_id' => $job->id]);

        Sanctum::actingAs($recruiter);

        $this->postJson('/api/applications/compare', ['ids' => [$app->id]])
            ->assertStatus(422);
    });

    it('forbids a candidate from comparing applications', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $apps = Application::factory()->count(2)->create(['job_offer_id' => $job->id]);

        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->postJson('/api/applications/compare', [
            'ids' => $apps->pluck('id')->all(),
        ])->assertStatus(403);
    });
});

describe('application suggestions', function () {
    it('suggests other candidates with overlapping skills across open offers', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $otherJob = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $refused = Application::factory()->create(['job_offer_id' => $job->id, 'status' => 'refused']);
        ApplicationAnalysis::factory()->create([
            'application_id' => $refused->id,
            'job_offer_id' => $job->id,
            'matching_score' => 70,
            'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
        ]);

        $matching = Application::factory()->create(['job_offer_id' => $otherJob->id]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $matching->id,
            'job_offer_id' => $otherJob->id,
            'matching_score' => 88,
            'matched_keywords' => ['PHP', 'Laravel'],
        ]);

        $unrelated = Application::factory()->create(['job_offer_id' => $otherJob->id]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $unrelated->id,
            'job_offer_id' => $otherJob->id,
            'matching_score' => 95,
            'matched_keywords' => ['React', 'Node.js'],
        ]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson("/api/applications/{$refused->id}/suggestions");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        expect($response->json('data.0.id'))->toBe($matching->id);
    });

    it('ranks suggestions by keyword overlap then score', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $otherJob = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $refused = Application::factory()->create(['job_offer_id' => $job->id, 'status' => 'refused']);
        ApplicationAnalysis::factory()->create([
            'application_id' => $refused->id,
            'job_offer_id' => $job->id,
            'matching_score' => 60,
            'matched_keywords' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ]);

        $oneOverlap = Application::factory()->create(['job_offer_id' => $otherJob->id]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $oneOverlap->id,
            'job_offer_id' => $otherJob->id,
            'matching_score' => 99,
            'matched_keywords' => ['PHP'],
        ]);

        $twoOverlap = Application::factory()->create(['job_offer_id' => $otherJob->id]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $twoOverlap->id,
            'job_offer_id' => $otherJob->id,
            'matching_score' => 70,
            'matched_keywords' => ['PHP', 'Laravel'],
        ]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson("/api/applications/{$refused->id}/suggestions");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        expect($response->json('data.0.id'))->toBe($twoOverlap->id);
        expect($response->json('data.1.id'))->toBe($oneOverlap->id);
    });

    it('does not suggest the refused candidate themselves', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $otherJob = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $refused = Application::factory()->create(['job_offer_id' => $job->id, 'status' => 'refused']);
        ApplicationAnalysis::factory()->create([
            'application_id' => $refused->id,
            'job_offer_id' => $job->id,
            'matching_score' => 70,
            'matched_keywords' => ['PHP', 'Laravel'],
        ]);

        Application::factory()->create([
            'candidate_id' => $refused->candidate_id,
            'job_offer_id' => $otherJob->id,
        ]);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/applications/{$refused->id}/suggestions")
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    });

    it('forbids a recruiter from seeing suggestions for another recruiters application', function () {
        $other = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $other->id]);
        $app = Application::factory()->create(['job_offer_id' => $job->id]);

        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->getJson("/api/applications/{$app->id}/suggestions")
            ->assertStatus(403);
    });

    it('forbids a candidate from seeing suggestions', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        $app = Application::factory()->create(['job_offer_id' => $job->id]);

        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->getJson("/api/applications/{$app->id}/suggestions")
            ->assertStatus(403);
    });
});
