<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Student;
use App\Models\StudentPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Services\AttendanceService;

class DashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    public function index(Request $request)
    {
        // 1. Stats
        $studentCount = Student::count();

        $attendanceSummary = $this->getOverallAttendanceSummary();

        $pendingSessions = AttendanceSession::where('end_time', '>', now())->count();
        $selectedMajorId = $request->integer('major_id') ?: null;
        $majorOptions = Major::orderBy('name')->get(['id', 'name', 'code']);
        $monitorSubjects = $this->getSubjectProgressByMajor($selectedMajorId);
        $majorComparison = $this->getMajorComparisonStats();

        // 2. Current Classes (Showing sessions from the last 24h + upcoming)
        $currentClasses = AttendanceSession::with(['classRoom.subject', 'classRoom.teacher'])
            ->where('end_time', '>', now()->subDay())
            ->orderBy('start_time', 'desc')
            ->take(10)
            ->get()
            ->map(function (\App\Models\AttendanceSession $session) {
                $now = now();
                $isLive = $now->between($session->start_time, $session->end_time);
                $isDone = $now > $session->end_time;

                $studentIds = $this->getSessionStudentIds($session);
                $totalInClass = $studentIds->count();
                $attendedInSession = $this->countAttendedForSessions(collect([$session->id]), $studentIds);
                $progress = $totalInClass > 0 ? ($attendedInSession / $totalInClass) * 100 : 0;

                return [
                    'id' => $session->id,
                    'name' => $session->classRoom->subject->name,
                    'room' => "ROOM " . (100 + ($session->id % 400)),
                    'time' => Carbon::parse($session->start_time)->format('H:i') . " – " . Carbon::parse($session->end_time)->format('H:i'),
                    'is_live' => $isLive,
                    'is_done' => $isDone,
                    'progress' => round($progress),
                ];
            });

        // 3. Selection of Active Session (Manual or Auto)
        $sessionId = $request->query('session_id');
        $activeSessionQuery = AttendanceSession::with(['classRoom.subject', 'classRoom.groups', 'attendanceRecords.student.user']);

        if ($sessionId) {
            $activeSession = $activeSessionQuery->find($sessionId);
        } else {
            $activeSession = $activeSessionQuery->where('end_time', '>=', now())->latest()->first();
        }

        // 4. Student Data for the Table
        $activeStudents = collect();
        $sessionScanCount = 0;
        $activePermissions = collect();

        if ($activeSession) {
            $sessionAttendance = $activeSession->attendanceRecords->keyBy('student_id');
            $sessionScanCount = $sessionAttendance->where('method', 'qr')->count();
            $sessionDate = Carbon::parse($activeSession->start_time)->toDateString();
            $allStudents = $activeSession->classRoom?->all_students ?? collect();
            if (method_exists($allStudents, 'load')) {
                $allStudents->load(['user', 'group.major', 'major']);
            }

            // Get active permissions for this session date for students in this class.
            $activePermissions = StudentPermission::with('student.user')
                ->where('start_date', '<=', $sessionDate)
                ->where('end_date', '>=', $sessionDate)
                ->whereIn('student_id', $allStudents->pluck('id'))
                ->get()
                ->keyBy('student_id');

            $activeStudents = $allStudents->map(function ($student) use ($sessionAttendance, $activePermissions) {
                $att = $sessionAttendance->get($student->id);
                $perm = $activePermissions->get($student->id);

                return [
                    'id' => $student->id,
                    'code' => $student->student_code,
                    'name' => $student->user->name,
                    'major' => $student->major->name ?? ($student->group->major->name ?? 'N/A'),
                    'year' => $student->group->year_level ?? '?',
                    'initials' => collect(explode(' ', $student->user->name))->map(fn($n) => substr($n, 0, 1))->join(''),
                    'status' => $att->status ?? ($perm ? 'excused' : 'absent'),
                    'permission' => $perm ? $perm->reason : null,
                    'time' => $att && $att->scan_time ? Carbon::parse($att->scan_time)->format('H:i') : '—',
                    'method' => $att->method ?? '—',
                    'avatar_color' => '#' . substr(md5($student->user->id), 0, 6),
                ];
            });
        }

        // 5. DB Activity Log
        $recentActivity = Attendance::with(['student.user', 'session.classRoom.subject'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($att) {
                return [
                    'time' => $att->created_at->format('H:i'),
                    'action' => $att->status === 'present' ? "AUTH scan verified {$att->student->student_code}" : "INSERT attendance #{$att->student->id}",
                    'type' => $att->status === 'present' ? 'auth' : 'ins',
                ];
            });

        return view('admin.student_overview', [
            'stats' => [
                'students' => number_format($studentCount),
                'attendance_rate' => number_format($attendanceSummary['attendance_rate'], 1) . '%',
                'pending_sessions' => $pendingSessions,
                'absence_rate' => number_format($attendanceSummary['absence_rate'], 1) . '%',
            ],
            'classes' => $currentClasses,
            'activeSession' => $activeSession,
            'activeStudents' => $activeStudents,
            'recentActivity' => $recentActivity,
            'sessionScanCount' => $sessionScanCount,
            'presentCount' => $activeStudents->whereIn('status', ['present', 'late', 'excused'])->count(),
            'totalCount' => $activeStudents->count(),
            'topAbsentStudents' => $this->getTopAbsentStudents(),
            'topAbsentClasses' => $this->getTopAbsentClasses(),
            'activePermissions' => $activePermissions,
            'yearStats' => $this->getYearLevelStats(),
            'majorOptions' => $majorOptions,
            'selectedMajorId' => $selectedMajorId,
            'monitorSubjects' => $monitorSubjects,
            'majorComparison' => $majorComparison,
        ]);
    }

    private function getOverallAttendanceSummary(): array
    {
        $sessions = AttendanceSession::with('classRoom.groups')
            ->where('status', 'completed')
            ->get();

        $totalPossible = 0;
        $attended = 0;

        foreach ($sessions as $session) {
            $studentIds = $this->getSessionStudentIds($session);

            if ($studentIds->isEmpty()) {
                continue;
            }

            $totalPossible += $studentIds->count();
            $attended += $this->countAttendedForSessions(collect([$session->id]), $studentIds);
        }

        $attendanceRate = $totalPossible > 0 ? round(($attended / $totalPossible) * 100, 1) : 0;

        return [
            'attendance_rate' => $attendanceRate,
            'absence_rate' => max(0, round(100 - $attendanceRate, 1)),
        ];
    }

    private function getSessionStudentIds(AttendanceSession $session, ?int $year = null)
    {
        $students = $session->classRoom?->all_students ?? collect();
        if (method_exists($students, 'load')) {
            $students->load('group');
        }

        return collect($students)
            ->when($year !== null, fn($collection) => $collection->filter(fn($student) => (int) ($student->group?->year_level ?? 0) === $year))
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();
    }

    private function getMajorComparisonStats()
    {
        return Major::with(['groups.students'])
            ->orderBy('name')
            ->get()
            ->map(function (Major $major) {
                $groupIds = $major->groups->pluck('id');
                $sessions = AttendanceSession::with(['classRoom.groups', 'classRoom.groups.students'])
                    ->whereHas('classRoom.groups', function ($q) use ($groupIds) {
                    $q->whereIn('class_groups.id', $groupIds);
                })
                    ->where('status', 'completed')
                    ->get();

                $studentCount = $major->groups->flatMap->students->pluck('id')->unique()->count();
                $totalPossible = 0;
                $attended = 0;

                foreach ($sessions as $session) {
                    $sessionGroupIds = $session->classRoom?->groups
                        ->where('major_id', $major->id)
                        ->pluck('id') ?? collect();
                    $studentIds = Student::whereIn('group_id', $sessionGroupIds)->pluck('id');

                    $totalPossible += $studentIds->count();
                    $attended += $this->countAttendedForSessions(collect([$session->id]), $studentIds);
                }

                $attendanceRate = $totalPossible > 0 ? round(($attended / $totalPossible) * 100) : 0;

                return [
                    'id' => $major->id,
                    'name' => $major->name,
                    'code' => $major->code,
                    'students' => $studentCount,
                    'sessions' => $sessions->count(),
                    'attendance_rate' => $attendanceRate,
                    'absence_rate' => max(0, 100 - $attendanceRate),
                ];
            })
            ->values();
    }

    private function countAttendedForSessions($sessionIds, $studentIds): int
    {
        $sessionIds = collect($sessionIds)->filter()->values();
        $studentIds = collect($studentIds)->filter()->unique()->values();

        if ($sessionIds->isEmpty() || $studentIds->isEmpty()) {
            return 0;
        }

        $sessions = AttendanceSession::whereIn('id', $sessionIds)->get(['id', 'start_time']);
        $attendanceRows = Attendance::whereIn('session_id', $sessionIds)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
            ->get(['session_id', 'student_id'])
            ->unique(fn($row) => $row->session_id . ':' . $row->student_id);

        $attended = $attendanceRows->count();
        $attendedBySession = $attendanceRows->groupBy('session_id');

        foreach ($sessions as $session) {
            $attendedStudentIds = $attendedBySession->get($session->id, collect())->pluck('student_id')->unique();
            $sessionDate = Carbon::parse($session->start_time)->toDateString();

            $attended += StudentPermission::where('start_date', '<=', $sessionDate)
                ->where('end_date', '>=', $sessionDate)
                ->whereIn('student_id', $studentIds->diff($attendedStudentIds))
                ->pluck('student_id')
                ->unique()
                ->count();
        }

        return $attended;
    }

    private function getSubjectProgressByMajor(?int $majorId = null)
    {
        return ClassRoom::with(['subject', 'teacher.user', 'groups.major'])
            ->when($majorId, function ($query) use ($majorId) {
                $query->where(function ($q) use ($majorId) {
                    $q->whereHas('groups', fn($groupQuery) => $groupQuery->where('major_id', $majorId))
                        ->orWhereHas('students.group', fn($groupQuery) => $groupQuery->where('major_id', $majorId));
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function (ClassRoom $class) use ($majorId) {
                $sessions = $class->sessions()->where('status', 'completed')->get();
                $groupIds = $class->groups->pluck('id');

                if ($groupIds->isEmpty() && $class->group_id) {
                    $groupIds = collect([$class->group_id]);
                }

                if ($majorId) {
                    $groupIds = $class->groups->where('major_id', $majorId)->pluck('id');
                }

                $studentCount = $groupIds->isNotEmpty()
                    ? Student::whereIn('group_id', $groupIds)->count()
                    : 0;

                $totalPossible = 0;
                $attended = 0;

                foreach ($sessions as $session) {
                    $studentIds = $groupIds->isNotEmpty()
                        ? Student::whereIn('group_id', $groupIds)->pluck('id')
                        : collect();

                    $totalPossible += $studentIds->count();
                    $attended += $this->countAttendedForSessions(collect([$session->id]), $studentIds);
                }

                $progress = $totalPossible > 0 ? round(($attended / $totalPossible) * 100) : 0;
                $majorNames = $class->groups
                    ->map(fn($group) => $group->major?->name)
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'id' => $class->id,
                    'name' => $class->subject->name ?? $class->name,
                    'teacher' => $class->teacher->user->name ?? 'Unassigned',
                    'majors' => $majorNames->isNotEmpty() ? $majorNames->join(', ') : 'Unassigned major',
                    'sessions' => $sessions->count(),
                    'students' => $studentCount,
                    'attended' => $attended,
                    'total_possible' => $totalPossible,
                    'progress' => $progress,
                    'missing' => max(0, 100 - $progress),
                ];
            })
            ->sortByDesc('progress')
            ->values();
    }

    private function getTopAbsentStudents()
    {
        // Students with most absences in completed sessions
        return Student::with(['user'])
            ->get()
            ->map(function($student) {
                // Count relevant completed sessions
                $sessionIds = AttendanceSession::whereHas('classRoom.groups', function($q) use ($student) {
                        $q->where('class_groups.id', $student->group_id);
                    })
                    ->where('status', 'completed')
                    ->pluck('id');
                
                $totalSessions = $sessionIds->count();
                if ($totalSessions === 0) return null;

                $attendedCount = Attendance::where('student_id', $student->id)
                    ->whereIn('session_id', $sessionIds)
                    ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
                    ->pluck('session_id')
                    ->unique()
                    ->count();
                $attendedSessionIds = Attendance::where('student_id', $student->id)
                    ->whereIn('session_id', $sessionIds)
                    ->whereIn('status', ['present', 'late', 'excused', 'PRESENT', 'LATE', 'EXCUSED'])
                    ->pluck('session_id')
                    ->unique();
                $excusedCount = AttendanceSession::whereIn('id', $sessionIds->diff($attendedSessionIds))
                    ->get()
                    ->filter(function ($session) use ($student) {
                        $sessionDate = Carbon::parse($session->start_time)->toDateString();

                        return \App\Models\StudentPermission::where('student_id', $student->id)
                            ->where('start_date', '<=', $sessionDate)
                            ->where('end_date', '>=', $sessionDate)
                            ->exists();
                    })
                    ->count();
                
                $absentCount = max(0, $totalSessions - $attendedCount - $excusedCount);
                $rate = ($absentCount / $totalSessions) * 100;
                
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? 'Unknown',
                    'absent_count' => $absentCount,
                    'absence_rate' => round($rate),
                    'initials' => collect(explode(' ', $student->user->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->join(''),
                ];
            })
            ->filter(fn($s) => $s !== null && $s['absent_count'] > 0)
            ->sortByDesc('absent_count')
            ->take(5);
    }

    private function getTopAbsentClasses()
    {
        return ClassRoom::with(['subject', 'teacher.user', 'groups'])
            ->get()
            ->map(function($class) {
                $sessions = AttendanceSession::where('class_id', $class->id)->where('status', 'completed')->pluck('id');
                if ($sessions->isEmpty()) return null;

                $totalStudents = $class->all_students->pluck('id')->unique()->count();
                if ($totalStudents === 0) return null;

                $totalPossibleAttendances = $sessions->count() * $totalStudents;
                $actualAttendances = $this->countAttendedForSessions($sessions, $class->all_students->pluck('id'));
                
                $absences = max(0, $totalPossibleAttendances - $actualAttendances);
                $absenceRate = $totalPossibleAttendances > 0 ? ($absences / $totalPossibleAttendances) * 100 : 0;

                return [
                    'id' => $class->id,
                    'name' => $class->subject->name ?? 'Unknown',
                    'teacher' => $class->teacher->user->name ?? 'Unknown',
                    'absence_rate' => round($absenceRate),
                ];
            })
            ->filter(fn($c) => $c !== null && $c['absence_rate'] > 0)
            ->sortByDesc('absence_rate')
            ->take(5);
    }

    private function getYearLevelStats()
    {
        $stats = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($stats as $year => $val) {
            $sessions = AttendanceSession::with(['classRoom.groups', 'classRoom.students', 'classRoom.groups.students'])
                ->whereHas('classRoom.groups', function ($q) use ($year) {
                $q->where('year_level', $year);
            })
                ->where('status', 'completed')
                ->get();

            if ($sessions->isEmpty()) {
                $stats[$year] = 0;
                continue;
            }

            $totalPossible = 0;
            $attended = 0;

            foreach ($sessions as $session) {
                $studentIds = $this->getSessionStudentIds($session, $year);
                $totalPossible += $studentIds->count();
                $attended += $this->countAttendedForSessions(collect([$session->id]), $studentIds);
            }

            $stats[$year] = $totalPossible > 0 ? round(($attended / $totalPossible) * 100) : 0;
        }

        return $stats;
    }


    public function studentScan($sessionId)
    {
        $session = AttendanceSession::with('classRoom.subject')->findOrFail($sessionId);
        return view('student_scan', compact('session'));
    }

    public function teacherReports(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') {
            abort(403, 'Unauthorized access to faculty reports.');
        }

        $teacher = $user->teacher;
        if (!$teacher) {
            abort(404, 'Teacher profile not found.');
        }

        // Auto-sync session statuses
        $now = now();
        AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->where('status', 'scheduled')->where('start_time', '<=', $now)->where('end_time', '>=', $now)
            ->update(['status' => 'active']);
        $toComplete = AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->where('status', 'active')->where('end_time', '<', $now->copy()->subMinutes(45))
            ->get();

        foreach ($toComplete as $session) {
            $session->update(['status' => 'completed']);
            app(\App\Services\TelegramService::class)->checkAbsenceThresholds($session->class_id);
        }

        // Assigned classes with session counts and student counts
        $classes = ClassRoom::where('teacher_id', $teacher->id)
            ->with([
                'subject',
                'groups',
                'sessions' => function ($q) {
                    $q->withCount('attendanceRecords')->orderBy('start_time', 'desc');
                }
            ])
            ->get();

        $classIds = $classes->pluck('id');

        // Aggregate stats
        $totalStudents = $classes
            ->flatMap(fn($class) => $class->all_students->pluck('id'))
            ->unique()
            ->count();
        $totalSessions = AttendanceSession::whereIn('class_id', $classIds)->count();
        $totalPossible = $totalSessions * max(1, $totalStudents);
        $totalAttended = \App\Models\Attendance::whereHas('session', fn($q) => $q->whereIn('class_id', $classIds))
            ->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
        $overallRate = $totalPossible > 0 ? round(($totalAttended / $totalPossible) * 100) : 0;

        // All sessions for the monitor sidebar (ordered: active first, then upcoming, then done)
        $allSessions = AttendanceSession::with(['classRoom.subject'])
            ->whereIn('class_id', $classIds)
            ->orderByRaw("
                CASE status 
                    WHEN 'active' THEN 1 
                    WHEN 'scheduled' THEN 2 
                    WHEN 'completed' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderBy('start_time', 'desc')
            ->take(50)
            ->get();

        // Selected class (for history view)
        $selectedClassId = $request->query('class_id', $classes->first()?->id);
        $selectedClass = $classes->find($selectedClassId);

        // Selected session (for both monitor and drill-down)
        $selectedSessionId = $request->query('session_id');
        $selectedSession = null;

        if ($selectedSessionId) {
            $selectedSession = AttendanceSession::with([
                'classRoom.subject',
                'classRoom.groups',
                'attendanceRecords.student.user'
            ])->find($selectedSessionId);
        } elseif ($allSessions->where('status', 'active')->isNotEmpty()) {
            // Auto-select the active session for the monitor view
            $selectedSession = AttendanceSession::with([
                'classRoom.subject',
                'classRoom.groups',
                'attendanceRecords.student.user'
            ])->find($allSessions->where('status', 'active')->first()->id);
        }

        return view('teacher.reports', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedSession' => $selectedSession,
            'allSessions' => $allSessions,
            'totalStudents' => $totalStudents,
            'totalSessions' => $totalSessions,
            'overallRate' => $overallRate,
        ]);
    }
}
