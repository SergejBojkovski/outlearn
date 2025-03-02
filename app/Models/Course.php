<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'title', 
        'description',
        'category_id',
        'image_url',
        'duration',
        'price',
        'status'
    ];

    /**
     * Get the category that the course belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get students enrolled in the course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "courses_users")->wherePivot('role', 'student');
    }

    /**
     * Get professors teaching the course.
     */
    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "course_proffesor")->wherePivot('role', 'professor');
    }

    /**
     * Get the modules for the course.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
    
    /**
     * Get all users enrolled in this course.
     */
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    
    /**
     * Get achievements associated with this course.
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}
