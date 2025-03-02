<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorData extends Model
{
    //

    protected $table = "professors_data";
    
    protected $fillable = [
        'user_id',
        'bio',
        'qualifications',
        'areas_of_expertise',
        'teaching_philosophy',
        'office_hours',
        'contact_information'
    ];
    
    /**
     * Get the user that this professor data belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
