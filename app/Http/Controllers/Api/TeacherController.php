<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AttendanceSession;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\SemesterAssignment;
use Carbon\Carbon;
use App\Services\SemesterAttendanceScoreService;
use App\Services\TelegramService;

class TeacherController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    private function currentTeacher(Request $request): Teacher
    {
        return Teacher::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function teacherSession(Request $request, $sessionId, array $with = []): AttendanceSession
    {
        $teacher = $this->currentTeacher($request);

        return AttendanceSession::with($with)
            ->where('id', $sessionId)
            ->whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->firstOrFail();
    }

    private function teacherAssignment(Request $request, $assignmentId, array $with = []): SemesterAssignment
    {
        $teacher = $this->currentTeacher($request);

        return SemesterAssignment::with($with)
            ->where('id', $assignmentId)
            ->whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->firstOrFail();
    }

    private function syncSessionStatuses($teacherId)
    {
        $now = now();
        
        // 1. Scheduled -> Active (Auto-start if time matches)
        AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacherId))
            ->where('status', 'scheduled')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->update(['status' => 'active']);

        // 2. Scheduled -> Completed (If time passed and it never started)
        AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacherId))
            ->where('status', 'scheduled')
            ->where(function($q) use ($now) {
                // If there's a close time, wait for it. Otherwise, end_time + 20.
                $q->whereNotNull('checkin_close_time')->where('checkin_close_time', '<', $now)
                  ->orWhereNull('checkin_close_time')->where('end_time', '<', $now->subMinutes(20));
            })
            ->update(['status' => 'completed']);

        // 3. Active -> Completed (Only if time passed by a reasonable buffer, e.g. 45 minutes)
        // This allows teachers to finish their class and get the report promptly.
        AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacherId))
            ->where('status', 'active')
            ->where('end_time', '<', $now->subMinutes(45))
            ->update(['status' => 'completed']);

        // 4. Auto-send Telegram for newly completed sessions
        $pendingReports = AttendanceSession::whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacherId))
            ->where('status', 'completed')
            ->where('telegram_sent', false)
            ->get();

        foreach ($pendingReports as $session) {
            $this->telegram->sendAttendanceReport($session->id);
        }
    }

    public function getSessions(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher record not found'], 404);

        $this->syncSessionStatuses($teacher->id);

        $query = AttendanceSession::with(['classRoom.subject'])
            ->whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id));

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        } else {
            // Default: Fetch active, upcoming (48h), and completed
            $query->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'scheduled')
                        ->where('start_time', '<=', now()->addDays(2));
                  })
                  ->orWhere('status', 'completed');
            });
        }

        $sessions = $query->orderByRaw("
                CASE status 
                    WHEN 'active' THEN 1 
                    WHEN 'scheduled' THEN 2 
                    WHEN 'completed' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($sessions->map(fn($s) => $this->formatSession($s)));
    }

    public function getSessionsByClass(Request $request, $classId)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher record not found'], 404);

        $this->syncSessionStatuses($teacher->id);

        $sessions = AttendanceSession::with(['classRoom.subject'])
            ->where('class_id', $classId)
            ->whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($sessions->map(fn($s) => $this->formatSession($s)));
    }

    private function formatSession($session)
    {
        return [
            'id' => $session->id,
            'class_id' => $session->class_id,
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'status' => $session->status,
            'room' => $session->classRoom->room_number ?? 'TBD',
            'subject' => [
                'name' => $session->classRoom->subject->name,
                'code' => $session->classRoom->subject->code,
            ],
            'presence_count' => Attendance::where('session_id', $session->id)->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count(),
            'total_students_count' => $session->classRoom ? \App\Models\Student::whereIn('group_id', $session->classRoom->groups->pluck('id'))->count() : 0,
        ];
    }

    public function monitor(Request $request, $sessionId)
    {
        $session = $this->teacherSession($request, $sessionId, ['classRoom.subject', 'classRoom.groups']);
        $attendances = Attendance::where('session_id', $sessionId)->get()->keyBy('student_id');
        $allStudents = collect();
        if ($session->classRoom) {
            $groupIds = $session->classRoom->groups->pluck('id');
            $allStudents = \App\Models\Student::whereIn('group_id', $groupIds)->get();
        }

        $sessionsCount = AttendanceSession::where('class_id', $session->class_id)->count();

        $sessionDate = Carbon::parse($session->start_time)->toDateString();
        $permissions = \App\Models\StudentPermission::where('start_date', '<=', $sessionDate)
            ->where('end_date', '>=', $sessionDate)
            ->whereIn('student_id', $allStudents->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $rows = $allStudents->map(function ($student) use ($attendances, $permissions) {
            $att = $attendances->get($student->id);
            $perm = $permissions->get($student->id);
            $userName = $student->user->name ?? 'Unknown Student';
            $names = explode(' ', $userName);
            $initials = (isset($names[0]) ? substr($names[0], 0, 1) : '') . (isset($names[1]) ? substr($names[1], 0, 1) : '');
            
            $status = 'ABSENT';
            if ($att) {
                $status = strtoupper($att->status);
            } elseif ($perm) {
                $status = 'EXCUSED';
            }
            
            return [
                'id' => $student->id,
                'attendance_id' => $att?->id,
                'initials' => strtoupper($initials),
                'name' => $userName,
                'student_code' => $student->student_code,
                'status' => $status,
                'permission_reason' => $perm ? $perm->reason : null,
                'permission_type' => $perm ? $perm->type : null,
                'check_in_time' => $att && $att->scan_time ? Carbon::parse($att->scan_time)->format('H:i') : '—',
                'method' => $att ? strtoupper($att->method) : '—',
                'avatar_color' => '#' . substr(md5($student->user_id), 0, 6)
            ];
        });

        $excusedCount = $permissions->count();
        $presentCount = $attendances->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();

        return response()->json([
            'session_name' => $session->classRoom->subject->name,
            'sessions_count' => $sessionsCount,
            'present_count' => $presentCount,
            'excused_count' => $excusedCount,
            'total_count' => $allStudents->count(),
            'data' => $rows
        ]);
    }

    public function exportAttendance(Request $request, $sessionId)
    {
        $session = $this->teacherSession($request, $sessionId, ['classRoom.subject']);
        $subjectName = $session->classRoom?->subject?->name ?? 'attendance';
        $date = Carbon::parse($session->start_time)->format('Ymd');
        $safeSubjectName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $subjectName);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($session->id),
            "attendance_{$safeSubjectName}_{$date}.xlsx"
        );
    }

    public function generateQr(Request $request, $sessionId)
    {
        $session = $this->teacherSession($request, $sessionId);
        
        // 🔒 SECURITY: For University-grade (HRU) security, we can rotate the token
        // to prevent students from sharing static photos of the QR code.
        $now = now();
        $open = Carbon::parse($session->checkin_open_time);
        $close = $session->checkin_close_time ? Carbon::parse($session->checkin_close_time) : Carbon::parse($session->end_time)->addMinutes(20);

        if ($session->status !== 'active') {
            if ($now->lt($open) || $now->gt($close)) {
                 return response()->json(['success' => false, 'message' => 'Attendance window closed.'], 403);
            }
        }

        // Rotate token (Dynamic QR to prevent photo sharing)
        $session->update(['qr_token' => bin2hex(random_bytes(8))]);

        return response()->json([
            'success'   => true,
            'qr_token'  => $session->qr_token,
            'scan_url'  => url("/scan/{$session->id}"),
            'expires_at' => $close->format('H:i'),
            'refresh_in' => 60 // Client should re-fetch every 60s
        ]);
    }

    public function manualCheckin(Request $request, $sessionId)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status'     => 'sometimes|in:present,late,absent'
        ]);
 
        $session = $this->teacherSession($request, $sessionId, ['classRoom.groups']);
        $status  = $request->get('status', 'present');
        $studentBelongsToSession = \App\Models\Student::where('id', $request->student_id)
            ->whereIn('group_id', $session->classRoom->groups->pluck('id'))
            ->exists();

        if (!$studentBelongsToSession) {
            return response()->json(['error' => 'Student is not enrolled in this session.'], 403);
        }
 
        if ($status === 'absent') {
            Attendance::where('student_id', $request->student_id)->where('session_id', $sessionId)->delete();
            return response()->json(['success' => true, 'message' => 'Attendance removed (Manual Clear).']);
        }
        
        $attendance = Attendance::updateOrCreate(
            ['student_id' => $request->student_id, 'session_id' => $sessionId],
            [
                'status'    => $status,
                'scan_time' => now(),
                'method'    => 'manual'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Student marked as ' . strtoupper($status) . ' manually.',
            'attendance' => $attendance
        ]);
    }

    /**
     * Delete/Reset attendance record
     */
    public function deleteAttendance(Request $request, $attendanceId)
    {
        $teacher = $this->currentTeacher($request);
        $attendance = Attendance::where('id', $attendanceId)
            ->whereHas('session.classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->firstOrFail();
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record removed.'
        ]);
    }


    /**
     * Get summary stats for the teacher
     */
    public function getSummary(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') return response()->json(['error' => 'Forbidden'], 403);

        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher not found'], 404);

        $this->syncSessionStatuses($teacher->id);

        $classes = \App\Models\ClassRoom::where('teacher_id', $teacher->id)->get();
        $classIds = $classes->pluck('id');
        $sessions = AttendanceSession::whereIn('class_id', $classIds)->get();

        $totalStudents = 0;
        $totalPossible = 0;
        foreach ($classes as $class) {
            $groupIds = $class->groups->pluck('id');
            $count = \App\Models\Student::whereIn('group_id', $groupIds)->count();
            $totalStudents += $count;
            $totalPossible += $count * $sessions->where('class_id', $class->id)->count();
        }
        $totalAttendance = Attendance::whereIn('session_id', $sessions->pluck('id'))->count();
        $rate = $totalPossible > 0 ? round(($totalAttendance / $totalPossible) * 100) : 0;

        return response()->json([
            'teacher' => $teacher->user->name,
            'total_classes' => $classes->count(),
            'total_students' => $totalStudents,
            'total_sessions' => $sessions->count(),
            'total_scans' => $totalAttendance,
            'attendance_rate' => $rate,
            'active_sessions' => $sessions->where('status', 'active')->count(),
        ]);
    }

    /**
     * List all students in teacher's classes with attendance stats
     */
    public function getStudents(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher not found'], 404);

        $classes = \App\Models\ClassRoom::where('teacher_id', $teacher->id)->with('groups')->get();
        $groupIds = $classes->flatMap(fn($c) => $c->groups->pluck('id'))->unique();
        $students = \App\Models\Student::with(['user', 'group', 'major.department'])
            ->whereIn('group_id', $groupIds)
            ->get();

        return response()->json($students->map(function ($student) use ($classes) {
            $myClassIds = $classes->filter(function($c) use ($student) {
                return $c->groups->contains($student->group_id);
            })->pluck('id');
            $totalSessions = \App\Models\AttendanceSession::whereIn('class_id', $myClassIds)->count();
            $attended = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])
                ->count();
            
            $percentage = $totalSessions > 0 ? round(($attended / $totalSessions) * 100) : 0;
            
            return [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name ?? 'Unknown',
                'student_code' => $student->student_code,
                'attendance_percentage' => $percentage,
                'status' => $percentage > 85 ? 'Excellent' : ($percentage > 70 ? 'Good' : 'Warning'),
                'group' => [
                    'id' => $student->group_id,
                    'name' => $student->group->name ?? 'Unknown',
                ],
                'major' => [
                    'id' => $student->major_id,
                    'name' => $student->major->name ?? 'Unknown',
                ],
                'department' => [
                    'name' => $student->major->department->name ?? 'Unknown',
                ]
            ];
        }));
    }

    /**
     * List all students in a specific class with attendance stats
     */
    public function getStudentsByClass(Request $request, $classId)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher not found'], 404);

        $class = \App\Models\ClassRoom::with('groups')->where('id', $classId)->where('teacher_id', $teacher->id)->firstOrFail();
        
        $students = \App\Models\Student::with(['user', 'group', 'major.department'])
            ->whereIn('group_id', $class->groups->pluck('id'))
            ->get();

        $sessionIds = \App\Models\AttendanceSession::where('class_id', $class->id)->pluck('id');
        $totalSessions = $sessionIds->count();

        return response()->json($students->map(function ($student) use ($class, $totalSessions, $sessionIds) {
            $attended = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('session_id', $sessionIds)
                ->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])
                ->count();
            
            $percentage = $totalSessions > 0 ? round(($attended / $totalSessions) * 100) : 0;
            
            return [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name ?? 'Unknown',
                'student_code' => $student->student_code,
                'attendance_percentage' => $percentage,
                'status' => $percentage > 85 ? 'Excellent' : ($percentage > 70 ? 'Good' : 'Warning'),
                'group' => [
                    'id' => $student->group_id,
                    'name' => $student->group->name ?? 'Unknown',
                ],
                'major' => [
                    'id' => $student->major_id,
                    'name' => $student->major->name ?? 'Unknown',
                ],
                'department' => [
                    'name' => $student->major->department->name ?? 'Unknown',
                ]
            ];
        }));
    }

    /**
     * List all classes assigned to teacher
     */
    public function getClasses(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'teacher') return response()->json(['error' => 'Forbidden'], 403);

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher record not found'], 404);

        $classes = \App\Models\ClassRoom::with(['subject', 'groups'])
            ->where('teacher_id', $teacher->id)
            ->get();

        $classes->transform(function ($class) {
            $sessions = AttendanceSession::where('class_id', $class->id)->get();
            $sessionsCount = $sessions->count();
            $totalStudents = \App\Models\Student::whereIn('group_id', $class->groups->pluck('id'))->count();
            
            $attended = Attendance::whereIn('session_id', $sessions->pluck('id'))->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
            $totalPossible = $sessionsCount * $totalStudents;
            $efficacy = $totalPossible > 0 ? round(($attended / $totalPossible) * 100) : 0;

            return [
                'id' => $class->id,
                'name' => $class->subject->name ?? 'N/A',
                'code' => $class->subject->code ?? 'N/A',
                'room' => $class->room_number,
                'group_name' => $class->groups->pluck('name')->join(', ') ?: 'N/A',
                'schedule' => $class->schedule,
                'sessions_count' => $sessionsCount,
                'total_students_count' => $totalStudents,
                'presence_count' => $attended,
                'efficacy' => $efficacy
            ];
        });

        return response()->json($classes);
    }

    /**
     * Regenerate QR Token for a session
     */
    public function regenerateQr(Request $request, $sessionId)
    {
        $session = $this->teacherSession($request, $sessionId);
        
        $newToken = bin2hex(random_bytes(8));
        $session->update(['qr_token' => $newToken]);

        return response()->json([
            'success' => true,
            'qr_token' => $newToken,
            'scan_url' => url("/scan/{$session->id}"),
            'expires_at' => Carbon::parse($session->end_time)->format('H:i')
        ]);
    }

    /**
     * Update session status manually (Teacher override)
     */
    public function updateStatus(Request $request, $sessionId)
    {
        $request->validate(['status' => 'required|in:active,scheduled,completed,passed,skipped']);
        
        $session = $this->teacherSession($request, $sessionId);
        
        $status = $request->status;
        if ($status === 'passed') $status = 'completed';

        $data = ['status' => $status];
        
        //  Logic Fix: If manually activating a session that is technically past its end time,
        // we extend the end time by 60 minutes. This prevents the auto-sync logic
        // from immediately switching it back to 'completed' on the next refresh.
        if ($status === 'active' && now()->gt($session->end_time)) {
             $data['end_time'] = now()->addMinutes(60);
        }

        $session->update($data);

        // Notify Telegram if completed
        if ($status === 'completed') {
            $this->telegram->sendAttendanceReport($session->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session status updated to ' . $status . ($status === 'completed' ? ' and report sent to Telegram.' : ''),
            'session' => $session
        ]);
    }
    /**
     * Get the current teacher's semester assignments
     */
    public function mySemesters(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher not found'], 404);

        // Uses the newly added hasManyThrough relation
        $assignments = $teacher->semesterAssignments()
            ->with('classRoom.subject')
            ->orderBy('academic_year', 'desc')
            ->orderBy('semester', 'asc')
            ->get()
            ->map(function (SemesterAssignment $a) {
                $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : null;
                return [
                    'id'            => $a->id,
                    'class_id'      => $a->class_id,
                    'academic_year' => $a->academic_year,
                    'semester'      => $a->semester,
                    'start_date'    => $fmt($a->start_date),
                    'end_date'      => $fmt($a->end_date),
                    'holiday_start' => $fmt($a->holiday_start),
                    'holiday_end'   => $fmt($a->holiday_end),
                    'status'        => $a->status,
                    'notes'         => $a->notes,
                    'progress'      => $a->progress, // Model accessor
                    'active_days'   => $a->active_days, // Model accessor
                    'in_holiday'    => $a->in_holiday, // Model accessor
                    'class_name'    => $a->classRoom->subject->name ?? 'Unknown',
                    'admin_score'   => $a->admin_score,
                    'teacher_score' => $a->teacher_score,
                    'grading_status'=> $a->grading_status,
                ];
            });

        return response()->json(['success' => true, 'data' => $assignments]);
    }

    /**
     * Get detailed student attendance history for teacher
     */
    public function getStudentDetail(Request $request, $studentId)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) return response()->json(['error' => 'Teacher record not found'], 404);

        $student = \App\Models\Student::findOrFail($studentId);
        
        // Ensure student is in one of teacher's class groups
        $isMyStudent = \App\Models\ClassRoom::whereHas('groups', function($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->where('teacher_id', $teacher->id)
            ->exists();
            
        if (!$isMyStudent) return response()->json(['error' => 'Unauthorized'], 403);

        $sessions = AttendanceSession::whereHas('classRoom.groups', function($q) use ($student) {
                $q->where('class_groups.id', $student->group_id);
            })
            ->whereHas('classRoom', fn($q) => $q->where('teacher_id', $teacher->id))
            ->with('classRoom.subject')
            ->orderBy('id', 'desc')
            ->get();

        $attendance = Attendance::where('student_id', $student->id)->get()->keyBy('session_id');
        $studentPermissions = \App\Models\StudentPermission::where('student_id', $student->id)->get();

        $history = $sessions->map(function ($session) use ($attendance, $studentPermissions) {
            $record = $attendance->get($session->id);
            $status = 'ABSENT';
            $isFuture = Carbon::parse($session->start_time)->isFuture();

            if ($record) {
                $status = strtoupper($record->status);
            } else {
                $sessionDate = Carbon::parse($session->start_time)->toDateString();
                $hasPerm = $studentPermissions->contains(function($p) use ($sessionDate) {
                    return $p->start_date <= $sessionDate && $p->end_date >= $sessionDate;
                });
                
                if ($hasPerm) {
                    $status = 'EXCUSED';
                } elseif ($session->status === 'scheduled' || $isFuture) {
                    $status = 'SCHEDULED';
                }
            }

            return [
                'session_id' => $session->id,
                'subject' => $session->classRoom->subject->name ?? 'N/A',
                'date' => $session->start_time,
                'status' => $status,
                'scan_time' => $record ? Carbon::parse($record->scan_time)->format('H:i') : null,
                'method' => $record ? strtoupper($record->method) : null,
            ];
        });

        $totalSessions = $sessions->count();
        $attendedCount = $attendance->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
        
        // Count excused sessions
        $excusedCount = $sessions->filter(function($session) use ($attendance, $studentPermissions) {
            if ($attendance->has($session->id)) return false;
            $sessionDate = Carbon::parse($session->start_time)->toDateString();
            return $studentPermissions->contains(function($p) use ($sessionDate) {
                return $p->start_date <= $sessionDate && $p->end_date >= $sessionDate;
            });
        })->count();

        $rate = $totalSessions > 0 ? round(($attendedCount / $totalSessions) * 100) : 0;

        return response()->json([
            'success' => true,
            'student' => $student->load(['user', 'group', 'major.department']),
            'stats' => [
                'total_sessions' => $totalSessions,
                'attended_count' => $attendedCount,
                'excused_count' => $excusedCount,
                'absent_count' => max(0, $totalSessions - $attendedCount - $excusedCount),
                'attendance_rate' => $rate,
            ],
            'history' => $history
        ]);
    }

    /**
     * Live Attendance Feed for real-time monitoring
     */
    public function liveFeed(Request $request, $sessionId)
    {
        $lastId = $request->get('last_id', 0);
        $session = $this->teacherSession($request, $sessionId, ['classRoom.groups']);
        
        $newRecords = Attendance::with('student.user')
            ->where('session_id', $sessionId)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($att) {
                return [
                    'id' => $att->id,
                    'student_name' => $att->student->user->name ?? 'Unknown',
                    'student_code' => $att->student->student_code,
                    'status' => strtoupper($att->status),
                    'time' => Carbon::parse($att->scan_time)->format('H:i:s'),
                    'method' => strtoupper($att->method),
                ];
            });

        $sessionDate = Carbon::parse($session->start_time)->toDateString();
        $groupIds = $session->classRoom ? $session->classRoom->groups->pluck('id') : collect();
        $studentIds = \App\Models\Student::whereIn('group_id', $groupIds)->pluck('id');
        $excusedCount = \App\Models\StudentPermission::where('start_date', '<=', $sessionDate)
            ->where('end_date', '>=', $sessionDate)
            ->whereIn('student_id', $studentIds)
            ->count();

        $stats = [
            'present_count' => Attendance::where('session_id', $sessionId)->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count(),
            'excused_count' => $excusedCount,
            'total_students' => $studentIds->count(),
        ];

        return response()->json([
            'success' => true,
            'new_records' => $newRecords,
            'stats' => $stats
        ]);
    }

    public function updateSemesterScore(Request $request, $assignmentId)
    {
        $request->validate([
            'teacher_score' => 'required|numeric|min:0|max:100',
            'grading_notes' => 'nullable|string'
        ]);

        $user = $request->user();
        $assignment = $this->teacherAssignment($request, $assignmentId, ['classRoom']);

        $assignment->update([
            'teacher_score' => $request->teacher_score,
            'grading_notes' => $request->grading_notes,
            'grading_status' => 'reviewed' // Teacher review moves it to 'reviewed'
        ]);

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    public function getStudentScores(Request $request, $assignmentId)
    {
        $assignment = $this->teacherAssignment($request, $assignmentId, ['classRoom.groups']);
        $class = $assignment->classRoom;
        
        $sessions = \App\Models\AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int)$assignment->semester)
            ->get();
        $attendanceScores = app(SemesterAttendanceScoreService::class);

        $students = $class->all_students;

        $savedScores = \DB::table('semester_assignment_scores')
            ->where('assignment_id', $assignmentId)
            ->get()
            ->keyBy('student_id');

        $scores = $students->map(function ($student) use ($sessions, $savedScores, $attendanceScores) {
            $attendanceResult = $attendanceScores->calculate($student->id, $sessions);
            $saved = $savedScores->get($student->id);

            return [
                'student_id' => $student->id,
                'attendance_score' => $attendanceResult['score'],
                'attended_sessions' => $attendanceResult['attended_sessions'],
                'permission_sessions' => $attendanceResult['permission_sessions'],
                'midterm_score' => $saved->midterm_score ?? null,
                'assignment_score' => $saved->assignment_score ?? null,
                'final_score' => $saved->final_score ?? null,
                'score' => $saved->score ?? null
            ];
        });
            
        return response()->json(['success' => true, 'data' => $scores]);
    }

    public function updateStudentScores(Request $request, $assignmentId)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.attendance_score' => 'nullable|numeric|min:0|max:20',
            'scores.*.midterm_score' => 'nullable|numeric|min:0|max:15',
            'scores.*.assignment_score' => 'nullable|numeric|min:0|max:15',
            'scores.*.final_score' => 'nullable|numeric|min:0|max:50',
        ]);

        $assignment = $this->teacherAssignment($request, $assignmentId, ['classRoom.groups']);
        $allowedStudentIds = $assignment->classRoom->all_students->pluck('id');
        $sessions = AttendanceSession::where('class_id', $assignment->class_id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int) $assignment->semester)
            ->get();
        $attendanceScores = app(SemesterAttendanceScoreService::class);
        
        foreach ($request->scores as $s) {
            if (!$allowedStudentIds->contains((int) $s['student_id'])) {
                return response()->json(['error' => 'Student is not enrolled in this assignment.'], 403);
            }

            $attendance = $attendanceScores->calculate((int) $s['student_id'], $sessions)['score'];
            $midterm = $s['midterm_score'] ?? 0;
            $assignment_score = $s['assignment_score'] ?? 0;
            $final = $s['final_score'] ?? 0;
            
            // Get existing record to preserve created_at
            $existing = \DB::table('semester_assignment_scores')
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $s['student_id'])
                ->first();
                
            $total = $attendance + $midterm + $assignment_score + $final;

            \DB::table('semester_assignment_scores')->updateOrInsert(
                ['assignment_id' => $assignmentId, 'student_id' => $s['student_id']],
                [
                    'attendance_score' => $attendance,
                    'midterm_score' => $midterm,
                    'assignment_score' => $assignment_score,
                    'final_score' => $final,
                    'score' => $total,
                    'updated_at' => now(),
                    'created_at' => $existing ? $existing->created_at : now()
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Student scores updated successfully.']);
    }

    public function exportSubjectScores(Request $request, $assignmentId)
    {
        $assignment = $this->teacherAssignment($request, $assignmentId, ['classRoom.subject', 'classRoom.groups']);
        $class = $assignment->classRoom;
        
        // Use the common logic to get scores
        $adminCtrl = new AdminController();
        $data = $adminCtrl->getGradingPreviewData($assignment);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SubjectScoresExport($data, $class->subject->name),
            "Scores_{$class->subject->name}.xlsx"
        );
    }

    public function exportSubjectScoresPdf(Request $request, $assignmentId)
    {
        $this->teacherAssignment($request, $assignmentId, ['classRoom.subject', 'classRoom.groups']);

        $adminCtrl = new AdminController();
        return $adminCtrl->generateSemesterReport($assignmentId);
    }
}
