<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Schema;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'teacher_code', 'department_id', 'specialization', 'status', 'telegram_id'];

    protected static function booted(): void
    {
        static::deleting(function (Teacher $teacher) {
            $teacher->photos()->each->delete();
        });
    }

    public static function hasTeacherCodeColumn(): bool
    {
        return Schema::hasColumn('teachers', 'teacher_code');
    }

    public static function generateTeacherCode(): ?string
    {
        if (!self::hasTeacherCodeColumn()) {
            return null;
        }

        do {
            $code = 'TCH-' . random_int(100000, 999999);
        } while (self::where('teacher_code', $code)->exists());

        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function semesterAssignments()
    {
        return $this->hasManyThrough(SemesterAssignment::class, ClassRoom::class, 'teacher_id', 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(TeacherSchedule::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(TeacherAttendanceSession::class);
    }

    public function attendanceCorrections()
    {
        return $this->hasMany(TeacherAttendanceCorrection::class);
    }

    public function classChangeRequests()
    {
        return $this->hasMany(TeacherClassChangeRequest::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')->latest();
    }

    public function primaryPhoto(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')->where('is_primary', true)->latestOfMany();
    }
}
