<?php

namespace App\Models;

use Database\Factories\AgentConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AgentConversation extends Model
{
    /** @use HasFactory<AgentConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'context_type', 'context_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class);
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }
}
