<?php

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('recruiter can batch-update status for own applications', function () {
    $recruiter = User::factory()->recruiter()->create();
    $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
    $applications = Application::factory()->count(3)->create([
        'job_offer_id' => $job->id,
        'status' => 'received',
    ]);
    Sanctum::actingAs($recruiter);

    $response = $this->putJson('/api/applications/status/batch', [
        'ids' => $applications->pluck('id')->toArray(),
        'status' => 'refused',
    ]);

    $response->assertStatus(200);

    foreach ($applications as $app) {
        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'status' => 'refused',
        ]);
    }
});

it('batch rejects application not owned by recruiter', function () {
    $recruiter = User::factory()->recruiter()->create();
    $otherRecruiter = User::factory()->recruiter()->create();
    $otherJob = JobOffer::factory()->create(['recruiter_id' => $otherRecruiter->id]);
    $application = Application::factory()->create(['job_offer_id' => $otherJob->id]);
    Sanctum::actingAs($recruiter);

    $response = $this->putJson('/api/applications/status/batch', [
        'ids' => [$application->id],
        'status' => 'refused',
    ]);

    $response->assertStatus(403);
});

it('validates batch requires ids array', function () {
    $recruiter = User::factory()->recruiter()->create();
    Sanctum::actingAs($recruiter);

    $response = $this->putJson('/api/applications/status/batch', [
        'status' => 'refused',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ids']);
});

it('validates batch status is a valid transition', function () {
    $recruiter = User::factory()->recruiter()->create();
    $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
    $application = Application::factory()->create([
        'job_offer_id' => $job->id,
        'status' => 'received',
    ]);
    Sanctum::actingAs($recruiter);

    $response = $this->putJson('/api/applications/status/batch', [
        'ids' => [$application->id],
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
