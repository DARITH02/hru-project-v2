<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\StudentPermission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $session = \App\Models\AttendanceSession::with('classRoom.groups')->find($this->sessionId);
        $groupIds = $session->classRoom ? $session->classRoom->groups->pluck('id') : collect();

        $attendances = Attendance::where('session_id', $this->sessionId)->get()->keyBy('student_id');

        if ($groupIds->isNotEmpty()) {
            $students = \App\Models\Student::with(['user', 'major', 'group'])
                ->whereIn('group_id', $groupIds)
                ->get();

            // Get active permissions for this session's date
            $date = Carbon::parse($session->start_time)->toDateString();
            $permissions = StudentPermission::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');

            return $students->map(function ($student) use ($attendances, $session, $permissions) {
                $att = $attendances->get($student->id);
                $perm = $permissions->get($student->id);
                
                $status = $att ? $att->status : ($perm ? 'excused' : 'absent');
                
                // For Major, handle the legacy 'major' column vs relationship conflict
                $majorObj = ($student->major instanceof \App\Models\Major) ? $student->major : ($student->group->major ?? null);

                return (object) [
                    'id' => $student->student_code ?? $student->id,
                    'name' => $student->user->name ?? $student->name,
                    'status' => $status,
                    'time' => $att ? $att->scan_time : 'N/A',
                    'major'       => $majorObj->name ?? 'N/A',
                    'group'       => $student->group->name ?? 'N/A',
                    'year_level'  => $student->group->year_level ?? 'N/A',
                    'room'        => $session->classRoom->room_number ?? 'N/A',
                    'subject'     => $session->classRoom->subject->name ?? 'N/A'
                ];
            });
        }

        return Attendance::with(['student.user', 'student.major', 'student.group', 'session.classRoom.subject'])
            ->where('session_id', $this->sessionId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Status',
            'Scan Time',
            'Major',
            'Class Group',
            'Year Level',
            'Room',
            'Subject'
        ];
    }

    /**
     * @var mixed $item
     */
    public function map($item): array
    {
        if ($item instanceof \App\Models\Attendance) {
            return [
                $item->student->student_code ?? $item->student->id ?? 'N/A',
                $item->student->user->name ?? $item->student->name ?? 'Unknown',
                ucfirst($item->status),
                $item->scan_time ?? 'N/A',
                $item->student->major->name ?? 'N/A',
                $item->student->group->name ?? 'N/A',
                $item->student->group->year_level ?? 'N/A',
                $item->session->classRoom->room_number ?? 'N/A',
                $item->session->classRoom->subject->name ?? 'N/A'
            ];
        }

        return [
            $item->id,
            $item->name,
            ucfirst($item->status),
            $item->time,
            $item->major,
            $item->group,
            $item->year_level ?? 'N/A',
            $item->room,
            $item->subject
        ];
    }
}
