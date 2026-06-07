<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceReport extends Model
{
    protected $fillable = [
        'report_type',
        'teacher_id',
        'department_id',
        'semester',
        'academic_year',
        'date_from',
        'date_to',
        'filters',
        'summary',
        'file_path',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'filters' => 'array',
        'summary' => 'array',
        'generated_at' => 'datetime',
    ];
}
