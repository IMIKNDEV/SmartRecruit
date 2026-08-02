<?php

namespace App\Services;

use App\Agents\ConversationalAgent;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\Application;

class QuestionGeneratorService
{
    /**
     * Generate interview questions for an application's tech stack and persist
     * the conversation (user + assistant messages).
     *
     * @return array{questions: string, conversation_id: int}
     */
    public function generate(Application $application): array
    {
        $job = $application->jobOffer;

        $conversation = AgentConversation::firstOrCreate([
            'user_id' => auth()->id(),
            'context_type' => 'interview_questions',
            'context_id' => $application->id,
        ]);

        $userContent = "Generate interview questions for tech stack: {$job->tech_stack}";
        $history = $this->historyFor($conversation);

        $this->storeMessage($conversation, 'user', $userContent);

        $questions = $this->askWithHistory($history, $userContent);

        $this->storeMessage($conversation, 'assistant', $questions);

        return [
            'questions' => $questions,
            'conversation_id' => $conversation->id,
        ];
    }

    /**
     * Send a turn in an existing conversation and persist both messages.
     */
    public function sendMessage(AgentConversation $conversation, string $content): AgentConversationMessage
    {
        $history = $this->historyFor($conversation);

        $this->storeMessage($conversation, 'user', $content);

        $reply = $this->askWithHistory($history, $content);

        return $this->storeMessage($conversation, 'assistant', $reply);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    protected function historyFor(AgentConversation $conversation): array
    {
        return AgentConversationMessage::where('agent_conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (AgentConversationMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->toArray();
    }

    protected function askWithHistory(array $history, string $prompt): string
    {
        $agent = new ConversationalAgent;
        $agent->history = $history;

        return $agent->ask($prompt);
    }

    protected function storeMessage(AgentConversation $conversation, string $role, string $content): AgentConversationMessage
    {
        return AgentConversationMessage::create([
            'agent_conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);
    }
}
