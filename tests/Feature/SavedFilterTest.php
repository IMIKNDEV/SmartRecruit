<?php

use App\Models\SavedFilter;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('listing', function () {
    it('recruiter can list their saved filters', function () {
        $recruiter = User::factory()->recruiter()->create();
        SavedFilter::factory()->count(3)->create(['recruiter_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson('/api/saved-filters');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'criteria']], 'meta']);
    });

    it('does not include filters belonging to other recruiters', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        SavedFilter::factory()->create(['recruiter_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson('/api/saved-filters');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    });
});

describe('creation', function () {
    it('recruiter can create a saved filter', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'Backend Laravel seniors',
            'criteria' => [
                'min_score' => 80,
                'tech_stack' => ['PHP', 'Laravel'],
                'contract_type' => 'CDI',
                'status' => 'received',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Backend Laravel seniors')
            ->assertJsonPath('data.criteria.min_score', 80)
            ->assertJsonStructure(['data' => ['id', 'name', 'criteria']]);

        $this->assertDatabaseHas('saved_filters', [
            'recruiter_id' => $recruiter->id,
            'name' => 'Backend Laravel seniors',
        ]);
    });

    it('validates name is required', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'criteria' => ['min_score' => 80],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('validates criteria is an array', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'My filter',
            'criteria' => 'PHP, Laravel',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['criteria']);
    });

    it('validates criteria.min_score is between 0 and 100', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'My filter',
            'criteria' => ['min_score' => 150],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['criteria.min_score']);
    });

    it('validates criteria.contract_type is valid', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'My filter',
            'criteria' => ['contract_type' => 'Invalid'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['criteria.contract_type']);
    });

    it('validates criteria.status is valid', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'My filter',
            'criteria' => ['status' => 'Invalid'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['criteria.status']);
    });

    it('candidate cannot create a saved filter', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $response = $this->postJson('/api/saved-filters', [
            'name' => 'My filter',
            'criteria' => ['min_score' => 80],
        ]);

        $response->assertStatus(403);
    });
});

describe('viewing', function () {
    it('recruiter can view their saved filter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson("/api/saved-filters/{$filter->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $filter->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'criteria']]);
    });

    it('cannot view a saved filter belonging to another recruiter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson("/api/saved-filters/{$filter->id}");

        $response->assertStatus(403);
    });
});

describe('updating', function () {
    it('recruiter can update their saved filter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->putJson("/api/saved-filters/{$filter->id}", [
            'name' => 'Renamed filter',
            'criteria' => ['min_score' => 90],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Renamed filter')
            ->assertJsonPath('data.criteria.min_score', 90);

        $this->assertDatabaseHas('saved_filters', [
            'id' => $filter->id,
            'name' => 'Renamed filter',
        ]);
    });

    it('cannot update a saved filter belonging to another recruiter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->putJson("/api/saved-filters/{$filter->id}", [
            'name' => 'Hijacked',
        ]);

        $response->assertStatus(403);
    });
});

describe('deleting', function () {
    it('recruiter can delete their saved filter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->deleteJson("/api/saved-filters/{$filter->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('saved_filters', ['id' => $filter->id]);
    });

    it('cannot delete a saved filter belonging to another recruiter', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        $filter = SavedFilter::factory()->create(['recruiter_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->deleteJson("/api/saved-filters/{$filter->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('saved_filters', ['id' => $filter->id]);
    });
});
