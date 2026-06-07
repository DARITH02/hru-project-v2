<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendanceCorrection;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherClassChangeRequest;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminTeacherAttendanceController extends Controller
{
    public function __construct(private TeacherAttendanceService $attendanceService)
    {
    }

    public function dashboard(Request $request)
    {
        $this->attendanceService->markAutomatedStatuses();
        $date = Carbon::parse($request->input('date', today()->toDateString()));
        $sessions = TeacherAttendanceSession::with(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup'])
            ->whereDate('attendance_date', $date)
            ->get();

        return response()->json([
            'date' => $date->toDateString(),
            'stats' => [
                'scheduled' => $sessions->count(),
                'present' => $sessions->whereIn('attendance_status', TeacherAttendanceService::VALID_PRESENT_STATUSES)->count(),
                'late' => $sessions->whereIn('attendance_status', ['late', 'very_late'])->count(),
                'absent' => $sessions->where('attendance_status', 'absent')->count(),
                'teaching' => $sessions->where('attendance_status', 'teaching')->count(),
                'completed' => $sessions->where('attendance_status', 'completed')->count(),
                'pending_corrections' => TeacherAttendanceCorrection::where('status', 'pending')->count(),
                'pending_changes' => TeacherClassChangeRequest::where('status', 'pending')->count(),
            ],
            'sessions' => $sessions->values(),
        ]);
    }

    public function sync(Request $request)
    {
        return response()->json([
            'success' => true,
            'created' => $this->attendanceService->syncFromStudentAttendanceSessions($request->user()?->id),
        ]);
    }

    public function sessions(Request $request)
    {
        return response()->json(TeacherAttendanceSession::with(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule'])
            ->when($request->filled('status'), fn($q) => $q->where('attendance_status', $request->status))
            ->when($request->filled('from'), fn($q) => $q->whereDate('attendance_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('attendance_date', '<=', $request->to))
            ->orderByDesc('attendance_date')
            ->paginate(30));
    }

    public function qrToken(Request $request, TeacherAttendanceSession $session)
    {
        $data = $request->validate([
            'ttl_seconds' => 'nullable|integer|min:15|max:300',
        ]);

        return response()->json([
            'success' => true,
            'qr' => $this->attendanceService->generateQrToken($session, $data['ttl_seconds'] ?? 60),
            'session' => $session->load(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']),
        ]);
    }

    public function updateSession(Request $request, TeacherAttendanceSession $session)
    {
        $data = $request->validate([
            'attendance_status' => 'nullable|string|max:50',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $old = $session->toArray();
        $session->fill($data);
        $session->approved_by = $request->user()?->id;
        $this->attendanceService->recalculate($session);
        $session->save();
        $this->attendanceService->log($session, 'admin_api_override', $old, $session->fresh()->toArray(), $request, $request->input('remarks'));

        return response()->json(['success' => true, 'session' => $session->fresh()]);
    }

    public function approveCorrection(Request $request, TeacherAttendanceCorrection $correction)
    {
        return response()->json(['success' => true, 'correction' => $this->attendanceService->approveCorrection($correction, $request)]);
    }

    public function rejectCorrection(Request $request, TeacherAttendanceCorrection $correction)
    {
        return response()->json(['success' => true, 'correction' => $this->attendanceService->rejectCorrection($correction, $request)]);
    }

    public function approveClassChange(Request $request, TeacherClassChangeRequest $changeRequest)
    {
        return response()->json(['success' => true, 'request' => $this->attendanceService->approveClassChange($changeRequest, $request)]);
    }

    public function rejectClassChange(Request $request, TeacherClassChangeRequest $changeRequest)
    {
        return response()->json(['success' => true, 'request' => $this->attendanceService->rejectClassChange($changeRequest, $request)]);
    }
}
