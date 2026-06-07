<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'class_group_id',
        'room_name',
        'schedule_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'session_number',
        'check_in_opens_at',
        'check_in_closes_at',
        'check_out_opens_at',
        'check_out_closes_at',
        'semester',
        'academic_year',
        'status',
        'source',
        'source_attendance_session_id',
        'original_schedule_id',
        'created_by',
        'approved_by',
        'remarks',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'session_number' => 'integer',
        'check_in_opens_at' => 'datetime',
        'check_in_closes_at' => 'datetime',
        'check_out_opens_at' => 'datetime',
        'check_out_closes_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
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

    public function attendanceSession()
    {
        return $this->hasOne(TeacherAttendanceSession::class, 'schedule_id');
    }

    public function sourceAttendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class, 'source_attendance_session_id');
    }
}
