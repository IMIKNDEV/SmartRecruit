<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'tech_stack' => $this->tech_stack,
            'tech_stack_array' => $this->tech_stack_array,
            'contract_type' => $this->contract_type,
            'salary' => $this->salary,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'status' => $this->status,
            'applications_count' => $this->whenCounted('applications'),
            'recruiter' => new UserResource($this->whenLoaded('recruiter')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
