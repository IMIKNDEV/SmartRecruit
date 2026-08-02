<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;

#[Provider('groq')]
#[Model('llama-3.3-70b-versatile')]
class ConversationalAgent implements Agent, Conversational
{
    use Promptable;

    /**
     * Prior messages, injected by the caller from persisted conversation rows.
     *
     * @var array<int, array{role: string, content: string}>
     */
    public array $history = [];

    public function instructions(): string
    {
        return 'You are a helpful recruitment assistant for SmartRecruit.';
    }

    /**
     * The provider prepends these messages to instructions() + the new prompt.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return array_map(
            fn (array $message) => $message['role'] === 'user'
                ? new UserMessage($message['content'])
                : new AssistantMessage($message['content']),
            $this->history,
        );
    }

    public function ask(string $prompt): string
    {
        return $this->prompt($prompt)->text;
    }

    public function generateQuestions(string $techStack): string
    {
        return $this->ask(
            'Generate 3-5 technical interview questions for a candidate '
            ."applying to a position requiring: {$techStack}. "
            .'Tailor the questions to assess hands-on experience, '
            .'not just theoretical knowledge.'
        );
    }
}
