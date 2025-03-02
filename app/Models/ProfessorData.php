<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessorData extends Model
{
    //

    protected $table = "professors_data";
    
    protected $fillable = [
        'user_id',
        'department',
        'specialization',
        'biography',
        'expertise',
        'teaching_since'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
