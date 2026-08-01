<?php

namespace App\Models;

use Database\Factories\JobOfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOffer extends Model
{
    /** @use HasFactory<JobOfferFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recruiter_id',
        'title',
        'description',
        'tech_stack',
        'contract_type',
        'salary',
        'deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function getTechStackArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->tech_stack));
    }
}
