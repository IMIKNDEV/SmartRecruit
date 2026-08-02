<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'job_offer_id',
        'cv_path',
        'cover_letter',
        'tags',
        'status',
        'notes',
        'comments',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(ApplicationAnalysis::class);
    }

    public function agentConversation(): HasOne
    {
        return $this->hasOne(AgentConversation::class, 'context_id')
            ->where('context_type', 'interview_questions');
    }
}
