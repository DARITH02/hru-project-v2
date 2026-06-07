<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'student_code', 'group_id', 'major_id', 'status', 'blacklist_semesters'];

    protected $casts = [
        'blacklist_semesters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(ClassGroup::class, 'group_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function restoreHistories()
    {
        return $this->hasMany(StudentRestoreHistory::class, 'student_id');
    }

    public function isBlacklistedInSemester($academicYear, $semester)
    {
        $key = "{$academicYear}-{$semester}";
        $semesters = $this->blacklist_semesters ?? [];
        return in_array($key, $semesters);
    }

    public function blacklistInSemester($academicYear, $semester)
    {
        $key = "{$academicYear}-{$semester}";
        $semesters = $this->blacklist_semesters ?? [];
        if (!in_array($key, $semesters)) {
            $semesters[] = $key;
            $this->update(['blacklist_semesters' => $semesters, 'status' => 'blacklisted']);
        }
    }

    public function restoreInSemester($academicYear, $semester)
    {
        $key = "{$academicYear}-{$semester}";
        $semesters = $this->blacklist_semesters ?? [];
        $semesters = array_values(array_diff($semesters, [$key]));
        
        $newStatus = empty($semesters) ? 'active' : 'blacklisted';
        $this->update(['blacklist_semesters' => $semesters, 'status' => $newStatus]);
    }
}
