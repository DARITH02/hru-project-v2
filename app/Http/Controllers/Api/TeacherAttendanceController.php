<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentPermission;
use App\Models\TeacherAttendanceCorrection;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherClassChangeRequest;
use App\Models\TeacherSchedule;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    public function __construct(private TeacherAttendanceService $attendanceService)
    {
    }

    public function schedules(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        return response()->json(TeacherSchedule::with(['subject', 'classRoom', 'classGroup', 'attendanceSession'])
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('from'), fn($q) => $q->whereDate('schedule_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('schedule_date', '<=', $request->to))
            ->orderBy('scheduled_start_time')
            ->paginate(20));
    }

    public function sessions(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);
        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        return response()->json(TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule'])
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('status'), fn($q) => $q->where('attendance_status', $request->status))
            ->when($request->filled('from'), fn($q) => $q->whereDate('attendance_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('attendance_date', '<=', $request->to))
            ->orderByDesc('attendance_date')
            ->paginate($perPage));
    }

    public function today(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $this->attendanceService->markAutomatedStatuses();

        return response()->json([
            'date' => today()->toDateString(),
            'sessions' => TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule', 'autoCheckInSourceSession'])
                ->where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', today())
                ->orderBy('scheduled_start_time')
                ->get(),
        ]);
    }

    public function show(Request $request, TeacherAttendanceSession $session)
    {
        $this->authorizeTeacherSession($request, $session);

        return response()->json($session->load(['subject', 'classRoom', 'classGroup', 'schedule', 'logs.actor']));
    }

    public function checkIn(Request $request, TeacherAttendanceSession $session)
    {
        $this->authorizeTeacherSession($request, $session);

        if ($session->session_number === 1) {
            abort(422, 'Session 1 requires QR check-in.');
        }

        return response()->json([
            'success' => true,
            'session' => $this->attendanceService->checkInFromPriorSameSubjectSession($session, $request),
        ]);
    }

    public function qrCheckIn(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'token' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        return response()->json([
            'success' => true,
            'session' => $this->attendanceService->qrCheckIn($data['token'], $teacher, $request),
        ]);
    }

    public function checkOut(Request $request, TeacherAttendanceSession $session)
    {
        $this->authorizeTeacherSession($request, $session);

        return response()->json([
            'success' => true,
            'session' => $this->attendanceService->checkOut($session, $request, $request->input('method', 'manual')),
        ]);
    }

    public function requiredCheckouts(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $sessions = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule', 'autoCheckInSourceSession'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', Carbon::parse($request->input('date', today()->toDateString())))
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission'])
            ->orderBy('scheduled_start_time')
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    public function storeCorrection(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'attendance_session_id' => 'nullable|exists:teacher_attendance_sessions,id',
            'schedule_id' => 'nullable|exists:teacher_schedules,id',
            'request_type' => 'required|in:missing_check_in,missing_check_out,wrong_status,internet_problem,schedule_change,other',
            'requested_check_in_time' => 'nullable|date',
            'requested_check_out_time' => 'nullable|date',
            'requested_status' => 'nullable|string|max:50',
            'reason' => 'required|string|max:2000',
        ]);

        if (!empty($data['attendance_session_id'])) {
            $session = TeacherAttendanceSession::where('teacher_id', $teacher->id)->findOrFail($data['attendance_session_id']);
            $data['schedule_id'] = $data['schedule_id'] ?? $session->schedule_id;
        }

        if (!empty($data['schedule_id'])) {
            TeacherSchedule::where('teacher_id', $teacher->id)->findOrFail($data['schedule_id']);
        }

        $correction = TeacherAttendanceCorrection::create($data + [
            'teacher_id' => $teacher->id,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'correction' => $correction->load(['attendanceSession', 'schedule'])], 201);
    }

    public function corrections(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        return response()->json(TeacherAttendanceCorrection::with(['attendanceSession.subject', 'schedule.subject'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20));
    }

    public function storeClassChange(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'schedule_id' => 'required|exists:teacher_schedules,id',
            'request_type' => 'required|in:cancellation,reschedule,replacement',
            'requested_date' => 'nullable|date',
            'requested_start_time' => 'nullable|date',
            'requested_end_time' => 'nullable|date|after:requested_start_time',
            'requested_room_name' => 'nullable|string|max:100',
            'reason' => 'required|string|max:2000',
        ]);

        TeacherSchedule::where('teacher_id', $teacher->id)->findOrFail($data['schedule_id']);

        $change = TeacherClassChangeRequest::create($data + [
            'teacher_id' => $teacher->id,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'request' => $change->load('schedule')], 201);
    }

    public function classChanges(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        return response()->json(TeacherClassChangeRequest::with(['schedule.subject', 'replacementSchedule'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20));
    }

    public function storeStudentPermissionRequest(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_session_id' => 'nullable|exists:attendance_sessions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:sick,event,personal,official',
            'reason' => 'required|string|max:1000',
        ]);

        $allowedStudentIds = $teacher->classes()
            ->with('groups')
            ->get()
            ->flatMap(fn ($class) => $class->groups->pluck('id')->push($class->group_id))
            ->filter()
            ->unique()
            ->pipe(fn ($groupIds) => \App\Models\Student::whereIn('group_id', $groupIds)->pluck('id'))
            ->all();

        abort_unless(in_array((int) $data['student_id'], $allowedStudentIds, true), 403);

        if (!empty($data['attendance_session_id'])) {
            $sessionBelongsToTeacher = \App\Models\AttendanceSession::where('id', $data['attendance_session_id'])
                ->whereHas('classRoom', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->exists();

            abort_unless($sessionBelongsToTeacher, 403);
        }

        $permission = StudentPermission::withoutGlobalScope('approved')->create($data + [
            'status' => 'pending',
            'requested_by' => $request->user()->id,
            'requested_by_teacher_id' => $teacher->id,
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'message' => 'Student permission request submitted. It must be approved by admin within 7 days or the student remains absent.',
        ], 201);
    }

    private function authorizeTeacherSession(Request $request, TeacherAttendanceSession $session): void
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher && $teacher->id === $session->teacher_id, 403);
    }
}
