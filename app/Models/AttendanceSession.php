<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $table = 'attendance_sessions';

    protected $fillable = [
        'class_id', 
        'qr_token', 
        'start_time', 
        'end_time', 
        'checkin_open_time', 
        'checkin_close_time',
        'semester',
        'academic_year',
        'status',
        'telegram_sent'
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }
}
