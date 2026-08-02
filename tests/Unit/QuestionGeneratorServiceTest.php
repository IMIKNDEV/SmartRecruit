<?php

use App\Agents\ConversationalAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use App\Services\QuestionGeneratorService;
use Laravel\Sanctum\Sanctum;

describe('QuestionGeneratorService', function () {
    beforeEach(function () {
        $this->recruiter = User::factory()->recruiter()->create();
        Sanctum::actingAs($this->recruiter);

        $this->job = JobOffer::factory()->create([
            'recruiter_id' => $this->recruiter->id,
            'tech_stack' => 'PHP, Laravel, MySQL',
        ]);
        $this->application = Application::factory()->create(['job_offer_id' => $this->job->id]);
    });

    it('generates questions and persists the conversation and messages', function () {
        ConversationalAgent::fake(['1. Explain the Laravel service container.']);

        $result = (new QuestionGeneratorService)->generate($this->application);

        expect($result['questions'])->toBe('1. Explain the Laravel service container.');
        expect($result['conversation_id'])->toBeInt();

        $conversation = AgentConversation::findOrFail($result['conversation_id']);
        expect($conversation->user_id)->toBe($this->recruiter->id);
        expect($conversation->context_type)->toBe('interview_questions');
        expect($conversation->context_id)->toBe($this->application->id);

        $messages = AgentConversationMessage::where('agent_conversation_id', $conversation->id)->get();
        expect($messages)->toHaveCount(2);
        expect($messages[0]->role)->toBe('user');
        expect($messages[0]->content)->toContain('PHP, Laravel, MySQL');
        expect($messages[1]->role)->toBe('assistant');
        expect($messages[1]->content)->toBe('1. Explain the Laravel service container.');

        ConversationalAgent::assertPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'PHP, Laravel, MySQL')
        );
    });

    it('reuses an existing conversation for the same application', function () {
        ConversationalAgent::fake(['First answer', 'Second answer']);

        $service = new QuestionGeneratorService;
        $first = $service->generate($this->application);
        $second = $service->generate($this->application);

        expect($second['conversation_id'])->toBe($first['conversation_id']);
        expect(AgentConversation::count())->toBe(1);
        expect(AgentConversationMessage::count())->toBe(4);
    });

    it('sends a follow-up message and persists both sides', function () {
        ConversationalAgent::fake(['Initial answer']);
        $service = new QuestionGeneratorService;
        $generated = $service->generate($this->application);

        $conversation = AgentConversation::findOrFail($generated['conversation_id']);

        ConversationalAgent::fake(['Follow-up answer']);
        $reply = $service->sendMessage($conversation, 'Can you add a follow-up question?');

        expect($reply->role)->toBe('assistant');
        expect($reply->content)->toBe('Follow-up answer');

        $messages = AgentConversationMessage::where('agent_conversation_id', $conversation->id)->get();
        expect($messages)->toHaveCount(4);
        expect($messages[2]->role)->toBe('user');
        expect($messages[2]->content)->toBe('Can you add a follow-up question?');
        expect($messages[3]->role)->toBe('assistant');
        expect($messages[3]->content)->toBe('Follow-up answer');
    });
});
