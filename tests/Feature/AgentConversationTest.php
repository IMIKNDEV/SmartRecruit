<?php

use App\Agents\ConversationalAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\JobOffer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('agent conversations', function () {
    it('requires authentication to list conversations', function () {
        $this->getJson('/api/agent-conversations')->assertStatus(401);
    });

    it('recruiter can create a conversation', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $response = $this->postJson('/api/agent-conversations', [
            'context_type' => 'general',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.context_type', 'general');
        $this->assertDatabaseHas('agent_conversations', ['user_id' => $recruiter->id]);
    });

    it('validates context_type', function () {
        $recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($recruiter);

        $this->postJson('/api/agent-conversations', [
            'context_type' => 'bogus',
        ])->assertStatus(422);
    });

    it('lists only own conversations', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();

        AgentConversation::factory()->create(['user_id' => $recruiter->id]);
        AgentConversation::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->getJson('/api/agent-conversations');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    });
});

describe('agent conversation messages', function () {
    it('lists messages of own conversation', function () {
        $recruiter = User::factory()->recruiter()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $recruiter->id]);
        AgentConversationMessage::factory()->create([
            'agent_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hello',
        ]);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/agent-conversations/{$conversation->id}/messages")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    });

    it('cannot list messages of another users conversation', function () {
        $recruiter = User::factory()->recruiter()->create();
        $other = User::factory()->recruiter()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/agent-conversations/{$conversation->id}/messages")
            ->assertStatus(403);
    });

    it('sends a message and persists the assistant reply', function () {
        ConversationalAgent::fake(['This is the assistant reply.']);

        $recruiter = User::factory()->recruiter()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $response = $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Tell me about this candidate',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.role', 'assistant')
            ->assertJsonPath('data.content', 'This is the assistant reply.');
        $this->assertDatabaseHas('agent_conversation_messages', [
            'agent_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Tell me about this candidate',
        ]);
    });

    it('validates content is required', function () {
        $recruiter = User::factory()->recruiter()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $recruiter->id]);

        Sanctum::actingAs($recruiter);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [])
            ->assertStatus(422);
    });
});

describe('candidate-aware chat', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->candidate = User::factory()->candidate()->create(['name' => 'Sara El Amrani']);
        $this->job = JobOffer::factory()->create(['recruiter_id' => $this->recruiter->id]);
        $this->application = Application::factory()->create([
            'candidate_id' => $this->candidate->id,
            'job_offer_id' => $this->job->id,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $this->application->id,
            'job_offer_id' => $this->job->id,
            'matching_score' => 85.00,
            'matched_keywords' => ['PHP', 'Laravel'],
            'missing_keywords' => ['Docker'],
        ]);
    });

    it('injects the candidate profile context when the message mentions a candidate name', function () {
        ConversationalAgent::fake(['Sara a un score de 85.00/100.']);

        $conversation = AgentConversation::factory()->create(['user_id' => $this->recruiter->id]);
        Sanctum::actingAs($this->recruiter);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Quel est le score de Sara El Amrani ?',
        ])->assertStatus(201);

        ConversationalAgent::assertPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Candidate profiles')
                && str_contains($prompt->prompt, 'Sara El Amrani')
                && str_contains($prompt->prompt, '85.00/100')
                && str_contains($prompt->prompt, 'compétences trouvées : PHP, Laravel')
                && str_contains($prompt->prompt, 'compétences manquantes : Docker')
        );
    });

    it('injects candidate context on generic candidate keywords', function () {
        ConversationalAgent::fake(['Voici les meilleurs profils.']);

        $conversation = AgentConversation::factory()->create(['user_id' => $this->recruiter->id]);
        Sanctum::actingAs($this->recruiter);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Quel est le meilleur profil ?',
        ])->assertStatus(201);

        ConversationalAgent::assertPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Candidate profiles')
                && str_contains($prompt->prompt, 'Sara El Amrani')
        );
    });

    it('does not inject candidate context for unrelated questions', function () {
        ConversationalAgent::fake(['Réponse générique.']);

        $conversation = AgentConversation::factory()->create(['user_id' => $this->recruiter->id]);
        Sanctum::actingAs($this->recruiter);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Comment créer une offre ?',
        ])->assertStatus(201);

        ConversationalAgent::assertNotPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Candidate profiles')
        );
    });

    it('never leaks candidates from another recruiters offers', function () {
        $other = User::factory()->recruiter()->create();
        $otherJob = JobOffer::factory()->create(['recruiter_id' => $other->id, 'title' => 'Offre Confidentielle']);
        $otherCandidate = User::factory()->candidate()->create(['name' => 'Nadia Confidentiel']);
        $otherApplication = Application::factory()->create([
            'candidate_id' => $otherCandidate->id,
            'job_offer_id' => $otherJob->id,
        ]);
        ApplicationAnalysis::factory()->create([
            'application_id' => $otherApplication->id,
            'job_offer_id' => $otherJob->id,
            'matching_score' => 99.00,
        ]);

        ConversationalAgent::fake(['Pas de données.']);

        $conversation = AgentConversation::factory()->create(['user_id' => $this->recruiter->id]);
        Sanctum::actingAs($this->recruiter);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Quel est le score de Nadia Confidentiel ?',
        ])->assertStatus(201);

        // The context block must be present (keyword match) but must not leak
        // the other recruiter's offer, candidate or score. The user message
        // itself legitimately contains "Nadia Confidentiel", so we only check
        // the injected context for leaks.
        ConversationalAgent::assertPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Candidate profiles')
                && ! str_contains($prompt->prompt, 'Offre Confidentielle')
                && ! str_contains($prompt->prompt, '99.00')
        );
    });

    it('does not inject candidate context for candidate users', function () {
        $conversation = AgentConversation::factory()->create(['user_id' => $this->candidate->id]);
        Sanctum::actingAs($this->candidate);

        ConversationalAgent::fake(['Je ne vois pas de profil.']);

        $this->postJson("/api/agent-conversations/{$conversation->id}/messages", [
            'content' => 'Quel est le score de Sara El Amrani ?',
        ])->assertStatus(201);

        ConversationalAgent::assertNotPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Candidate profiles')
        );
    });
});

describe('interview question generation', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        $this->job = JobOffer::factory()->create([
            'recruiter_id' => $this->recruiter->id,
            'tech_stack' => 'PHP, Laravel, MySQL',
        ]);
        $this->application = Application::factory()->create(['job_offer_id' => $this->job->id]);
    });

    it('requires authentication', function () {
        $this->postJson("/api/applications/{$this->application->id}/generate-questions")
            ->assertStatus(401);
    });

    it('recruiter can generate questions for own application', function () {
        ConversationalAgent::fake(['Q1: What is Eloquent?']);

        Sanctum::actingAs($this->recruiter);

        $response = $this->postJson("/api/applications/{$this->application->id}/generate-questions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['questions', 'conversation_id']])
            ->assertJsonPath('data.questions', 'Q1: What is Eloquent?');
    });

    it('candidate cannot generate questions', function () {
        $candidate = User::factory()->candidate()->create();
        Sanctum::actingAs($candidate);

        $this->postJson("/api/applications/{$this->application->id}/generate-questions")
            ->assertStatus(403);
    });

    it('recruiter cannot generate questions for another recruiters application', function () {
        $other = User::factory()->recruiter()->create();
        Sanctum::actingAs($other);

        $this->postJson("/api/applications/{$this->application->id}/generate-questions")
            ->assertStatus(403);
    });
});
