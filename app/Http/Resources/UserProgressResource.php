<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserProgressResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'user_id' => $this->user_id,
            'lesson_id' => $this->lesson_id,
            'completed' => $this->completed,
            'completion_date' => $this->completion_date,
            'user' => new UserResource($this->whenLoaded('user')),
            'lesson' => new LessonResource($this->whenLoaded('lesson')),
        ]);
    }
} 