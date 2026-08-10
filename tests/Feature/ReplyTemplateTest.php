<?php

use App\Models\User;
use App\Support\ReplyTemplates;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

describe('reply templates', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('lists the default templates', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->getJson('/api/reply-templates');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $keys = collect($response->json('data'))->pluck('key')->all();
        expect($keys)->toBe(['follow_up', 'refusal']);
    });

    it('lets the recruiter edit a template and persists the override', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->putJson('/api/reply-templates/refusal', [
            'content' => 'Bonjour {candidate}, votre candidature a été retenue pour un entretien.',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'key' => 'refusal',
                'content' => 'Bonjour {candidate}, votre candidature a été retenue pour un entretien.',
            ]);

        expect(ReplyTemplates::get('refusal')['content'])->toBe('Bonjour {candidate}, votre candidature a été retenue pour un entretien.');
    });

    it('returns 404 for an unknown template key', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->putJson('/api/reply-templates/unknown', ['content' => 'test'])
            ->assertStatus(404);
    });

    it('validates content is required', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->putJson('/api/reply-templates/refusal', ['content' => ''])
            ->assertStatus(422);
    });

    it('forbids a candidate from editing templates', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->putJson('/api/reply-templates/refusal', ['content' => 'test'])
            ->assertStatus(403);
    });
});
