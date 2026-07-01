<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSubjectGradeHistory extends Model
{
    protected $fillable = [
        'semester_gpa_history_id',
        'student_id',
        'assignment_id',
        'class_id',
        'subject_id',
        'class_name',
        'subject_name',
        'subject_code',
        'credit',
        'attendance_score',
        'midterm_score',
        'assignment_score',
        'final_score',
        'total_score',
        'letter_grade',
        'grade_point',
        'quality_points',
        'notes',
        'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];
}
