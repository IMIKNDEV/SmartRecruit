<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentConversation>
 */
class AgentConversationFactory extends Factory
{
    protected $model = AgentConversation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'context_type' => 'general',
            'context_id' => null,
            'status' => 'active',
        ];
    }

    public function interviewQuestions(): static
    {
        return $this->state(fn (array $attributes) => [
            'context_type' => 'interview_questions',
        ]);
    }

    public function matching(): static
    {
        return $this->state(fn (array $attributes) => [
            'context_type' => 'matching',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
