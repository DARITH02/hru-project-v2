<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSemesterGpaHistory extends Model
{
    protected $fillable = [
        'student_id',
        'class_group_id',
        'major_id',
        'student_name',
        'student_code',
        'class_group_name',
        'major_name',
        'academic_year',
        'year_level',
        'semester',
        'total_credits',
        'total_grade_points',
        'semester_gpa',
        'cumulative_credits',
        'cumulative_grade_points',
        'cumulative_gpa',
        'result_status',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function subjectGrades()
    {
        return $this->hasMany(StudentSubjectGradeHistory::class, 'semester_gpa_history_id');
    }
}
