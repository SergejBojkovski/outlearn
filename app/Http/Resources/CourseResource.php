<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CourseResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'title' => $this->title,
            'description' => $this->description,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'students_count' => $this->whenCounted('students'),
            'professors_count' => $this->whenCounted('professors'),
            'students' => UserResource::collection($this->whenLoaded('students')),
            'professors' => UserResource::collection($this->whenLoaded('professors')),
        ]);
    }
} 