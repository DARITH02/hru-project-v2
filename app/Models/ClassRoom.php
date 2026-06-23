<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['name', 'subject_id', 'teacher_id', 'room_number', 'schedule', 'status', 'academic_year', 'semester', 'group_id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function groups()
    {
        // Check if pivot table exists to avoid 500 errors during migration transitions
        if (!\Illuminate\Support\Facades\Schema::hasTable('class_class_group')) {
            return $this->belongsToMany(ClassGroup::class, 'classes', 'id', 'group_id'); // Fallback to legacy column
        }
        return $this->belongsToMany(ClassGroup::class, 'class_class_group', 'class_room_id', 'class_group_id');
    }

    /**
     * Get all students associated with this class.
     * Works with both the new pivot relationship and the legacy group_id.
     */
    public function getAllStudentsAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('class_class_group')) {
            $groupIds = $this->groups->pluck('id');
            if ($groupIds->isNotEmpty()) {
                return Student::whereIn('group_id', $groupIds)->get();
            }
        }
        
        // Fallback to legacy group_id
        if ($this->group_id) {
            return Student::where('group_id', $this->group_id)->get();
        }

        return collect();
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'group_id', 'group_id');
    }

    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class, 'class_id');
    }

    public function semesterAssignments()
    {
        return $this->hasMany(SemesterAssignment::class, 'class_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'class_id');
    }
}
