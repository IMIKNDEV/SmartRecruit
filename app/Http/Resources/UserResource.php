<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'badges' => BadgeResource::collection($this->whenLoaded('badges')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
