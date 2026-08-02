<?php

use App\Agents\ConversationalAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\Application;
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
