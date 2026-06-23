<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Document extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'description',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isOwnedByTeacher(Teacher $teacher): bool
    {
        return $this->teacher_id === $teacher->id;
    }

    public function isVisibleToStudent(Student $student): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $class = $this->classRoom;
        if (!$class) {
            return false;
        }

        if ($class->group_id && $class->group_id === $student->group_id) {
            return true;
        }

        if (!Schema::hasTable('class_class_group')) {
            return false;
        }

        return $class->groups()
            ->where('class_groups.id', $student->group_id)
            ->exists();
    }
}
