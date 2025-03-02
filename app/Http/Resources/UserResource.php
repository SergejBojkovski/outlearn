<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'email' => $this->email,
            'profile_picture' => $this->profile_picture,
            'email_verified_at' => $this->email_verified_at,
            'role' => new RoleResource($this->whenLoaded('role')),
            'student_data' => new StudentDataResource($this->whenLoaded('studentData')),
            'professor_data' => new ProfessorDataResource($this->whenLoaded('professorData')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ]);
    }
} 