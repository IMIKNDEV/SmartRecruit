<?php

use App\Models\Application;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('scheduling', function () {
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

    it('recruiter can schedule an interview', function () {
        $response = $this->postJson("/api/applications/{$this->application->id}/interviews", [
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'scheduled_at', 'link', 'status']])
            ->assertJsonPath('data.status', 'scheduled');
    });

    it('validates scheduled_at is in the future', function () {
        $response = $this->postJson("/api/applications/{$this->application->id}/interviews", [
            'scheduled_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_at']);
    });

    it('validates link is a valid URL', function () {
        $response = $this->postJson("/api/applications/{$this->application->id}/interviews", [
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'link' => 'not-a-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['link']);
    });

    it('interview schedule is optional', function () {
        $response = $this->postJson("/api/applications/{$this->application->id}/interviews", [
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(201);
    });

    it('candidate cannot schedule an interview', function () {
        Sanctum::actingAs($this->candidate);

        $response = $this->postJson("/api/applications/{$this->application->id}/interviews", [
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(403);
    });
});

describe('completion and scoring', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'interview',
        ]);
        $this->interview = Interview::factory()->create([
            'application_id' => $this->application->id,
            'status' => 'scheduled',
        ]);
        Sanctum::actingAs($this->recruiter);
    });

    it('recruiter can complete interview with scores', function () {
        $response = $this->putJson("/api/interviews/{$this->interview->id}/complete", [
            'score_technique' => 4,
            'score_communication' => 5,
            'score_motivation' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.score_technique', 4)
            ->assertJsonPath('data.score_communication', 5)
            ->assertJsonPath('data.score_motivation', 3);
    });

    it('validates scores between 1 and 5', function () {
        $response = $this->putJson("/api/interviews/{$this->interview->id}/complete", [
            'score_technique' => 0,
            'score_communication' => 6,
            'score_motivation' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score_technique', 'score_communication']);
    });

    it('validates all scores are required on complete', function () {
        $response = $this->putJson("/api/interviews/{$this->interview->id}/complete", [
            'score_technique' => 4,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score_communication', 'score_motivation']);
    });
});

describe('cancellation', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
            'status' => 'interview',
        ]);
        $this->interview = Interview::factory()->create([
            'application_id' => $this->application->id,
            'status' => 'scheduled',
        ]);
        Sanctum::actingAs($this->recruiter);
    });

    it('recruiter can cancel an interview', function () {
        $response = $this->putJson("/api/interviews/{$this->interview->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    });
});

describe('listing', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create();
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
        ]);
        Interview::factory()->count(2)->create(['application_id' => $this->application->id]);
        Sanctum::actingAs($this->recruiter);
    });

    it('recruiter can list interviews for an application', function () {
        $response = $this->getJson("/api/applications/{$this->application->id}/interviews");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    });
});
