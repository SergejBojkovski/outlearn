<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'image_url',
        'condition_value',
        'points',
        'course_id',
        'module_id'
    ];
    
    /**
     * Get the users who have earned this achievement.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('awarded_at')
            ->withTimestamps();
    }
    
    /**
     * Get the course associated with this achievement.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Get the module associated with this achievement.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
