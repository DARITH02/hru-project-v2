<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRestoreHistory extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year',
        'semester',
        'restored_by',
        'reason'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
