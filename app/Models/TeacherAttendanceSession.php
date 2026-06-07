<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'schedule_id',
        'subject_id',
        'class_id',
        'class_group_id',
        'room_name',
        'attendance_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'session_number',
        'check_in_time',
        'check_out_time',
        'auto_check_in_source_session_id',
        'attendance_status',
        'check_in_method',
        'check_out_method',
        'late_minutes',
        'early_leave_minutes',
        'teaching_duration_minutes',
        'actual_teaching_hours',
        'attendance_percentage',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'remarks',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'session_number' => 'integer',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'actual_teaching_hours' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TeacherSchedule::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function logs()
    {
        return $this->hasMany(TeacherAttendanceLog::class, 'teacher_attendance_session_id');
    }

    public function autoCheckInSourceSession()
    {
        return $this->belongsTo(self::class, 'auto_check_in_source_session_id');
    }

    public function qrTokens()
    {
        return $this->hasMany(TeacherAttendanceQrToken::class, 'teacher_attendance_session_id');
    }
}
