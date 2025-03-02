<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProfessorDataResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'bio' => $this->bio,
            'specialization' => $this->specialization,
            'years_of_experience' => $this->years_of_experience,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ]);
    }
} 