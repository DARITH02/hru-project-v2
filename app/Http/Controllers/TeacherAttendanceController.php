<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendanceCorrection;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherClassChangeRequest;
use App\Models\TeacherSchedule;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Services\TeacherAttendanceService;
use Illuminate\Auth\Access\AuthorizationException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceController extends Controller
{
    public function __construct(private TeacherAttendanceService $attendanceService)
    {
    }

    public function index(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $this->attendanceService->markAutomatedStatuses();
        $today = today();

        $todaySessions = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', $today)
            ->orderBy('scheduled_start_time')
            ->get();

        $upcoming = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', '>', $today)
            ->orderBy('scheduled_start_time')
            ->limit(8)
            ->get();

        $history = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup'])
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('status'), fn($q) => $q->where('attendance_status', $request->status))
            ->when($request->filled('from'), fn($q) => $q->whereDate('attendance_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('attendance_date', '<=', $request->to))
            ->orderByDesc('attendance_date')
            ->orderByDesc('scheduled_start_time')
            ->paginate(15)
            ->appends($request->all());

        $pendingCorrections = TeacherAttendanceCorrection::where('teacher_id', $teacher->id)->where('status', 'pending')->count();
        $pendingChanges = TeacherClassChangeRequest::where('teacher_id', $teacher->id)->where('status', 'pending')->count();
        $pendingPermissionRequests = StudentPermission::withoutGlobalScope('approved')
            ->where('requested_by_teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->count();
        $permissionStudents = $this->studentsForTeacher($teacher);

        $monthStart = now()->startOfMonth();
        $percentage = $this->attendanceService->teacherAttendancePercentage($teacher, $monthStart, now());

        return view('teacher.attendance', compact('todaySessions', 'upcoming', 'history', 'pendingCorrections', 'pendingChanges', 'pendingPermissionRequests', 'permissionStudents', 'percentage'));
    }

    public function scan(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $todaySessions = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', today())
            ->orderBy('scheduled_start_time')
            ->get();

        return view('teacher.attendance_scan', [
            'todaySessions' => $todaySessions,
            'prefilledToken' => $request->query('token'),
        ]);
    }

    public function publicScan(Request $request)
    {
        $token = $request->query('token');
        abort_unless($token, 404);

        $qr = $this->attendanceService->findQrToken($token);
        abort_unless($qr, 404, 'QR token not found.');

        return view('teacher.attendance_public_scan', [
            'token' => $token,
            'qr' => $qr->load(['attendanceSession.teacher.user', 'attendanceSession.subject', 'attendanceSession.classRoom', 'attendanceSession.classGroup']),
            'requireLocation' => \App\Models\Setting::get('require_location', 'true') === 'true',
        ]);
    }

    public function publicQrCheckIn(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'attendance_action' => 'required|in:check_in,check_out',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
        ], [
            'latitude.required' => 'Phone location is required. Allow location access and try again.',
            'longitude.required' => 'Phone location is required. Allow location access and try again.',
        ]);

        $qr = $this->attendanceService->findQrToken($data['token']);
        $teacher = $qr?->attendanceSession?->teacher;

        if (!$teacher) {
            return back()
                ->withErrors(['token' => 'The QR code teacher could not be found. Ask the admin to refresh the QR code.']);
        }

        try {
            $session = $this->attendanceService->qrSubmit($data['token'], $teacher, $data['attendance_action'], $request);
        } catch (AuthorizationException $e) {
            return back()
                ->withErrors(['token' => 'This QR code is not assigned to that teacher.']);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors());
        }

        return view('teacher.attendance_public_scan_success', [
            'session' => $session,
            'action' => $data['attendance_action'],
        ]);
    }

    public function checkoutPage(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404, 'Teacher profile not found.');

        $sessions = TeacherAttendanceSession::with(['subject', 'classRoom', 'classGroup', 'schedule', 'autoCheckInSourceSession'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', today())
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission'])
            ->orderBy('scheduled_start_time')
            ->get();

        return view('teacher.attendance_checkout', compact('sessions'));
    }

    public function qrCheckIn(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'token' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $session = $this->attendanceService->qrCheckIn($data['token'], $teacher, $request);

        return redirect()
            ->route('teacher.attendance.checkout')
            ->with('success', 'QR check-in recorded for ' . ($session->subject->name ?? 'session') . '. Use check-out when the session ends.');
    }

    public function checkIn(Request $request, TeacherAttendanceSession $session)
    {
        $this->authorizeTeacherSession($request, $session);
        if ($session->session_number === 1) {
            return back()->withErrors(['session' => 'Session 1 requires QR check-in.']);
        }

        $this->attendanceService->checkInFromPriorSameSubjectSession($session, $request);

        return back()->with('success', 'Session ' . $session->session_number . ' auto check-in recorded from session 1.');
    }

    public function checkOut(Request $request, TeacherAttendanceSession $session)
    {
        $this->authorizeTeacherSession($request, $session);
        $this->attendanceService->checkOut($session, $request, 'manual');

        return back()->with('success', 'Check-out recorded.');
    }

    public function storeCorrection(Request $request)
    {
        $teacher = $request->user()?->teacher;
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

        if ($correction->attendanceSession) {
            $this->attendanceService->log($correction->attendanceSession, 'correction_submitted', null, $correction->toArray(), $request, $correction->reason);
        }

        return back()->with('success', 'Correction request submitted.');
    }

    public function storeClassChange(Request $request)
    {
        $teacher = $request->user()?->teacher;
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

        TeacherClassChangeRequest::create($data + [
            'teacher_id' => $teacher->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Class change request submitted.');
    }

    public function storeStudentPermissionRequest(Request $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 404);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:sick,event,personal,official',
            'reason' => 'required|string|max:1000',
        ]);

        $allowedStudentIds = $this->studentsForTeacher($teacher)->pluck('id')->all();
        abort_unless(in_array((int) $data['student_id'], $allowedStudentIds, true), 403);

        StudentPermission::withoutGlobalScope('approved')->create($data + [
            'status' => 'pending',
            'requested_by' => $request->user()->id,
            'requested_by_teacher_id' => $teacher->id,
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', 'Student permission request submitted. It must be approved by admin within 7 days or the student remains absent.');
    }

    private function authorizeTeacherSession(Request $request, TeacherAttendanceSession $session): void
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher && $session->teacher_id === $teacher->id, 403);
    }

    private function studentsForTeacher(Teacher $teacher)
    {
        $classes = $teacher->classes()->with('groups')->get();
        $groupIds = $classes
            ->flatMap(fn ($class) => $class->groups->pluck('id')->push($class->group_id))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($groupIds->isEmpty()) {
            return collect();
        }

        return Student::with(['user', 'group'])
            ->whereIn('group_id', $groupIds)
            ->orderBy(
                \App\Models\User::select('name')
                    ->whereColumn('users.id', 'students.user_id')
                    ->take(1)
            )
            ->get();
    }

    private function resolveTeacherCode(string $identifier): ?Teacher
    {
        if (!Teacher::hasTeacherCodeColumn()) {
            return null;
        }

        $code = strtoupper(trim($identifier));

        return Teacher::with('user')
            ->where('teacher_code', $code)
            ->first();
    }
}
