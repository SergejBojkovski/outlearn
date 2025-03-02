<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentData extends Model
{
    protected $table = "student_data";
    
    protected $fillable = [
        'user_id',
        'bio',
        'academic_level',
        'interests',
        'learning_goals',
        'educational_background'
    ];
    
    /**
     * Get the user that this student data belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
