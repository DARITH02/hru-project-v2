<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\ClassRoom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class SystemSummaryExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, ShouldAutoSize
{
    protected $academicYear;
    protected $semester;
    protected $reportType; // 'full' or 'half'

    public function __construct($academicYear, $semester, $reportType = 'full')
    {
        $this->academicYear = $academicYear;
        $this->semester = $semester;
        $this->reportType = $reportType;
    }
    public function startCell(): string
    {
        return 'A4';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $type = $this->reportType === 'half' ? 'MID-TERM' : 'FULL SEMESTER';
                $sheet = $event->sheet->getDelegate();
                
                $sheet->setCellValue('A1', 'Report Type: ' . $type);
                $sheet->setCellValue('C1', 'Academic Year: ' . $this->academicYear);
                $sheet->setCellValue('E1', 'Semester: ' . $this->semester);

                // Styling
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $sheet->getStyle('A4:H4')->getFont()->setBold(true);
                $sheet->getStyle('A4:H4')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('EEEEEE');
            },
        ];
    }

    public function collection()
    {
        // Get all classes for this semester/year
        $classes = ClassRoom::where('academic_year', $this->academicYear)
            ->where('semester', $this->semester)
            ->with(['subject', 'groups'])
            ->get();

        $data = collect();

        foreach ($classes as $class) {
            // Get sessions for this class (including active and completed)
            $sessionQuery = AttendanceSession::where('class_id', $class->id)
                ->whereIn('status', ['completed', 'active'])
                ->orderBy('start_time', 'asc');

            // If half semester, only take sessions in the first half of the semester duration
            if ($this->reportType === 'half') {
                $assignment = \App\Models\SemesterAssignment::where('class_id', $class->id)
                    ->where('academic_year', $this->academicYear)
                    ->where('semester', $this->semester)
                    ->first();

                if ($assignment && $assignment->start_date && $assignment->end_date) {
                    $start = Carbon::parse($assignment->start_date);
                    $end = Carbon::parse($assignment->end_date);
                    $midPoint = $start->addDays($start->diffInDays($end) / 2);
                    $sessionQuery->where('start_time', '<=', $midPoint);
                } else {
                    // Fallback: Use 50% of total sessions if date range isn't clear
                    $allSessions = $sessionQuery->get();
                    $limit = ceil($allSessions->count() / 2);
                    $sessionQuery = AttendanceSession::whereIn('id', $allSessions->take($limit)->pluck('id'));
                }
            }

            $sessions = $sessionQuery->get();
            $sessionIds = $sessions->pluck('id');

            if ($sessionIds->isEmpty()) continue;

            // Get students in the groups with user relationship
            $groupIds = $class->groups->pluck('id');
            $students = Student::whereIn('group_id', $groupIds)->with('user')->get();

            foreach ($students as $student) {
                $presentCount = Attendance::where('student_id', $student->id)
                    ->whereIn('session_id', $sessionIds)
                    ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
                    ->count();

                $totalSessions = $sessions->count();
                $absentCount = max(0, $totalSessions - $presentCount);
                $rate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 2) : 0;

                $data->push([
                    'student_code' => $student->student_code,
                    'student_name' => $student->user->name ?? 'N/A',
                    'subject'      => $class->subject->name ?? 'N/A',
                    'class_name'   => $class->groups->first()->name ?? 'N/A',
                    'total'        => $totalSessions,
                    'present'      => $presentCount,
                    'absent'       => $absentCount,
                    'rate'         => $rate . '%'
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Subject',
            'Group/Class',
            'Total Sessions',
            'Present',
            'Absent',
            'Attendance Rate'
        ];
    }

    public function map($row): array
    {
        return [
            $row['student_code'],
            $row['student_name'],
            $row['subject'],
            $row['class_name'],
            $row['total'],
            $row['present'],
            $row['absent'],
            $row['rate']
        ];
    }
}
