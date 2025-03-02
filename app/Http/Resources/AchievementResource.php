<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AchievementResource extends BaseResource
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
            'description' => $this->description,
            'criteria' => $this->criteria,
            'badge_url' => $this->badge_url,
        ]);
    }
} 