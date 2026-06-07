<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SemesterAssignment extends Model
{
    protected $fillable = [
        'class_id', 'academic_year', 'semester',
        'start_date', 'end_date',
        'holiday_start', 'holiday_end',
        'status', 'notes',
        'admin_score', 'teacher_score', 'grading_status', 'grading_notes',
        'final_attendance_rate', 'final_total_sessions', 'finalized_at'
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'holiday_start' => 'date',
        'holiday_end'   => 'date',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /** Auto-compute end_date = start_date + 4 months */
    public static function computeEndDate($startDate): Carbon
    {
        return Carbon::parse($startDate)->addMonths(4);
    }

    /** Auto-compute holiday_end = holiday_start + 3 weeks */
    public static function computeHolidayEnd($holidayStart): Carbon
    {
        return Carbon::parse($holidayStart)->addWeeks(3);
    }

    /** Duration in weeks (excluding holiday) */
    public function getActiveDaysAttribute(): int
    {
        $total = Carbon::parse($this->start_date)->diffInDays($this->end_date);
        $holiday = 0;
        if ($this->holiday_start && $this->holiday_end) {
            $holiday = Carbon::parse($this->holiday_start)->diffInDays($this->holiday_end);
        }
        return max(0, $total - $holiday);
    }

    /** Progress percentage through the semester */
    public function getProgressAttribute(): int
    {
        $now = now();
        if ($now->lt($this->start_date)) return 0;
        if ($now->gt($this->end_date)) return 100;
        $elapsed = Carbon::parse($this->start_date)->diffInDays($now);
        $total = Carbon::parse($this->start_date)->diffInDays($this->end_date);
        return $total > 0 ? (int) round(($elapsed / $total) * 100) : 0;
    }

    /** Whether we're currently in the holiday window */
    public function getInHolidayAttribute(): bool
    {
        if (!$this->holiday_start || !$this->holiday_end) return false;
        return now()->between(
            Carbon::parse($this->holiday_start),
            Carbon::parse($this->holiday_end)
        );
    }
}
