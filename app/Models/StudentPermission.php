<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPermission extends Model
{
    protected $fillable = [
        'student_id',
        'start_date',
        'end_date',
        'reason',
        'type'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
