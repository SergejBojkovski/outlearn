<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentData extends Model
{
    protected $table = "student_data";
    
    protected $fillable = [
        'user_id',
        'student_number',
        'enrollment_date',
        'graduation_date',
        'student_status'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
