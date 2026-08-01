<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'cv_path' => $this->cv_path,
            'cover_letter' => $this->cover_letter,
            'tags' => $this->tags,
            'notes' => $this->when($user?->isRecruiter(), $this->notes),
            'comments' => $this->when($user?->isRecruiter(), $this->comments),
            'candidate' => new UserResource($this->whenLoaded('candidate')),
            'job_offer' => new JobOfferResource($this->whenLoaded('jobOffer')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
