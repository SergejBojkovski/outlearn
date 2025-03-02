<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class StudentDataResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'education_level' => $this->education_level,
            'interests' => $this->interests,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ]);
    }
} 