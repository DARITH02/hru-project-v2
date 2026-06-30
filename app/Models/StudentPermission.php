<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentPermission extends Model
{
    protected $fillable = [
        'student_id',
        'attendance_session_id',
        'start_date',
        'end_date',
        'reason',
        'type',
        'status',
        'requested_by',
        'requested_by_teacher_id',
        'expires_at',
        'approved_at',
        'approved_by',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('approved', function (Builder $builder) {
            $builder->where('status', 'approved');
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function createdBy()
    {
        return $this->requestedBy();
    }

    public function requestedByTeacher()
    {
        return $this->belongsTo(Teacher::class, 'requested_by_teacher_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isExpiredPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at && $this->expires_at->isPast();
    }
}
