<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'matching_score' => $this->matching_score,
            'matched_keywords' => $this->matched_keywords,
            'missing_keywords' => $this->missing_keywords,
            'strengths' => $this->strengths,
            'gaps' => $this->gaps,
            'years_experience' => $this->years_experience,
            'education_level' => $this->education_level,
            'languages' => $this->languages,
            'recommendation' => $this->recommendation,
        ];
    }
}
