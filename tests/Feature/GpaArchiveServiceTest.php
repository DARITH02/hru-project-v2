<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\SemesterAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\GpaArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GpaArchiveServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_gpa_history_survives_live_class_deletion(): void
    {
        $department = Department::create([
            'name' => 'Technology',
            'code' => 'TECH',
        ]);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'department_id' => $department->id,
            'specialization' => 'Software',
            'status' => 'active',
        ]);

        $subject = Subject::create([
            'department_id' => $department->id,
            'name' => 'Database Systems',
            'code' => 'DBS101',
        ]);

        $class = ClassRoom::create([
            'name' => 'Database Systems A',
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'academic_year' => '2025-2026',
            'semester' => 1,
            'status' => 'active',
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Student One',
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'STD-00001',
            'status' => 'active',
        ]);

        $assignment = SemesterAssignment::create([
            'class_id' => $class->id,
            'academic_year' => '2025-2026',
            'semester' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
            'status' => 'active',
            'grading_status' => 'finalized',
        ]);

        DB::table('semester_assignment_scores')->insert([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'attendance_score' => 18,
            'midterm_score' => 14,
            'assignment_score' => 13,
            'final_score' => 41,
            'score' => 86,
            'notes' => 'Good work',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(GpaArchiveService::class)->archiveAssignment($assignment);

        $this->assertSame(1, $result['students']);
        $this->assertDatabaseHas('student_semester_gpa_histories', [
            'student_id' => $student->id,
            'academic_year' => '2025-2026',
            'semester' => 1,
            'semester_gpa' => 3.00,
            'cumulative_gpa' => 3.00,
            'result_status' => 'finalized',
        ]);
        $this->assertDatabaseHas('student_subject_grade_histories', [
            'student_id' => $student->id,
            'subject_name' => 'Database Systems',
            'total_score' => 86,
            'letter_grade' => 'B',
            'grade_point' => 3.00,
        ]);

        $class->delete();

        $this->assertDatabaseHas('student_semester_gpa_histories', [
            'student_code' => 'STD-00001',
            'semester_gpa' => 3.00,
        ]);
        $this->assertDatabaseHas('student_subject_grade_histories', [
            'subject_name' => 'Database Systems',
            'letter_grade' => 'B',
        ]);
    }
}
