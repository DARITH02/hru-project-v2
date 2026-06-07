<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClassChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'schedule_id',
        'request_type',
        'requested_date',
        'requested_start_time',
        'requested_end_time',
        'requested_room_name',
        'reason',
        'status',
        'replacement_schedule_id',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'requested_start_time' => 'datetime',
        'requested_end_time' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TeacherSchedule::class);
    }

    public function replacementSchedule()
    {
        return $this->belongsTo(TeacherSchedule::class, 'replacement_schedule_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
