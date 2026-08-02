<?php

namespace App\Models;

use Database\Factories\ApplicationAnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAnalysis extends Model
{
    /** @use HasFactory<ApplicationAnalysisFactory> */
    use HasFactory;

    protected $table = 'application_analysis';

    protected $fillable = [
        'application_id',
        'job_offer_id',
        'matching_score',
        'matched_keywords',
        'missing_keywords',
    ];

    protected function casts(): array
    {
        return [
            'matching_score' => 'decimal:2',
            'matched_keywords' => 'array',
            'missing_keywords' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
