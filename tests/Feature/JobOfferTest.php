<?php

use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('public access', function () {
    it('lists active job offers', function () {
        $recruiter = User::factory()->recruiter()->create();
        JobOffer::factory()->count(3)->create(['recruiter_id' => $recruiter->id]);

        $response = $this->getJson('/api/job-offers');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('shows a single job offer', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);

        $response = $this->getJson("/api/job-offers/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'title', 'description', 'tech_stack', 'contract_type', 'salary', 'deadline', 'status']]);
    });

    it('returns 404 for non-existent offer', function () {
        $response = $this->getJson('/api/job-offers/99999');

        $response->assertStatus(404);
    });
});

describe('creation', function () {
    it('recruiter can create a job offer', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/job-offers', [
            'title' => 'Laravel Developer',
            'description' => 'We are looking for a talented Laravel developer to join our team. Must have experience with PHP, MySQL, and REST APIs.',
            'tech_stack' => 'PHP, Laravel, MySQL, Docker',
            'contract_type' => 'CDI',
            'salary' => 50000,
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Laravel Developer')
            ->assertJsonStructure(['data' => ['id', 'title', 'description', 'tech_stack', 'contract_type', 'salary', 'deadline', 'status']]);
    });

    it('validates required fields on create', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/job-offers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'tech_stack', 'contract_type', 'deadline']);
    });

    it('validates deadline is after today', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/job-offers', [
            'title' => 'Laravel Developer',
            'description' => 'We are looking for a talented Laravel developer to join our team. Must have experience with PHP, MySQL, and REST APIs.',
            'tech_stack' => 'PHP, Laravel',
            'contract_type' => 'CDI',
            'deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deadline']);
    });

    it('validates contract_type is valid', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/job-offers', [
            'title' => 'Test',
            'description' => 'A longer description that meets the minimum length requirement for testing purposes.',
            'tech_stack' => 'PHP',
            'contract_type' => 'INVALID',
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contract_type']);
    });

    it('candidate cannot create a job offer', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $response = $this->postJson('/api/job-offers', [
            'title' => 'Laravel Developer',
            'description' => 'We are looking for a talented Laravel developer to join our team. Must have experience with PHP, MySQL, and REST APIs.',
            'tech_stack' => 'PHP, Laravel',
            'contract_type' => 'CDI',
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    });

    it('unauthenticated user cannot create', function () {
        $response = $this->postJson('/api/job-offers', [
            'title' => 'Laravel Developer',
            'description' => 'description here',
            'tech_stack' => 'PHP',
            'contract_type' => 'CDI',
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
    });
});

describe('update', function () {
    it('recruiter can update own job offer', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        Sanctum::actingAs($recruiter);

        $response = $this->putJson("/api/job-offers/{$job->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description that is long enough to pass validation rules for the job offer.',
            'tech_stack' => 'PHP, Laravel, Vue.js',
            'contract_type' => 'CDD',
            'deadline' => now()->addMonths(2)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    });

    it('recruiter cannot update another recruiters offer', function () {
        $recruiter1 = User::factory()->recruiter()->create();
        $recruiter2 = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter1->id]);
        Sanctum::actingAs($recruiter2);

        $response = $this->putJson("/api/job-offers/{$job->id}", [
            'title' => 'Hacked Title',
            'description' => 'This should not work because ownership is enforced.',
            'tech_stack' => 'PHP',
            'contract_type' => 'CDI',
            'deadline' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    });

    it('validates update fields', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        Sanctum::actingAs($recruiter);

        $response = $this->putJson("/api/job-offers/{$job->id}", [
            'title' => '',
        ]);

        $response->assertStatus(422);
    });
});

describe('deletion', function () {
    it('recruiter can soft-delete own offer', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        Sanctum::actingAs($recruiter);

        $response = $this->deleteJson("/api/job-offers/{$job->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($job);
    });

    it('soft-deleted offer returns 404', function () {
        $recruiter = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $recruiter->id]);
        Sanctum::actingAs($recruiter);
        $this->deleteJson("/api/job-offers/{$job->id}");

        $response = $this->getJson("/api/job-offers/{$job->id}");

        $response->assertStatus(404);
    });

    it('recruiter cannot delete another recruiters offer', function () {
        $owner = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        $job = JobOffer::factory()->create(['recruiter_id' => $owner->id]);
        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/job-offers/{$job->id}");

        $response->assertStatus(403);
    });
});
