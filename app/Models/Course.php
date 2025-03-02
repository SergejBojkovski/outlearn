<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = ["title", "description"];


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "courses_users")->wherePivot('role', 'student');
    }

    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "course_proffesor")->wherePivot('role', 'professor');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
}
