<?php

namespace App\Models;

use Database\Factories\InterviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Interview extends Model
{
    /** @use HasFactory<InterviewFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'scheduled_at',
        'link',
        'status',
        'score_technique',
        'score_communication',
        'score_motivation',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function agentConversation(): HasOne
    {
        return $this->hasOne(AgentConversation::class, 'context_id')
            ->where('context_type', 'interview_questions');
    }

    public function getAverageScoreAttribute(): ?float
    {
        $scores = array_filter([
            $this->score_technique,
            $this->score_communication,
            $this->score_motivation,
        ]);

        return $scores ? round(array_sum($scores) / count($scores), 2) : null;
    }
}
