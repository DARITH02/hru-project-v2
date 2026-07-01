<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Public Student Check-in
     */
    public function verify(Request $request)
    {
        $request->validate([
            'session_id'   => 'required',
            'student_id'   => 'required_without:student_code|nullable|integer|exists:students,id',
            'student_code' => 'required_without:student_id|nullable|string',
            'qr_token'     => 'required',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'accuracy'     => 'nullable|numeric',
        ]);

        try {
            if ($request->filled('student_id')) {
                $attendance = $this->attendanceService->processCheckinByStudentId(
                    $request->session_id,
                    $request->student_id,
                    $request->qr_token,
                    $request->latitude,
                    $request->longitude,
                    $request->accuracy
                );
            } else {
                $attendance = $this->attendanceService->processCheckin(
                    $request->session_id,
                    $request->student_code,
                    $request->qr_token,
                    $request->latitude,
                    $request->longitude,
                    $request->accuracy
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'status' => strtoupper($attendance->status),
                'attendance' => [
                    'student_id' => $attendance->student_id,
                    'session_id' => $attendance->session_id,
                    'status' => $attendance->status,
                    'scan_time' => optional($attendance->scan_time)->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get Session details for student API scan
     */
    public function getScanInfo(Request $request, $sessionId)
    {
        try {
            $session = AttendanceSession::with(['classRoom.subject', 'classRoom.groups', 'attendanceRecords'])->findOrFail($sessionId);
            $students = $this->studentsForSession($session);
            $attendanceByStudent = $session->attendanceRecords->keyBy('student_id');

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $session->id,
                    'class_id' => $session->class_id,
                    'room' => $session->classRoom->room_number ?? (100 + ($session->id % 400)),
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'status' => $session->status,
                    'subject' => $session->classRoom->subject->name ?? 'Unknown',
                    'groups' => $session->classRoom?->groups?->map(fn ($group) => [
                        'id' => $group->id,
                        'name' => $group->name,
                        'year_level' => $group->year_level,
                    ])->values() ?? [],
                ],
                'qr_token' => $request->query('token'),
                'students' => $students->map(function ($student) use ($attendanceByStudent) {
                    $attendance = $attendanceByStudent->get($student->id);

                    return [
                        'id' => $student->id,
                        'student_code' => $student->student_code,
                        'name' => $student->user->name ?? 'Unknown Student',
                        'group_id' => $student->group_id,
                        'group_name' => $student->group->name ?? null,
                        'status' => $attendance?->status ?? 'absent',
                        'already_checked_in' => $attendance && in_array($attendance->status, ['present', 'late'], true),
                        'scan_time' => $attendance?->scan_time ? Carbon::parse($attendance->scan_time)->format('H:i') : null,
                    ];
                })->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or unavailable'
            ], 404);
        }
    }

    private function studentsForSession(AttendanceSession $session)
    {
        $classRoom = $session->classRoom;

        if (!$classRoom) {
            return collect();
        }

        $students = $classRoom->all_students;

        if (method_exists($students, 'load')) {
            $students->load(['user', 'group']);
        }

        return collect($students)
            ->filter(fn ($student) => $student->status !== 'blacklisted')
            ->sortBy(fn ($student) => $student->user->name ?? $student->student_code)
            ->values();
    }

    public function getPortalData(Request $request)
    {
        $user = $request->user();
        $student = \App\Models\Student::with(['group', 'user'])->where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }

        // 1. Active/Scheduled Session
        $session = \App\Models\AttendanceSession::whereHas('classRoom.groups', function ($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->whereIn('status', ['active', 'scheduled'])
            ->with(['classRoom.subject', 'classRoom.teacher.user'])
            ->latest('id')
            ->first();

        $activeSession = null;
        if ($session) {
            $activeSession = [
                'id' => $session->id,
                'subject' => $session->classRoom->subject->name ?? 'N/A',
                'teacher' => $session->classRoom->teacher->user->name ?? 'N/A',
                'room' => $session->location ?? 'TBD',
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'status' => $session->status,
            ];
        }

        // 2. Stats
        $allSessions = \App\Models\AttendanceSession::whereHas('classRoom.groups', function($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->get();

        $attendance = \App\Models\Attendance::where('student_id', $student->id)->get();
        
        $total = $allSessions->count();
        $present = $attendance->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
        $rate = $total > 0 ? round(($present / $total) * 100) : 0;

        return response()->json([
            'student' => [
                'name' => $student->user->name ?? $student->name,
                'code' => $student->student_code,
                'group' => $student->group->name ?? 'N/A',
                'major' => $student->major->name ?? 'N/A',
            ],
            'active_session' => $activeSession,
            'stats' => [
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'rate' => $rate,
                'remaining' => max(0, 30 - $total), // Example logic: 30 sessions per semester
            ]
        ]);
    }

    public function getActiveSession(Request $request)
    {
        $user = $request->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student) return response()->json(['message' => 'Student record not found'], 404);

        $session = \App\Models\AttendanceSession::whereHas('classRoom.groups', function ($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->whereIn('status', ['active', 'scheduled'])
            ->with(['classRoom.subject', 'classRoom.teacher.user'])
            ->latest('id')
            ->first();

        if (!$session) return response()->json(null);

        return response()->json([
            'id' => $session->id,
            'subject' => $session->classRoom->subject->name ?? 'N/A',
            'teacher' => $session->classRoom->teacher->user->name ?? 'N/A',
            'room' => $session->location ?? 'TBD',
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'status' => $session->status,
        ]);
    }

    public function getStudentHistoryByCode(Request $request)
    {
        $request->validate(['student_code' => 'required']);
        
        $student = \App\Models\Student::with(['group', 'user'])->where('student_code', $request->student_code)->first();
        if (!$student) return response()->json(['success' => false, 'message' => 'Student not found'], 404);

        $sessions = \App\Models\AttendanceSession::whereHas('classRoom.groups', function($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->with(['classRoom.subject', 'classRoom.teacher.user'])
            ->orderBy('id', 'desc')
            ->get();

        $attendance = \App\Models\Attendance::where('student_id', $student->id)->get()->keyBy('session_id');

        $history = $sessions->map(function ($session) use ($attendance) {
            $record = $attendance->get($session->id);
            
            $status = 'ABSENT';
            $isFuture = \Carbon\Carbon::parse($session->start_time)->isFuture();

            if ($record) {
                $status = strtoupper($record->status);
            } elseif ($session->status === 'scheduled' || $isFuture) {
                $status = 'SCHEDULED';
            }

            return [
                'subject' => $session->classRoom->subject->name ?? 'N/A',
                'teacher' => $session->classRoom->teacher->user->name ?? 'N/A',
                'date' => $session->start_time,
                'status' => $status,
                'session_status' => $session->status,
                'scan_time' => $record ? \Carbon\Carbon::parse($record->scan_time)->format('H:i') : null,
                'method' => $record ? strtoupper($record->method) : null,
            ];
        });

        $total = $sessions->count();
        $present = $attendance->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();

        return response()->json([
            'success' => true,
            'student_name' => $student->user->name ?? 'Student',
            'student_id' => $student->id,
            'student_code' => $student->student_code,
            'class_name' => $student->group->name ?? 'N/A',
            'stats' => [
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'rate' => $total > 0 ? round(($present / $total) * 100) : 0
            ],
            'history' => $history
        ]);
    }

    public function getStudentClasses(Request $request)
    {
        $user = $request->user();
        $student = \App\Models\Student::with('group')->where('user_id', $user->id)->first();
        if (!$student) return response()->json(['message' => 'Student record not found'], 404);

        $classes = \App\Models\ClassRoom::with(['subject', 'teacher.user'])
            ->whereHas('groups', function($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->get();

        return response()->json($classes->map(function ($class) use ($student) {
            $sessions = \App\Models\AttendanceSession::where('class_id', $class->id)->get();
            $sessionIds = $sessions->pluck('id');
            $attendance = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('session_id', $sessionIds)
                ->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])
                ->count();
            
            $total = $sessions->count();
            $rate = $total > 0 ? round(($attendance / $total) * 100) : 0;

            return [
                'id' => $class->id,
                'name' => $class->subject->name ?? 'N/A',
                'code' => $class->subject->code ?? 'N/A',
                'teacher' => $class->teacher->user->name ?? 'N/A',
                'sessions_count' => $total,
                'attended_count' => $attendance,
                'attendance_rate' => $rate,
            ];
        }));
    }

    public function getStudentClassHistory(Request $request, $classId)
    {
        $user = $request->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student) return response()->json(['message' => 'Student record not found'], 404);

        $class = \App\Models\ClassRoom::with(['subject', 'teacher.user'])->findOrFail($classId);
        
        if (!$class->groups->contains($student->group_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sessions = \App\Models\AttendanceSession::where('class_id', $classId)
            ->orderBy('id', 'desc')
            ->get();

        $attendance = \App\Models\Attendance::where('student_id', $student->id)
            ->whereIn('session_id', $sessions->pluck('id'))
            ->get()
            ->keyBy('session_id');

        $history = $sessions->map(function ($session) use ($attendance) {
            $record = $attendance->get($session->id);
            $status = 'ABSENT';
            $isFuture = \Carbon\Carbon::parse($session->start_time)->isFuture();

            if ($record) {
                $status = strtoupper($record->status);
            } elseif ($session->status === 'scheduled' || $isFuture) {
                $status = 'SCHEDULED';
            }

            return [
                'id' => $session->id,
                'date' => $session->start_time,
                'status' => $status,
                'scan_time' => $record ? \Carbon\Carbon::parse($record->scan_time)->format('H:i') : null,
                'method' => $record ? strtoupper($record->method) : null,
            ];
        });

        $assignments = \App\Models\SemesterAssignment::where('class_id', $classId)
            ->orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();
            
        $scoresData = collect();
        if ($assignments->isNotEmpty()) {
            $scores = \DB::table('semester_assignment_scores')
                ->where('student_id', $student->id)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->get();
                
            $scoresData = $assignments->map(function ($a) use ($scores) {
                $score = $scores->firstWhere('assignment_id', $a->id);
                return [
                    'assignment_id' => $a->id,
                    'semester' => $a->academic_year . ' S' . $a->semester,
                    'attendance_score' => $score->attendance_score ?? 0,
                    'midterm_score' => $score->midterm_score ?? 0,
                    'assignment_score' => $score->assignment_score ?? 0,
                    'final_score' => $score->final_score ?? 0,
                    'total_score' => $score->score ?? 0,
                ];
            });
        }

        return response()->json([
            'class' => [
                'id' => $class->id,
                'name' => $class->subject->name,
                'teacher' => $class->teacher->user->name,
            ],
            'history' => $history,
            'scores' => $scoresData
        ]);
    }

    public function getTranscript(Request $request)
    {
        $student = Student::with(['user', 'group.major'])->where('user_id', $request->user()->id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }

        $histories = \App\Models\StudentSemesterGpaHistory::with(['subjectGrades' => function ($query) {
                $query->orderBy('subject_name');
            }])
            ->where('student_id', $student->id)
            ->orderByDesc('academic_year')
            ->orderByDesc('semester')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'student_code' => $student->student_code,
                'group' => $student->group?->name,
                'major' => $student->major?->name ?? $student->group?->major?->name,
                'year_level' => $student->group?->year_level,
            ],
            'histories' => $histories,
            'summary' => [
                'semester_count' => $histories->count(),
                'total_credits' => round((float) $histories->sum('total_credits'), 2),
                'latest_gpa' => round((float) ($histories->first()?->semester_gpa ?? 0), 2),
                'cumulative_gpa' => round((float) ($histories->first()?->cumulative_gpa ?? 0), 2),
            ],
        ]);
    }
}
