<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceQrToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_attendance_session_id',
        'teacher_id',
        'schedule_id',
        'subject_id',
        'attendance_date',
        'session_number',
        'token_hash',
        'expires_at',
        'used_at',
        'used_ip_address',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'session_number' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function attendanceSession()
    {
        return $this->belongsTo(TeacherAttendanceSession::class, 'teacher_attendance_session_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TeacherSchedule::class);
    }
}
