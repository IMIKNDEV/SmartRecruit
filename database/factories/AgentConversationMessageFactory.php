<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentConversationMessage>
 */
class AgentConversationMessageFactory extends Factory
{
    protected $model = AgentConversationMessage::class;

    public function definition(): array
    {
        return [
            'agent_conversation_id' => AgentConversation::factory(),
            'role' => 'user',
            'content' => fake()->paragraph(),
            'metadata' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'system',
        ]);
    }

    public function withMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => ['score' => 85, 'matched_keywords' => ['PHP', 'Laravel']],
        ]);
    }
}
