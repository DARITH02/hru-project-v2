<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
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

    public function dashboard(Request $request)
    {
        $this->attendanceService->markAutomatedStatuses();

        $date = Carbon::parse($request->input('date', today()->toDateString()));
        $query = TeacherAttendanceSession::with(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup', 'schedule'])
            ->whereDate('attendance_date', $date);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        $sessions = $query->orderBy('scheduled_start_time')->paginate(20)->appends($request->all());
        $daySessions = TeacherAttendanceSession::whereDate('attendance_date', $date)->get();

        $stats = [
            'scheduled' => $daySessions->count(),
            'present' => $daySessions->whereIn('attendance_status', TeacherAttendanceService::VALID_PRESENT_STATUSES)->count(),
            'late' => $daySessions->whereIn('attendance_status', ['late', 'very_late'])->count(),
            'absent' => $daySessions->where('attendance_status', 'absent')->count(),
            'teaching' => $daySessions->where('attendance_status', 'teaching')->count(),
            'completed' => $daySessions->where('attendance_status', 'completed')->count(),
            'missing_checkout' => $daySessions->where('attendance_status', 'missing_check_out')->count(),
            'pending_corrections' => TeacherAttendanceCorrection::where('status', 'pending')->count(),
            'pending_changes' => TeacherClassChangeRequest::where('status', 'pending')->count(),
        ];

        $teachers = Teacher::with('user')->orderBy('id')->get()->sortBy(fn($teacher) => $teacher->user->name ?? '');
        $statuses = $this->statuses();

        return view('admin.teacher_attendance', compact('sessions', 'stats', 'teachers', 'statuses', 'date'));
    }

    public function sync(Request $request)
    {
        $created = $this->attendanceService->syncFromStudentAttendanceSessions($request->user()?->id);

        return back()->with('success', "Teacher attendance schedules synced. {$created} new schedules created.");
    }

    public function scanQr(Request $request)
    {
        $this->attendanceService->markAutomatedStatuses();

        $date = Carbon::parse($request->input('date', today()->toDateString()));
        $sessions = TeacherAttendanceSession::with(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup', 'schedule'])
            ->whereDate('attendance_date', $date)
            ->where('session_number', 1)
            ->whereNotIn('attendance_status', ['cancelled', 'rescheduled', 'permission'])
            ->orderBy('scheduled_start_time')
            ->get();

        $selectedSession = null;
        if ($request->filled('session_id')) {
            $selectedSession = $sessions->firstWhere('id', (int) $request->session_id);
        }
        $selectedSession ??= $sessions->first();

        $qr = null;
        $teacherScanUrl = null;
        if ($selectedSession) {
            $qr = $this->attendanceService->generateQrToken($selectedSession, 60);
            $teacherScanUrl = $this->teacherScanUrl($request, $qr['token']);
        }

        $scanUrlNeedsPublicHost = $teacherScanUrl && $this->usesLocalhost($teacherScanUrl);

        return view('admin.teacher_attendance_scan_qr', compact('date', 'sessions', 'selectedSession', 'qr', 'teacherScanUrl', 'scanUrlNeedsPublicHost'));
    }

    public function scanMonitor(Request $request)
    {
        $date = Carbon::parse($request->input('date', today()->toDateString()));

        return view('admin.teacher_attendance_scan_monitor', [
            'date' => $date,
            'initialPayload' => $this->scanMonitorPayload($date),
        ]);
    }

    public function scanMonitorData(Request $request)
    {
        $date = Carbon::parse($request->input('date', today()->toDateString()));

        return response()->json($this->scanMonitorPayload($date));
    }

    public function updateSession(Request $request, TeacherAttendanceSession $session)
    {
        $data = $request->validate([
            'attendance_status' => 'required|in:' . implode(',', $this->statuses()),
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $old = $session->toArray();
        $session->fill($data);
        $session->approved_by = $request->user()?->id;
        $this->attendanceService->recalculate($session);
        $session->save();
        $this->attendanceService->log($session, 'admin_override', $old, $session->fresh()->toArray(), $request, $request->input('remarks'));

        return back()->with('success', 'Teacher attendance session updated.');
    }

    public function qrToken(Request $request, TeacherAttendanceSession $session)
    {
        $qr = $this->attendanceService->generateQrToken($session, 60);
        $teacherScanUrl = $this->teacherScanUrl($request, $qr['token']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'qr' => $qr,
                'teacherScanUrl' => $teacherScanUrl,
                'session' => $session->load(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup', 'schedule']),
            ]);
        }

        return view('admin.teacher_attendance_qr', [
            'session' => $session->load(['teacher.user', 'subject', 'classRoom', 'classGroup', 'schedule']),
            'qr' => $qr,
            'teacherScanUrl' => $teacherScanUrl,
            'scanUrlNeedsPublicHost' => $this->usesLocalhost($teacherScanUrl),
        ]);
    }

    public function manualCheckIn(Request $request, TeacherAttendanceSession $session)
    {
        $time = $request->filled('check_in_time') ? Carbon::parse($request->check_in_time) : now();
        $this->attendanceService->checkIn($session, $request, 'manual', $time);

        return back()->with('success', 'Manual check-in recorded.');
    }

    public function manualCheckOut(Request $request, TeacherAttendanceSession $session)
    {
        $time = $request->filled('check_out_time') ? Carbon::parse($request->check_out_time) : now();
        $this->attendanceService->checkOut($session, $request, 'manual', $time);

        return back()->with('success', 'Manual check-out recorded.');
    }

    public function corrections(Request $request)
    {
        $corrections = TeacherAttendanceCorrection::with(['teacher.user', 'attendanceSession.subject', 'schedule.subject', 'reviewer'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->appends($request->all());

        return view('admin.teacher_attendance_corrections', compact('corrections'));
    }

    public function approveCorrection(Request $request, TeacherAttendanceCorrection $correction)
    {
        $this->attendanceService->approveCorrection($correction, $request);

        return back()->with('success', 'Correction request approved.');
    }

    public function rejectCorrection(Request $request, TeacherAttendanceCorrection $correction)
    {
        $request->validate(['review_note' => 'nullable|string|max:1000']);
        $this->attendanceService->rejectCorrection($correction, $request);

        return back()->with('success', 'Correction request rejected.');
    }

    public function changeRequests(Request $request)
    {
        $requests = TeacherClassChangeRequest::with(['teacher.user', 'schedule.subject', 'schedule.classRoom', 'replacementSchedule', 'reviewer'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->appends($request->all());

        $permissionRequests = TeacherAttendanceCorrection::with(['teacher.user', 'attendanceSession.subject', 'schedule.subject', 'reviewer'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20, ['*'], 'permission_page')
            ->appends($request->all());

        return view('admin.teacher_class_change_requests', [
            'changeRequests' => $requests,
            'permissionRequests' => $permissionRequests,
        ]);
    }

    public function approveChangeRequest(Request $request, TeacherClassChangeRequest $changeRequest)
    {
        $this->attendanceService->approveClassChange($changeRequest, $request);

        return back()->with('success', 'Class change request approved.');
    }

    public function rejectChangeRequest(Request $request, TeacherClassChangeRequest $changeRequest)
    {
        $request->validate(['review_note' => 'nullable|string|max:1000']);
        $this->attendanceService->rejectClassChange($changeRequest, $request);

        return back()->with('success', 'Class change request rejected.');
    }

    public function reports(Request $request)
    {
        $data = $this->getReportsData($request);
        return view('admin.teacher_attendance_reports', $data);
    }

    public function exportReportsPdf(Request $request)
    {
        $data = $this->getReportsData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.exports.teacher_attendance_reports_pdf', $data);
        
        $teacherSlug = str_replace(' ', '_', $data['teacherName']);
        return $pdf->download("Teacher_Attendance_Report_{$teacherSlug}_{$data['from']->format('Ymd')}_to_{$data['to']->format('Ymd')}.pdf");
    }

    public function sendReportsToTelegram(Request $request)
    {
        $data = $this->getReportsData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.exports.teacher_attendance_reports_pdf', $data);

        $teacherSlug = str_replace(' ', '_', $data['teacherName']);
        $fileName = "Teacher_Attendance_Report_{$teacherSlug}_{$data['from']->format('Ymd')}_to_{$data['to']->format('Ymd')}.pdf";
        $pdfPath = storage_path("app/public/{$fileName}");

        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        $pdf->save($pdfPath);

        $bot = \App\Models\TelegramBot::where('is_active', true)->first();
        if (!$bot) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            return back()->with('error', 'No active Telegram bot found.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::attach(
                'document',
                file_get_contents($pdfPath),
                $fileName
            )->post("https://api.telegram.org/bot{$bot->bot_token}/sendDocument", [
                'chat_id' => $bot->chat_id,
                'caption' => "📊 Teacher Attendance Report\nTeacher: {$data['teacherName']}\nPeriod: {$data['from']->format('M d, Y')} - {$data['to']->format('M d, Y')}\nGenerated by ATTENDAI Intelligence."
            ]);

            if (file_exists($pdfPath)) unlink($pdfPath);
            return back()->with('success', 'Teacher Attendance Report sent to Telegram successfully!');
        } catch (\Exception $e) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            return back()->with('error', 'Failed to send Telegram report: ' . $e->getMessage());
        }
    }

    private function getReportsData(Request $request): array
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()));
        $to = Carbon::parse($request->input('to', now()->toDateString()));

        $sessions = TeacherAttendanceSession::with(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup'])
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->when($request->filled('teacher_id'), fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->orderBy('attendance_date')
            ->orderBy('scheduled_start_time')
            ->get();

        $summary = [
            'scheduled' => $sessions->whereNotIn('attendance_status', ['cancelled', 'rescheduled'])->count(),
            'completed' => $sessions->where('attendance_status', 'completed')->count(),
            'late' => $sessions->whereIn('attendance_status', ['late', 'very_late'])->count(),
            'absent' => $sessions->where('attendance_status', 'absent')->count(),
            'teaching_hours' => round($sessions->sum('actual_teaching_hours'), 2),
            'attendance_percentage' => $sessions->whereNotIn('attendance_status', ['cancelled', 'rescheduled'])->count()
                ? round(($sessions->whereIn('attendance_status', TeacherAttendanceService::VALID_PRESENT_STATUSES)->count() / $sessions->whereNotIn('attendance_status', ['cancelled', 'rescheduled'])->count()) * 100, 2)
                : 0,
        ];

        $teacherName = 'All Teachers';
        if ($request->filled('teacher_id')) {
            $t = Teacher::with('user')->find($request->teacher_id);
            if ($t && $t->user) {
                $teacherName = $t->user->name;
            }
        }

        $teachers = Teacher::with('user')->get()->sortBy(fn($teacher) => $teacher->user->name ?? '');

        return compact('sessions', 'summary', 'teachers', 'from', 'to', 'teacherName');
    }

    private function statuses(): array
    {
        return [
            'scheduled',
            'present',
            'on_time',
            'late',
            'very_late',
            'teaching',
            'completed',
            'early_leave',
            'absent',
            'permission',
            'cancelled',
            'rescheduled',
            'missing_check_out',
        ];
    }

    private function scanMonitorPayload(Carbon $date): array
    {
        $this->attendanceService->markAutomatedStatuses();

        $sessions = TeacherAttendanceSession::with(['teacher.user', 'teacher.department', 'subject', 'classRoom', 'classGroup', 'schedule'])
            ->whereDate('attendance_date', $date)
            ->orderBy('scheduled_start_time')
            ->get();

        $items = $sessions->map(function (TeacherAttendanceSession $session) {
            $start = $session->scheduled_start_time;
            $hour = $start?->format('H');
            $shift = match (true) {
                $hour !== null && (int) $hour < 12 => 'morning',
                $hour !== null && (int) $hour < 17 => 'afternoon',
                default => 'evening',
            };

            return [
                'id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'teacher_name' => $session->teacher?->user?->name ?? 'Teacher',
                'teacher_initials' => collect(explode(' ', $session->teacher?->user?->name ?? 'T'))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->implode(''),
                'department' => $session->teacher?->department?->name ?? 'No department',
                'department_key' => strtolower(str_replace(' ', '-', $session->teacher?->department?->name ?? 'none')),
                'subject' => $session->subject?->name ?? 'Subject',
                'class_name' => $session->classGroup?->name ?? $session->classRoom?->name ?? 'Class',
                'room_name' => $session->room_name,
                'shift' => $shift,
                'session_number' => $session->session_number,
                'status' => $session->attendance_status,
                'group' => $this->monitorStatusGroup($session->attendance_status),
                'check_in_time' => $session->check_in_time?->format('H:i'),
                'check_out_time' => $session->check_out_time?->format('H:i'),
                'scheduled_start_time' => $session->scheduled_start_time?->format('H:i'),
                'scheduled_end_time' => $session->scheduled_end_time?->format('H:i'),
                'late_minutes' => $session->late_minutes,
                'scan_method' => $session->check_in_method,
                'updated_at' => $session->updated_at?->toIso8601String(),
            ];
        })->values();

        $total = $items->count();
        $present = $items->where('group', 'present')->count();
        $late = $items->where('group', 'late')->count();
        $absent = $items->where('group', 'absent')->count();
        $permission = $items->where('group', 'permission')->count();

        return [
            'date' => $date->toDateString(),
            'stats' => [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'permission' => $permission,
                'present_pct' => $total ? round(($present / $total) * 100) : 0,
                'late_pct' => $total ? round(($late / $total) * 100) : 0,
                'absent_pct' => $total ? round(($absent / $total) * 100) : 0,
                'permission_pct' => $total ? round(($permission / $total) * 100) : 0,
            ],
            'departments' => $items->pluck('department')->unique()->values(),
            'sessions' => $items,
        ];
    }

    private function monitorStatusGroup(string $status): string
    {
        return match ($status) {
            'permission' => 'permission',
            'absent', 'missing_check_out' => 'absent',
            'late', 'very_late' => 'late',
            'present', 'on_time', 'teaching', 'completed', 'early_leave' => 'present',
            default => 'absent',
        };
    }

    private function teacherScanUrl(Request $request, string $token): string
    {
        $baseUrl = rtrim((string) config('app.teacher_qr_public_url', ''), '/');
        if ($baseUrl === '') {
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        }

        return $baseUrl . route('teacher.attendance.public-scan', ['token' => $token], false);
    }

    private function usesLocalhost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
