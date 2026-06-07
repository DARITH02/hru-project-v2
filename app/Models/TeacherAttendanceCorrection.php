<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'attendance_session_id',
        'schedule_id',
        'request_type',
        'requested_check_in_time',
        'requested_check_out_time',
        'requested_status',
        'reason',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'requested_check_in_time' => 'datetime',
        'requested_check_out_time' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendanceSession()
    {
        return $this->belongsTo(TeacherAttendanceSession::class, 'attendance_session_id');
    }

    public function schedule()
    {
        return $this->belongsTo(TeacherSchedule::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
