<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'teacher_attendance_session_id',
        'teacher_id',
        'actor_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TeacherAttendanceSession::class, 'teacher_attendance_session_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
