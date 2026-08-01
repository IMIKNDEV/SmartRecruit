<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'link' => $this->link,
            'status' => $this->status,
            'score_technique' => $this->score_technique,
            'score_communication' => $this->score_communication,
            'score_motivation' => $this->score_motivation,
            'average_score' => $this->average_score,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
