<?php

namespace App\Services;

use App\Models\SemesterAssignment;
use App\Models\Student;
use App\Models\StudentSemesterGpaHistory;
use App\Models\StudentSubjectGradeHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GpaArchiveService
{
    public function archiveAssignment(SemesterAssignment $assignment, ?int $finalizedBy = null): array
    {
        $assignment->loadMissing([
            'classRoom.subject',
            'classRoom.groups.major',
        ]);

        $class = $assignment->classRoom;
        if (!$class) {
            throw new RuntimeException('Cannot archive GPA because the class no longer exists.');
        }

        $scores = DB::table('semester_assignment_scores')
            ->where('assignment_id', $assignment->id)
            ->get()
            ->keyBy('student_id');

        if ($scores->isEmpty()) {
            throw new RuntimeException('Cannot archive GPA because no student scores exist for this semester assignment.');
        }

        $students = Student::with(['user', 'group.major'])
            ->whereIn('id', $scores->keys())
            ->get();

        $archivedStudents = 0;
        $archivedSubjects = 0;

        DB::transaction(function () use (
            $assignment,
            $class,
            $scores,
            $students,
            $finalizedBy,
            &$archivedStudents,
            &$archivedSubjects
        ) {
            foreach ($students as $student) {
                $score = $scores->get($student->id);
                $group = $student->group ?: $class->groups->first();
                $major = $student->major ?: $group?->major;

                $history = StudentSemesterGpaHistory::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year' => $assignment->academic_year,
                        'semester' => (int) $assignment->semester,
                    ],
                    [
                        'class_group_id' => $group?->id,
                        'major_id' => $major?->id,
                        'student_name' => $student->user->name ?? 'Unknown',
                        'student_code' => $student->student_code,
                        'class_group_name' => $group?->name,
                        'major_name' => $major?->name,
                        'year_level' => $group?->year_level,
                        'result_status' => 'finalized',
                        'finalized_at' => now(),
                        'finalized_by' => $finalizedBy,
                    ]
                );

                $grade = $this->gradeForScore((float) ($score->score ?? 0));
                $credit = $this->creditForAssignment($assignment);
                $qualityPoints = round($grade['point'] * $credit, 2);

                StudentSubjectGradeHistory::updateOrCreate(
                    [
                        'semester_gpa_history_id' => $history->id,
                        'assignment_id' => $assignment->id,
                    ],
                    [
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'subject_id' => $class->subject?->id,
                        'class_name' => $class->name,
                        'subject_name' => $class->subject?->name ?? $class->name,
                        'subject_code' => $class->subject?->code,
                        'credit' => $credit,
                        'attendance_score' => $score->attendance_score ?? 0,
                        'midterm_score' => $score->midterm_score ?? 0,
                        'assignment_score' => $score->assignment_score ?? 0,
                        'final_score' => $score->final_score ?? 0,
                        'total_score' => $score->score ?? 0,
                        'letter_grade' => $grade['letter'],
                        'grade_point' => $grade['point'],
                        'quality_points' => $qualityPoints,
                        'notes' => $score->notes ?? null,
                        'finalized_at' => now(),
                    ]
                );

                $this->recalculateSemesterHistory($history);

                $archivedStudents++;
                $archivedSubjects++;
            }
        });

        return [
            'students' => $archivedStudents,
            'subject_grades' => $archivedSubjects,
        ];
    }

    public function gradeForScore(float $score): array
    {
        return match (true) {
            $score >= 90 => ['letter' => 'A', 'point' => 4.00],
            $score >= 80 => ['letter' => 'B', 'point' => 3.00],
            $score >= 70 => ['letter' => 'C', 'point' => 2.00],
            $score >= 60 => ['letter' => 'D', 'point' => 1.00],
            default => ['letter' => 'F', 'point' => 0.00],
        };
    }

    private function recalculateSemesterHistory(StudentSemesterGpaHistory $history): void
    {
        $subjectGrades = $history->subjectGrades()->get();
        $totalCredits = (float) $subjectGrades->sum('credit');
        $totalGradePoints = (float) $subjectGrades->sum('quality_points');
        $semesterGpa = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;

        $otherHistories = StudentSemesterGpaHistory::where('student_id', $history->student_id)
            ->where('id', '!=', $history->id)
            ->get();

        $cumulativeCredits = (float) $otherHistories->sum('total_credits') + $totalCredits;
        $cumulativeGradePoints = (float) $otherHistories->sum('total_grade_points') + $totalGradePoints;
        $cumulativeGpa = $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : 0;

        $history->update([
            'total_credits' => $totalCredits,
            'total_grade_points' => $totalGradePoints,
            'semester_gpa' => $semesterGpa,
            'cumulative_credits' => $cumulativeCredits,
            'cumulative_grade_points' => $cumulativeGradePoints,
            'cumulative_gpa' => $cumulativeGpa,
            'result_status' => 'finalized',
        ]);
    }

    private function creditForAssignment(SemesterAssignment $assignment): float
    {
        $subject = $assignment->classRoom?->subject;
        return (float) ($subject->credit ?? $subject->credits ?? 1);
    }
}
