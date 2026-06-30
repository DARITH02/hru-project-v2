<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Department;
use App\Models\AttendanceSession;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\SemesterAssignment;
use App\Models\Major;
use App\Models\ClassGroup;
use App\Models\TeacherAttendanceSession;
use App\Models\TeacherSchedule;
use App\Services\HruAttendanceAiAgentService;
use App\Services\HruAttendanceAiAssistantService;
use App\Services\SemesterAttendanceScoreService;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class AdminController extends Controller
{
    public function attendanceAssistant(
        Request $request,
        HruAttendanceAiAssistantService $assistant,
        HruAttendanceAiAgentService $agent
    )
    {
        $filters = $request->validate([
            'question' => 'nullable|string|max:1000',
            'academic_year' => 'nullable|string|max:20',
            'semester' => 'nullable|integer|min:1|max:3',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'class_id' => 'nullable|integer|exists:classes,id',
            'group_id' => 'nullable|integer|exists:class_groups,id',
            'major_id' => 'nullable|integer|exists:majors,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'teacher_id' => 'nullable|integer|exists:teachers,id',
        ]);

        return response()->json([
            'success' => true,
            'data' => $agent->enhance($assistant->analyze($filters), $filters['question'] ?? null),
        ]);
    }

    public function checkStatus(Request $request)
    {
        $data = [
            'status' => 'OK',
            'controller' => 'Api\AdminController',
            'time' => now()
        ];

        if ($request->has('sync')) {
            try {
                // 1. Pivot Table Sync
                if (!\Illuminate\Support\Facades\Schema::hasTable('class_class_group')) {
                    \Illuminate\Support\Facades\Schema::create('class_class_group', function ($table) {
                        $table->id();
                        $table->unsignedBigInteger('class_room_id');
                        $table->unsignedBigInteger('class_group_id');
                        $table->timestamps();
                        $table->foreign('class_room_id')->references('id')->on('classes')->onDelete('cascade');
                        $table->foreign('class_group_id')->references('id')->on('class_groups')->onDelete('cascade');
                    });

                    // Migrate data from classes.group_id
                    $classes = \Illuminate\Support\Facades\DB::table('classes')->whereNotNull('group_id')->get();
                    foreach ($classes as $class) {
                        \Illuminate\Support\Facades\DB::table('class_class_group')->insertOrIgnore([
                            'class_room_id' => $class->id,
                            'class_group_id' => $class->group_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 2. SemesterAssignment Grading Fields
                if (\Illuminate\Support\Facades\Schema::hasTable('semester_assignments')) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('semester_assignments', 'admin_score')) {
                        \Illuminate\Support\Facades\Schema::table('semester_assignments', function ($table) {
                            $table->decimal('admin_score', 5, 2)->nullable();
                            $table->decimal('teacher_score', 5, 2)->nullable();
                            $table->string('grading_status')->default('pending'); // pending, reviewed, finalized
                            $table->text('grading_notes')->nullable();
                        });
                    }
                }

                // 3. Individual Student Scores Table
                if (!\Illuminate\Support\Facades\Schema::hasTable('semester_assignment_scores')) {
                    \Illuminate\Support\Facades\Schema::create('semester_assignment_scores', function ($table) {
                        $table->id();
                        $table->unsignedBigInteger('assignment_id');
                        $table->unsignedBigInteger('student_id');
                        $table->decimal('score', 5, 2)->nullable();
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        $table->foreign('assignment_id')->references('id')->on('semester_assignments')->onDelete('cascade');
                        $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                        $table->unique(['assignment_id', 'student_id'], 'assignment_student_unique');
                    });
                }

                $data['sync_result'] = 'Database schema synchronized successfully.';
            } catch (\Exception $e) {
                $data['sync_result'] = 'Error: ' . $e->getMessage();
            }
        }

        return response()->json($data);
    }

    public function getStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_teachers' => Teacher::count(),
            'total_students' => Student::count(),
            'total_classes' => ClassRoom::count(),
            'total_sessions' => AttendanceSession::count(),
            'total_scans' => Attendance::count(),
            'attendance_rate' => $this->getGlobalAttendanceRate(),
            'active_sessions_now' => AttendanceSession::where('status', 'active')->count()
        ]);
    }

    private function getGlobalAttendanceRate()
    {
        $sessions = AttendanceSession::with('classRoom.groups')
            ->where('status', '!=', 'skipped')
            ->get();
        $totalPossible = 0;
        foreach ($sessions as $s) {
            if ($s->classRoom) {
                $groupIds = $s->classRoom->groups->pluck('id');
                $totalPossible += Student::whereIn('group_id', $groupIds)->count();
            }
        }
        $scans = Attendance::count();
        return $totalPossible === 0 ? 0 : round(($scans / $totalPossible) * 100, 1);
    }

    public function listUsers()
    {
        return response()->json(User::orderBy('created_at', 'desc')->get());
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,teacher,student'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            if ($request->role === 'teacher') {
                Teacher::create(['user_id' => $user->id, 'name' => $user->name]);
            }
            DB::commit();
            return response()->json(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser($userId, ChatService $chat)
    {
        $user = User::findOrFail($userId);
        if ($user->id === request()->user()?->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $chat->deleteUserChatHistory($user);
        $user->delete();

        return response()->json(['success' => true]);
    }

    // ── CLASS GROUPS (BATCHES) ─────────────────────────
    public function listClassGroups()
    {
        return response()->json(ClassGroup::with(['major.department'])->get());
    }

    public function storeClassGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:class_groups,name',
            'major_id' => 'required|exists:majors,id',
            'year_level' => 'nullable|integer',
        ]);

        $group = ClassGroup::create($request->all());
        return response()->json(['success' => true, 'group' => $group->load('major.department')]);
    }

    public function updateClassGroup(Request $request, $id)
    {
        $group = ClassGroup::findOrFail($id);
        $group->update($request->all());
        return response()->json(['success' => true, 'group' => $group->load('major.department')]);
    }

    public function deleteClassGroup($id)
    {
        $group = ClassGroup::findOrFail($id);
        $group->delete();
        return response()->json(['success' => true]);
    }

    // ── MAJORS ─────────────────────────────────────────
    public function listMajors()
    {
        return response()->json(Major::with('department')->get());
    }

    public function storeMajor(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:majors,name',
            'department_id' => 'required|exists:departments,id'
        ]);

        $major = Major::create($request->all());
        return response()->json(['success' => true, 'major' => $major->load('department')]);
    }

    public function updateMajor(Request $request, $id)
    {
        $major = Major::findOrFail($id);
        $major->update($request->all());
        return response()->json(['success' => true, 'major' => $major->load('department')]);
    }

    public function deleteMajor($id)
    {
        $major = Major::findOrFail($id);
        $major->delete();
        return response()->json(['success' => true]);
    }

    public function listClasses()
    {
        return response()->json(ClassRoom::with(['subject', 'teacher.user', 'groups'])->get());
    }

    public function assignSemester(Request $request, $classId)
    {
        $class = ClassRoom::findOrFail($classId);

        $request->validate([
            'academic_year' => 'required',
            'semester' => 'required',
            'start_date' => 'required|date',
            'holiday_start' => 'nullable|date',
            'sessions_count' => 'nullable|integer|min:1|max:100',
            'schedule_days' => 'nullable|string',
            'time_start' => 'nullable|string',
            'time_end' => 'nullable|string',
            'time_start2' => 'nullable|string',
            'time_end2' => 'nullable|string',
        ]);

        $holidayStart = $request->holiday_start ? \Carbon\Carbon::parse($request->holiday_start) : null;
        $holidayEnd = $holidayStart ? SemesterAssignment::computeHolidayEnd($holidayStart) : null;
        $endDate = SemesterAssignment::computeEndDate($request->start_date);

        $sessionsTarget = $request->sessions_count ?? 30;
        $proposedSchedule = $class->schedule;
        if ($request->time_start && $request->time_end) {
            $daysPart = $request->schedule_days ?? 'Mon-Fri';
            $slots = ["{$request->time_start}-{$request->time_end}"];
            if ($request->time_start2 && $request->time_end2) {
                $slots[] = "{$request->time_start2}-{$request->time_end2}";
            }
            $proposedSchedule = "$daysPart (" . implode(', ', $slots) . ")";
        }

        if ($conflict = $this->findTeacherScheduleConflict((int) $class->teacher_id, $proposedSchedule, (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->teacherScheduleConflictMessage($conflict),
            ], 422);
        }

        if ($conflict = $this->findClassScheduleConflict($proposedSchedule, $class->room_number, $this->classGroupIds($class), (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->classScheduleConflictMessage($conflict),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update Class Schedule if new times/days provided
            if ($request->time_start && $request->time_end) {
                $daysPart = $request->schedule_days ?? 'Mon-Fri';

                $slots = [];
                $slots[] = "{$request->time_start}-{$request->time_end}";
                if ($request->time_start2 && $request->time_end2) {
                    $slots[] = "{$request->time_start2}-{$request->time_end2}";
                }

                $class->update([
                    'schedule' => "$daysPart (" . implode(', ', $slots) . ")",
                    'semester' => $request->semester,
                    'academic_year' => $request->academic_year
                ]);
                $class->refresh(); // CRITICAL: Refresh memory model to pick up the newly updated schedule string
            } else {
                $class->update([
                    'semester' => $request->semester,
                    'academic_year' => $request->academic_year
                ]);
            }

            $assignment = SemesterAssignment::create([
                'class_id' => $classId,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
                'start_date' => $request->start_date,
                'end_date' => $endDate,
                'holiday_start' => $holidayStart,
                'holiday_end' => $holidayEnd,
                'status' => 'upcoming',
                'notes' => $request->notes,
            ]);

            // GENERATE SESSIONS (UNIFIED LOGIC)
            $sessionsCreated = $this->generateAcademicSessions(
                $class,
                $assignment->academic_year,
                $assignment->semester,
                \Carbon\Carbon::parse($request->start_date),
                $request->sessions_count ?? 30,
                $holidayStart,
                $holidayEnd
            );

            DB::commit();
            return response()->json(['success' => true, 'assignment' => $assignment, 'sessions_count' => $sessionsCreated]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unified Session Generator
     * Handles days parsing, time-slot parsing (multiple sessions per day),
     * holiday skipping, and target counting.
     */
    private function parseSchedule($schedStr)
    {
        $schedStr = strtolower($schedStr ?? '');
        if (!$schedStr)
            return [[], []];

        // 1. Parse Days
        $daysMap = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $allowedDays = [];

        if (str_contains($schedStr, 'mon-fri') || str_contains($schedStr, 'weekday')) {
            $allowedDays = [1, 2, 3, 4, 5];
        } else if (str_contains($schedStr, 'sat/sun') || str_contains($schedStr, 'weekend')) {
            $allowedDays = [6, 0];
        } else if (str_contains($schedStr, 'everyday') || str_contains($schedStr, 'full-week')) {
            $allowedDays = [0, 1, 2, 3, 4, 5, 6];
        } else {
            if (preg_match('/(mon|tue|wed|thu|fri|sat|sun)\s?[-–—]\s?(mon|tue|wed|thu|fri|sat|sun)/i', $schedStr, $matches)) {
                $start = $daysMap[strtolower($matches[1])];
                $end = $daysMap[strtolower($matches[2])];
                if ($start <= $end) {
                    for ($i = $start; $i <= $end; $i++)
                        $allowedDays[] = $i;
                } else {
                    for ($i = $start; $i <= 6; $i++)
                        $allowedDays[] = $i;
                    for ($i = 0; $i <= $end; $i++)
                        $allowedDays[] = $i;
                }
            } else {
                foreach ($daysMap as $dStr => $dNum) {
                    if (str_contains($schedStr, $dStr))
                        $allowedDays[] = $dNum;
                }
            }
        }
        if (empty($allowedDays))
            $allowedDays = [1, 2, 3, 4, 5];

        // 2. Parse Time Slots
        preg_match_all('/(\d{1,2}:\d{2}(?::\d{2})?)\s?([AP]M)?\s?[-–—]\s?(\d{1,2}:\d{2}(?::\d{2})?)\s?([AP]M)?/i', $schedStr, $matches, PREG_SET_ORDER);
        $timeSlots = [];
        foreach ($matches as $m) {
            try {
                $timeSlots[] = [
                    'start' => \Carbon\Carbon::parse($m[1] . ($m[2] ?? ''))->format('H:i'),
                    'end' => \Carbon\Carbon::parse($m[3] . ($m[4] ?? ''))->format('H:i')
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return [$allowedDays, $timeSlots];
    }

    private function findTeacherScheduleConflict(?int $teacherId, ?string $schedule, ?int $excludeClassId = null): ?ClassRoom
    {
        if (!$teacherId || !$schedule) {
            return null;
        }

        [$days, $timeSlots] = $this->parseSchedule($schedule);
        if (empty($days) || empty($timeSlots)) {
            return null;
        }

        $classes = ClassRoom::with(['subject', 'groups'])
            ->where('teacher_id', $teacherId)
            ->whereNotNull('schedule')
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'archived'))
            ->when($excludeClassId, fn ($query) => $query->where('id', '!=', $excludeClassId))
            ->get();

        foreach ($classes as $class) {
            [$existingDays, $existingSlots] = $this->parseSchedule($class->schedule);
            if (empty(array_intersect($days, $existingDays))) {
                continue;
            }

            foreach ($timeSlots as $slot) {
                foreach ($existingSlots as $existingSlot) {
                    if ($this->timeSlotsOverlap($slot, $existingSlot)) {
                        return $class;
                    }
                }
            }
        }

        return null;
    }

    private function findClassScheduleConflict(?string $schedule, ?string $roomNumber = null, array $groupIds = [], ?int $excludeClassId = null): ?array
    {
        if (!$schedule) {
            return null;
        }

        [$days, $timeSlots] = $this->parseSchedule($schedule);
        if (empty($days) || empty($timeSlots)) {
            return null;
        }

        $roomNumber = trim((string) $roomNumber);
        $groupIds = collect($groupIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($roomNumber === '' && empty($groupIds)) {
            return null;
        }

        $classes = ClassRoom::with(['subject', 'groups'])
            ->whereNotNull('schedule')
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'archived'))
            ->when($excludeClassId, fn ($query) => $query->where('id', '!=', $excludeClassId))
            ->get();

        foreach ($classes as $class) {
            [$existingDays, $existingSlots] = $this->parseSchedule($class->schedule);
            if (empty(array_intersect($days, $existingDays))) {
                continue;
            }

            $hasOverlappingTime = false;
            foreach ($timeSlots as $slot) {
                foreach ($existingSlots as $existingSlot) {
                    if ($this->timeSlotsOverlap($slot, $existingSlot)) {
                        $hasOverlappingTime = true;
                        break 2;
                    }
                }
            }

            if (!$hasOverlappingTime) {
                continue;
            }

            if ($roomNumber !== '' && strcasecmp(trim((string) $class->room_number), $roomNumber) === 0) {
                return ['type' => 'room', 'class' => $class];
            }

            $existingGroupIds = $this->classGroupIds($class);
            if (!empty($groupIds) && !empty(array_intersect($groupIds, $existingGroupIds))) {
                return ['type' => 'group', 'class' => $class];
            }
        }

        return null;
    }

    private function classGroupIds(ClassRoom $class): array
    {
        $class->loadMissing('groups');
        $groupIds = $class->groups->pluck('id')->all();

        if ($class->group_id) {
            $groupIds[] = (int) $class->group_id;
        }

        return collect($groupIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function timeSlotsOverlap(array $slot, array $existingSlot): bool
    {
        $start = $this->minutesFromTime($slot['start'] ?? null);
        $end = $this->minutesFromTime($slot['end'] ?? null);
        $existingStart = $this->minutesFromTime($existingSlot['start'] ?? null);
        $existingEnd = $this->minutesFromTime($existingSlot['end'] ?? null);

        if ($start === null || $end === null || $existingStart === null || $existingEnd === null) {
            return false;
        }

        return $start < $existingEnd && $end > $existingStart;
    }

    private function minutesFromTime(?string $time): ?int
    {
        if (!$time || !preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return null;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }

    private function teacherScheduleConflictMessage(ClassRoom $class): string
    {
        $subject = $class->subject->name ?? "Class #{$class->id}";
        $groups = $class->groups->pluck('name')->filter()->join(', ');
        $groupText = $groups ? " for {$groups}" : '';

        return "This teacher is already assigned to {$subject}{$groupText} at {$class->schedule}. Choose another teacher, day, or time.";
    }

    private function classScheduleConflictMessage(array $conflict): string
    {
        /** @var ClassRoom $class */
        $class = $conflict['class'];
        $subject = $class->subject->name ?? "Class #{$class->id}";
        $room = $class->room_number ? " in {$class->room_number}" : '';
        $groups = $class->groups->pluck('name')->filter()->join(', ');
        $groupText = $groups ? " for {$groups}" : '';

        if ($conflict['type'] === 'room') {
            return "Room {$class->room_number} is already used by {$subject}{$groupText} at {$class->schedule}. Choose another room, day, or time.";
        }

        return "This class group is already assigned to {$subject}{$room} at {$class->schedule}. Choose another group, day, or time.";
    }

    private function generateAcademicSessions($class, $year, $semester, $startDate, $targetCount, $holidayStart = null, $holidayEnd = null)
    {
        list($allowedDays, $timeSlots) = $this->parseSchedule($class->schedule);
        if (empty($timeSlots))
            return 0;

        // 3. Clear existing scheduled (future) sessions
        $scheduledSessionIds = \App\Models\AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $year)
            ->where('semester', (int) $semester)
            ->where('status', 'scheduled')
            ->pluck('id');
        $this->deleteCourseSessionsWithTeacherAttendance($scheduledSessionIds);

        $currentDate = $startDate->copy();
        $created = 0;
        $maxIter = 400; // Safety break

        while ($created < $targetCount && $maxIter > 0) {
            $maxIter--;
            if (in_array($currentDate->dayOfWeek, $allowedDays)) {
                $inHoliday = false;
                if ($holidayStart && $holidayEnd) {
                    $inHoliday = $currentDate->between($holidayStart, $holidayEnd);
                }

                if (!$inHoliday) {
                    foreach ($timeSlots as $slot) {
                        if ($created >= $targetCount)
                            break;

                        $sTime = $currentDate->copy()->setTimeFromTimeString($slot['start']);
                        $eTime = $currentDate->copy()->setTimeFromTimeString($slot['end']);

                        \App\Models\AttendanceSession::create([
                            'class_id' => $class->id,
                            'start_time' => $sTime,
                            'end_time' => $eTime,
                            'checkin_open_time' => $sTime->copy()->subMinutes(20),
                            'checkin_close_time' => $sTime->copy()->addMinutes(20),
                            'semester' => (int) $semester,
                            'academic_year' => $year,
                            'status' => $sTime->isPast() ? 'completed' : 'scheduled',
                            'qr_token' => bin2hex(random_bytes(8))
                        ]);
                        $created++;
                    }
                }
            }
            $currentDate->addDay();
        }
        return $created;
    }

    public function listClassSemesters($classId)
    {
        return response()->json(SemesterAssignment::where('class_id', $classId)->orderBy('start_date', 'desc')->get());
    }

    public function deleteSemester($id)
    {
        $assignment = SemesterAssignment::findOrFail($id);

        DB::beginTransaction();
        try {
            // Purge all generated sessions for this class and semester
            $sessionIds = \App\Models\AttendanceSession::where('class_id', $assignment->class_id)
                ->where('academic_year', $assignment->academic_year)
                ->where('semester', (int) $assignment->semester)
                ->pluck('id');
            $this->deleteCourseSessionsWithTeacherAttendance($sessionIds);

            $assignment->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeClass(Request $request)
    {
        $data = $request->all();
        if ($request->has('schedule_days') && $request->has('time_start')) {
            $data['schedule'] = $request->schedule_days . ' (' . $request->time_start . '-' . $request->time_end . ')';
        }

        if ($conflict = $this->findTeacherScheduleConflict((int) ($data['teacher_id'] ?? 0), $data['schedule'] ?? null)) {
            return response()->json([
                'success' => false,
                'error' => $this->teacherScheduleConflictMessage($conflict),
            ], 422);
        }

        $groupIds = $request->input('group_ids', $request->input('group_id') ? [$request->input('group_id')] : []);
        if ($conflict = $this->findClassScheduleConflict($data['schedule'] ?? null, $data['room_number'] ?? null, (array) $groupIds)) {
            return response()->json([
                'success' => false,
                'error' => $this->classScheduleConflictMessage($conflict),
            ], 422);
        }

        $class = ClassRoom::create($data);
        if ($request->has('group_ids')) {
            $class->groups()->sync($request->group_ids);
        }
        return response()->json(['success' => true, 'class' => $class->load('groups')]);
    }

    public function updateClass(Request $request, $classId)
    {
        $class = ClassRoom::findOrFail($classId);
        $data = $request->all();
        if ($request->has('schedule_days') && $request->has('time_start')) {
            $data['schedule'] = $request->schedule_days . ' (' . $request->time_start . '-' . $request->time_end . ')';
        }

        $teacherId = (int) ($data['teacher_id'] ?? $class->teacher_id);
        $schedule = $data['schedule'] ?? $class->schedule;
        if ($conflict = $this->findTeacherScheduleConflict($teacherId, $schedule, (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->teacherScheduleConflictMessage($conflict),
            ], 422);
        }

        $groupIds = $request->has('group_ids')
            ? $request->input('group_ids', [])
            : $this->classGroupIds($class);
        if ($conflict = $this->findClassScheduleConflict($schedule, $data['room_number'] ?? $class->room_number, (array) $groupIds, (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->classScheduleConflictMessage($conflict),
            ], 422);
        }

        $class->update($data);
        if ($request->has('group_ids')) {
            $class->groups()->sync($request->group_ids);
        }
        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "catalog.classes#{$classId}"
        ]);
        return response()->json(['success' => true, 'class' => $class]);
    }

    public function deleteClass($classId)
    {
        $class = ClassRoom::findOrFail($classId);

        DB::beginTransaction();
        try {
            $deleted = $this->deleteClassDependencies((int) $classId);

            $class->delete();
            ActivityLog::create([
                'action' => 'DELETE',
                'target' => "catalog.classes#{$classId}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'deleted' => $deleted,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteClasses(Request $request)
    {
        $request->validate([
            'class_ids' => 'required|array',
            'class_ids.*' => 'integer|exists:classes,id'
        ]);

        $ids = collect($request->class_ids)->map(fn ($id) => (int) $id)->all();

        DB::beginTransaction();
        try {
            $summary = [
                'student_attendance_sessions' => 0,
                'teacher_attendance_sessions' => 0,
                'teacher_schedules' => 0,
            ];

            foreach ($ids as $id) {
                $deleted = $this->deleteClassDependencies($id);

                foreach ($summary as $key => $count) {
                    $summary[$key] += $deleted[$key] ?? 0;
                }

                ActivityLog::create([
                    'action' => 'DELETE',
                    'target' => "catalog.classes#{$id}",
                ]);
            }

            ClassRoom::whereIn('id', $ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' classes deleted successfully.',
                'deleted' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function endClassSchedule($classId)
    {
        $class = ClassRoom::with(['subject', 'teacher.user'])->findOrFail($classId);

        DB::beginTransaction();
        try {
            // 1. Find the current active assignment
            $assignment = SemesterAssignment::where('class_id', $classId)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$assignment) {
                return response()->json(['success' => false, 'error' => 'No active semester assignment found for this class.'], 400);
            }

            // 2. Prevent ending if not finalized (Safety Check)
            if ($assignment->grading_status !== 'finalized') {
                return response()->json(['success' => false, 'error' => 'Class results must be set to FINALIZED before ending the schedule.'], 400);
            }

            // 3. Snapshot Stats (Prevent Data Loss)
            $sessions = \App\Models\AttendanceSession::where('class_id', $classId)
                ->where('academic_year', $assignment->academic_year)
                ->where('semester', (int) $assignment->semester)
                ->get();

            $totalSessions = $sessions->count();
            $attended = 0;
            $totalPossible = 0;
            $students = $class->all_students;

            foreach ($sessions as $s) {
                if ($s->status !== 'skipped') {
                    $totalPossible += $students->count();
                    $attended += \App\Models\Attendance::where('session_id', $s->id)
                        ->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])
                        ->count();
                }
            }

            $finalRate = $totalPossible > 0 ? round(($attended / $totalPossible) * 100, 2) : 0;

            // 4. Update assignment with final stats and completion status
            $assignment->update([
                'status' => 'completed',
                'final_attendance_rate' => $finalRate,
                'final_total_sessions' => $totalSessions,
                'finalized_at' => now()
            ]);

            // 🚀 Send Telegram Notification before purging
            $this->sendTelegramSummary($class, $assignment);

            // 5. Permanently remove all sessions associated with this class (Clean Up)
            $this->deleteClassDependencies((int) $classId);

            // 6. Update Class status to archived after completion
            $class->update(['status' => 'archived']);

            ActivityLog::create([
                'action' => 'TERMINATE',
                'target' => "catalog.classes#{$classId}.purged_and_finalized"
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Class finalized and schedule purged. Final stats archived in semester records.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function sendTelegramSummary($class, $assignment)
    {
        $bot = \App\Models\TelegramBot::where('is_active', true)->first();
        if (!$bot)
            return;

        $students = $class->all_students;
        $scores = \DB::table('semester_assignment_scores')->where('assignment_id', $assignment->id)->get();
        $avgTotal = $scores->count() > 0 ? round($scores->avg('score'), 2) : 0;

        $message = "🎓 *Academic Semester Finalized*\n\n";
        $message .= "📘 *Class:* {$class->name}\n";
        $message .= "👨‍🏫 *Teacher:* " . ($class->teacher->user->name ?? 'N/A') . "\n";
        $message .= "🗓 *Period:* {$assignment->academic_year} (S{$assignment->semester})\n\n";
        $message .= "📊 *Stats:*\n";
        $message .= "• Students: " . $students->count() . "\n";
        $message .= "• Avg Score: {$avgTotal}/100\n";
        $message .= "• Status: Completed ✅\n\n";
        $message .= "Report ready for export in Admin Panel.";

        $url = "https://api.telegram.org/bot{$bot->bot_token}/sendMessage";
        \Http::post($url, [
            'chat_id' => $bot->chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function deleteClassDependencies(int $classId): array
    {
        $teacherScheduleIds = TeacherSchedule::where('class_id', $classId)->pluck('id');

        $teacherAttendanceQuery = TeacherAttendanceSession::where('class_id', $classId);
        if ($teacherScheduleIds->isNotEmpty()) {
            $teacherAttendanceQuery->orWhereIn('schedule_id', $teacherScheduleIds);
        }

        $deleted = [
            'student_attendance_sessions' => AttendanceSession::where('class_id', $classId)->count(),
            'teacher_attendance_sessions' => (clone $teacherAttendanceQuery)->count(),
            'teacher_schedules' => $teacherScheduleIds->count(),
        ];

        $this->deleteTeacherAttendanceForSchedules($teacherScheduleIds);
        AttendanceSession::where('class_id', $classId)->delete();

        return $deleted;
    }

    private function deleteCourseSessionsWithTeacherAttendance($sessionIds): array
    {
        $sessionIds = collect($sessionIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($sessionIds->isEmpty()) {
            return [
                'student_attendance_sessions' => 0,
                'teacher_attendance_sessions' => 0,
                'teacher_schedules' => 0,
            ];
        }

        $teacherScheduleIds = TeacherSchedule::whereIn('source_attendance_session_id', $sessionIds)->pluck('id');

        $deleted = [
            'student_attendance_sessions' => AttendanceSession::whereIn('id', $sessionIds)->count(),
            'teacher_attendance_sessions' => $teacherScheduleIds->isEmpty()
                ? 0
                : TeacherAttendanceSession::whereIn('schedule_id', $teacherScheduleIds)->count(),
            'teacher_schedules' => $teacherScheduleIds->count(),
        ];

        $this->deleteTeacherAttendanceForSchedules($teacherScheduleIds);
        AttendanceSession::whereIn('id', $sessionIds)->delete();

        return $deleted;
    }

    private function deleteTeacherAttendanceForSchedules($scheduleIds): void
    {
        $scheduleIds = collect($scheduleIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($scheduleIds->isEmpty()) {
            return;
        }

        TeacherAttendanceSession::whereIn('schedule_id', $scheduleIds)->delete();
        TeacherSchedule::whereIn('id', $scheduleIds)->delete();
    }

    private function deleteTeacherAttendanceForCourseSession(AttendanceSession $session): void
    {
        $scheduleIds = TeacherSchedule::where('source_attendance_session_id', $session->id)->pluck('id');
        $this->deleteTeacherAttendanceForSchedules($scheduleIds);
    }

    public function updateSemesterScore(Request $request, $assignmentId)
    {
        $request->validate([
            'admin_score' => 'nullable|numeric|min:0|max:100',
            'grading_status' => 'nullable|string|in:pending,reviewed,finalized',
            'grading_notes' => 'nullable|string'
        ]);

        $assignment = SemesterAssignment::findOrFail($assignmentId);

        $assignment->update($request->only(['admin_score', 'grading_status', 'grading_notes']));

        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "semester_assignment#{$assignmentId}.score_updated"
        ]);

        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    public function getGradingPreview($assignmentId)
    {
        $assignment = SemesterAssignment::with(['classRoom.subject', 'classRoom.groups'])->findOrFail($assignmentId);
        return response()->json($this->getGradingPreviewData($assignment));
    }

    public function getGradingPreviewData($assignment)
    {
        $class = $assignment->classRoom;
        $students = $class->all_students;
        $sessions = \App\Models\AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int) $assignment->semester)
            ->get();
        $totalSessions = $sessions->count();
        $attendanceScores = app(SemesterAttendanceScoreService::class);

        $studentStats = $students->map(function ($student) use ($sessions, $assignment, $attendanceScores) {
            $attendanceResult = $attendanceScores->calculate($student->id, $sessions);
            $saved = \DB::table('semester_assignment_scores')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            $midterm = $saved->midterm_score ?? 0;
            $asgn = $saved->assignment_score ?? 0;
            $final = $saved->final_score ?? 0;

            return [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'code' => $student->student_code,
                'att_score' => $attendanceResult['score'],
                'attended' => $attendanceResult['attended_sessions'],
                'permission_sessions' => $attendanceResult['permission_sessions'],
                'permission_absence_units' => $attendanceResult['permission_absence_units'],
                'midterm' => $midterm,
                'assignment' => $asgn,
                'final' => $final,
                'total' => round($attendanceResult['score'] + $midterm + $asgn + $final, 2)
            ];
        });

        return [
            'assignment' => $assignment,
            'students' => $studentStats,
            'total_sessions' => $totalSessions
        ];
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
            'scores.*.notes' => 'nullable|string'
        ]);

        $assignment = SemesterAssignment::findOrFail($assignmentId);
        $sessions = AttendanceSession::where('class_id', $assignment->class_id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int) $assignment->semester)
            ->get();
        $attendanceScores = app(SemesterAttendanceScoreService::class);

        foreach ($request->scores as $s) {
            $attendanceScore = $attendanceScores->calculate((int) $s['student_id'], $sessions)['score'];
            $total = $attendanceScore + ($s['midterm_score'] ?? 0) + ($s['assignment_score'] ?? 0) + ($s['final_score'] ?? 0);

            \DB::table('semester_assignment_scores')->updateOrInsert(
                ['assignment_id' => $assignmentId, 'student_id' => $s['student_id']],
                [
                    'attendance_score' => $attendanceScore,
                    'midterm_score' => $s['midterm_score'] ?? 0,
                    'assignment_score' => $s['assignment_score'] ?? 0,
                    'final_score' => $s['final_score'] ?? 0,
                    'score' => $total,
                    'notes' => $s['notes'] ?? '',
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function generateSemesterReport($assignmentId)
    {
        $assignment = SemesterAssignment::with([
            'classRoom.subject.department',
            'classRoom.teacher.user',
            'classRoom.groups.major'
        ])->findOrFail($assignmentId);

        $class = $assignment->classRoom;
        $subject = $class->subject;
        $department = $subject->department->name ?? 'Academic Dept';
        $major = $class->groups->first()->major->name ?? 'General';

        $students = $class->all_students;
        $sessions = AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int) $assignment->semester)
            ->get();
        $totalSessions = $sessions->count();
        $attendanceScores = app(SemesterAttendanceScoreService::class);

        $data = $students->map(function ($student) use ($sessions, $assignment, $attendanceScores) {
            $attendanceResult = $attendanceScores->calculate($student->id, $sessions);
            $savedScore = \DB::table('semester_assignment_scores')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            $midterm = $savedScore->midterm_score ?? 0;
            $assignment_score = $savedScore->assignment_score ?? 0;
            $final = $savedScore->final_score ?? 0;

            return [
                'name' => $student->user->name ?? 'Unknown',
                'code' => $student->student_code,
                'rate' => $attendanceResult['rate'],
                'att_score' => $attendanceResult['score'],
                'permission_sessions' => $attendanceResult['permission_sessions'],
                'permission_absence_units' => $attendanceResult['permission_absence_units'],
                'midterm' => $midterm,
                'assignment' => $assignment_score,
                'final' => $final,
                'total' => round($attendanceResult['score'] + $midterm + $assignment_score + $final, 2)
            ];
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.semester_report', [
            'assignment' => $assignment,
            'class' => $class,
            'department' => $department,
            'major' => $major,
            'data' => $data,
            'stats' => [
                'avg_rate' => $data->avg('rate'),
                'avg_total' => $data->avg('total')
            ]
        ]);

        $cleanSubject = str_replace(' ', '_', $subject->name ?? 'Class');
        $fileName = "ACADEMIC_REPORT_{$cleanSubject}_{$assignment->academic_year}_S{$assignment->semester}.pdf";

        return $pdf->download($fileName);
    }

    public function generateSemesterCSV($assignmentId)
    {
        $assignment = SemesterAssignment::with(['classRoom.subject', 'classRoom.groups'])->findOrFail($assignmentId);
        $class = $assignment->classRoom;

        $fileName = 'semester_report_' . $class->id . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $students = $class->all_students;
        $sessions = \App\Models\AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $assignment->academic_year)
            ->where('semester', (int) $assignment->semester)
            ->get();

        $columns = ['Student ID', 'Name', 'Group', 'Attended', 'Permission', 'Total Sessions', 'Rate %', 'Score'];

        $callback = function () use ($students, $sessions, $columns, $assignment) {
            $attendanceScores = app(SemesterAttendanceScoreService::class);
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($students as $student) {
                $attendanceResult = $attendanceScores->calculate($student->id, $sessions);

                fputcsv($file, [
                    $student->student_code,
                    $student->user->name ?? 'Unknown',
                    $student->group->name ?? 'N/A',
                    $attendanceResult['attended_sessions'],
                    $attendanceResult['permission_sessions'],
                    $sessions->count(),
                    $attendanceResult['rate'] . '%',
                    $assignment->admin_score // Course overall score
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function listSessions($classId)
    {
        $sessions = AttendanceSession::with(['classRoom.subject', 'classRoom.groups'])
            ->where('class_id', $classId)
            ->orderBy('start_time', 'asc')
            ->get();

        $formatted = $sessions->map(function ($s) {
            $class = $s->classRoom;
            $subject = $class ? $class->subject : null;

            return [
                'id' => $s->id,
                'class_id' => $s->class_id,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'status' => $s->status,
                'room' => $class->room_number ?? 'TBD',
                'subject' => [
                    'name' => $subject->name ?? 'N/A',
                    'code' => $subject->code ?? 'N/A',
                ],
                'presence_count' => Attendance::where('session_id', $s->id)->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count(),
                'total_students_count' => $class ? $class->all_students->count() : 0,
            ];
        });

        return response()->json($formatted);
    }

    public function updateStatus(Request $request, $sessionId)
    {
        $request->validate([
            'status' => 'required|in:active,scheduled,completed,passed,skipped',
            'reschedule' => 'sometimes|boolean'
        ]);

        $session = AttendanceSession::findOrFail($sessionId);

        if ($request->status === 'skipped' && $request->reschedule) {
            $nextSlot = $this->calculateNextSlotData($session);
            if ($nextSlot) {
                $this->deleteTeacherAttendanceForCourseSession($session);
                $session->update([
                    'start_time' => $nextSlot['start_time'],
                    'end_time' => $nextSlot['end_time'],
                    'checkin_open_time' => $nextSlot['checkin_open_time'],
                    'checkin_close_time' => $nextSlot['checkin_close_time'],
                    'status' => 'scheduled' // Re-schedule it to the end
                ]);
                app(\App\Services\TeacherAttendanceService::class)->syncFromStudentAttendanceSessions($request->user()?->id);

                ActivityLog::create([
                    'action' => 'UPDATE',
                    'target' => "session#{$session->id}.move_to_end"
                ]);

                return response()->json(['success' => true, 'message' => 'Session moved to end of semester', 'session' => $session]);
            } else {
                return response()->json(['success' => false, 'error' => 'Could not find a future slot.'], 400);
            }
        }

        $session->update(['status' => $request->status]);
        if ($request->status === 'skipped') {
            $this->deleteTeacherAttendanceForCourseSession($session);
        }

        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "session#{$session->id}.status_{$request->status}"
        ]);

        if ($request->status === 'completed') {
            app(\App\Services\TelegramService::class)->checkAbsenceThresholds($session->class_id);
        }

        return response()->json(['success' => true, 'session' => $session]);
    }

    public function getNextAvailableSlot($sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $nextSlot = $this->calculateNextSlotData($session);
        if ($nextSlot) {
            return response()->json([
                'success' => true,
                'date' => $nextSlot['start_time']->format('Y-m-d'),
                'time' => $nextSlot['start_time']->format('H:i'),
                'start_time' => $nextSlot['start_time']->toDateTimeString()
            ]);
        }
        return response()->json(['success' => false, 'error' => 'No more slots available.'], 404);
    }

    private function calculateNextSlotData($session)
    {
        $class = $session->classRoom;
        if (!$class)
            return null;

        // Find the last session for this class/semester
        $lastSession = AttendanceSession::where('class_id', $class->id)
            ->where('academic_year', $session->academic_year)
            ->where('semester', $session->semester)
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$lastSession)
            return null;

        list($allowedDays, $timeSlots) = $this->parseSchedule($class->schedule);
        if (empty($allowedDays) || empty($timeSlots))
            return null;

        $currentDate = \Carbon\Carbon::parse($lastSession->start_time)->startOfDay();

        // Find which slot the last session was
        $lastSlotIndex = -1;
        $lastStartTimeStr = \Carbon\Carbon::parse($lastSession->start_time)->format('H:i');
        foreach ($timeSlots as $idx => $slot) {
            if ($slot['start'] === $lastStartTimeStr) {
                $lastSlotIndex = $idx;
                break;
            }
        }

        // Determine next slot
        $nextSlotIndex = $lastSlotIndex + 1;
        if ($nextSlotIndex >= count($timeSlots)) {
            $nextSlotIndex = 0;
            $currentDate->addDay();
        }

        // Find next available day
        $maxIter = 60; // Safety (increased to handle longer breaks)
        while ($maxIter > 0) {
            $maxIter--;
            if (in_array($currentDate->dayOfWeek, $allowedDays)) {
                // Check if in holiday
                $assignment = SemesterAssignment::where('class_id', $class->id)
                    ->where('academic_year', $session->academic_year)
                    ->where('semester', $session->semester)
                    ->first();

                $inHoliday = false;
                if ($assignment && $assignment->holiday_start && $assignment->holiday_end) {
                    $inHoliday = $currentDate->between($assignment->holiday_start, $assignment->holiday_end);
                }

                if (!$inHoliday) {
                    $slot = $timeSlots[$nextSlotIndex];
                    $sTime = $currentDate->copy()->setTimeFromTimeString($slot['start']);
                    $eTime = $currentDate->copy()->setTimeFromTimeString($slot['end']);

                    return [
                        'start_time' => $sTime,
                        'end_time' => $eTime,
                        'checkin_open_time' => $sTime->copy()->subMinutes(20),
                        'checkin_close_time' => $sTime->copy()->addMinutes(20),
                    ];
                }
            }
            $currentDate->addDay();
            $nextSlotIndex = 0; // After moving to a new day, start from first slot
        }

        return null;
    }

    public function listSessionAttendance($sessionId)
    {
        $session = AttendanceSession::with('classRoom.subject')->findOrFail($sessionId);
        $attendances = Attendance::where('session_id', $sessionId)->get()->keyBy('student_id');

        $allStudents = collect();
        if ($session->classRoom) {
            $groupIds = $session->classRoom->groups->pluck('id');
            $allStudents = Student::with('user')->whereIn('group_id', $groupIds)->get();
        }

        // Fetch active permissions for the session date
        $sessionDate = \Carbon\Carbon::parse($session->start_time)->toDateString();
        $permissions = \App\Models\StudentPermission::where('start_date', '<=', $sessionDate)
            ->where('end_date', '>=', $sessionDate)
            ->whereIn('student_id', $allStudents->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $excusedCount = 0;
        $rows = $allStudents->map(function ($student) use ($attendances, $permissions, &$excusedCount) {
            $att = $attendances->get($student->id);
            $perm = $permissions->get($student->id);

            $status = 'ABSENT';
            if ($att) {
                $status = strtoupper($att->status);
            } elseif ($perm) {
                $status = 'EXCUSED';
                $excusedCount++;
            }

            return [
                'id' => $student->id,
                'attendance_id' => $att?->id,
                'name' => $student->user->name ?? 'Unknown Student',
                'student_code' => $student->student_code,
                'status' => $status,
                'permission_reason' => $perm ? $perm->reason : null,
                'permission_type' => $perm ? $perm->type : null,
                'check_in_time' => $att && $att->scan_time ? \Carbon\Carbon::parse($att->scan_time)->format('H:i') : '—',
                'method' => $att ? strtoupper($att->method) : '—',
            ];
        });

        $presentCount = $attendances->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();

        return response()->json([
            'success' => true,
            'session_name' => $session->classRoom->subject->name ?? 'N/A',
            'present_count' => $presentCount,
            'excused_count' => $excusedCount,
            'total_count' => $allStudents->count(),
            'data' => $rows
        ]);
    }

    public function updateSession(Request $request, $sessionId)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'sometimes|string'
        ]);

        $session = AttendanceSession::findOrFail($sessionId);

        $data = $request->only(['start_time', 'end_time', 'status']);
        $this->deleteTeacherAttendanceForCourseSession($session);

        // Auto-update checkin windows based on new start_time
        $sTime = \Carbon\Carbon::parse($request->start_time);
        $data['checkin_open_time'] = $sTime->copy()->subMinutes(20);
        $data['checkin_close_time'] = $sTime->copy()->addMinutes(20);

        $session->update($data);
        app(\App\Services\TeacherAttendanceService::class)->syncFromStudentAttendanceSessions($request->user()?->id);

        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "session#{$sessionId}.reschedule_man"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session updated successfully',
            'session' => $session
        ]);
    }

    public function globalSkip(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reschedule' => 'sometimes|boolean'
        ]);

        $sessions = AttendanceSession::whereBetween('start_time', [
            \Carbon\Carbon::parse($request->start_time),
            \Carbon\Carbon::parse($request->end_time)
        ])
            ->where('status', '!=', 'completed')
            ->get();

        $affected = 0;
        foreach ($sessions as $session) {
            if ($request->reschedule) {
                $nextSlot = $this->calculateNextSlotData($session);
                if ($nextSlot) {
                    $this->deleteTeacherAttendanceForCourseSession($session);
                    $session->update([
                        'start_time' => $nextSlot['start_time'],
                        'end_time' => $nextSlot['end_time'],
                        'checkin_open_time' => $nextSlot['checkin_open_time'],
                        'checkin_close_time' => $nextSlot['checkin_close_time'],
                        'status' => 'scheduled'
                    ]);
                    $affected++;

                    ActivityLog::create([
                        'action' => 'UPDATE',
                        'target' => "session#{$session->id}.auto_move"
                    ]);
                }
            } else {
                $session->update(['status' => 'skipped']);
                $this->deleteTeacherAttendanceForCourseSession($session);
                $affected++;

                ActivityLog::create([
                    'action' => 'UPDATE',
                    'target' => "session#{$session->id}.status_skipped"
                ]);
            }
        }

        if ($affected > 0 && $request->reschedule) {
            app(\App\Services\TeacherAttendanceService::class)->syncFromStudentAttendanceSessions($request->user()?->id);
        }

        if ($affected > 0) {
            ActivityLog::create([
                'action' => 'UPDATE',
                'target' => "global.batch_skip#{$affected}"
            ]);
        }

        $msg = $request->reschedule
            ? "Successfully moved $affected sessions to the end of the semester."
            : "Successfully skipped $affected sessions across all subjects.";

        return response()->json([
            'success' => true,
            'message' => $msg,
            'affected_count' => $affected
        ]);
    }

    public function skipTodayAndShift(Request $request)
    {
        $today = now()->toDateString();
        $dayOfWeek = now()->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $sqlDay = $dayOfWeek + 1; // MySQL DAYOFWEEK is 1-indexed (1=Sun, 2=Mon...)

        // 1. Identify all sessions scheduled for today
        $sessionsToday = AttendanceSession::whereDate('start_time', $today)
            ->where('status', 'scheduled')
            ->get();

        if ($sessionsToday->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No scheduled sessions found for today.']);
        }

        \DB::beginTransaction();
        try {
            foreach ($sessionsToday as $session) {
                $classId = $session->class_id;

                // 2. We shift THIS session and ALL FUTURE sessions for this class
                // that occur on the SAME DAY OF WEEK.
                $futureAndToday = AttendanceSession::where('class_id', $classId)
                    ->where('start_time', '>=', $session->start_time)
                    ->where('status', 'scheduled')
                    ->whereRaw("DAYOFWEEK(start_time) = ?", [$sqlDay])
                    ->orderBy('start_time', 'desc') // Shift from the furthest future backwards to avoid any logic overlaps
                    ->get();

                foreach ($futureAndToday as $fs) {
                    $newStart = $fs->start_time->addWeek();
                    $newEnd = $fs->end_time->addWeek();

                    $this->deleteTeacherAttendanceForCourseSession($fs);
                    $fs->update([
                        'start_time' => $newStart,
                        'end_time' => $newEnd,
                        'checkin_open_time' => $newStart->copy()->subMinutes(20),
                        'checkin_close_time' => $newStart->copy()->addMinutes(20),
                    ]);
                }
            }

            app(\App\Services\TeacherAttendanceService::class)->syncFromStudentAttendanceSessions($request->user()?->id);

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Today\'s sessions shifted to next week successfully.']);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function listStudents()
    {
        return response()->json(Student::with(['user', 'classRoom.subject'])->get());
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string',
            'student_code' => 'required|unique:students,student_code',
            'group_id' => 'required|exists:class_groups,id',
            'major_id' => 'required|exists:majors,id',
        ]);

        DB::beginTransaction();
        try {
            $temporaryPassword = Str::password(16);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($temporaryPassword),
                'role' => 'student',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => $request->student_code,
                'group_id' => $request->group_id,
                'major_id' => $request->major_id,
                'status' => $request->status ?? 'active',
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'student' => $student->load('user', 'classRoom.subject'),
                'temporary_password' => $temporaryPassword,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStudent(Request $request, $studentId)
    {
        $student = Student::with('user')->findOrFail($studentId);

        $request->validate([
            'name' => 'sometimes|required',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $student->user_id,
            'phone' => 'sometimes|nullable|string',
            'student_code' => 'sometimes|required|unique:students,student_code,' . $student->id,
            'group_id' => 'sometimes|required|exists:class_groups,id',
            'major_id' => 'sometimes|required|exists:majors,id',
            'status' => 'sometimes|required|in:active,suspended,graduated'
        ]);

        DB::beginTransaction();
        try {
            $userUpdates = $request->only(['name', 'email', 'phone']);
            if (!empty($userUpdates)) {
                $student->user->update($userUpdates);
            }

            $studentUpdates = $request->only(['student_code', 'group_id', 'major_id', 'status', 'class_id']);

            if ($request->has('class_id') && $request->class_id) {
                $class = \App\Models\ClassRoom::with('groups')->find($request->class_id);
                if ($class && $class->groups->isNotEmpty()) {
                    // Try to find a group associated with this class that matches the student's major
                    $matchingGroup = $class->groups->where('major_id', $student->major_id)->first();

                    if ($matchingGroup) {
                        $studentUpdates['group_id'] = $matchingGroup->id;
                    } else {
                        // If no exact major match, use the first group associated with the class
                        // This ensures the student is technically "in" the class's scope
                        $studentUpdates['group_id'] = $class->groups->first()->id;
                    }
                }
            }

            $student->update($studentUpdates);

            DB::commit();
            return response()->json(['success' => true, 'student' => $student->load('user', 'classRoom.subject')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteStudent($studentId)
    {
        $student = Student::with('user')->findOrFail($studentId);
        $user = $student->user;

        DB::beginTransaction();
        try {
            $student->delete();
            $user->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteStudents(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $students = Student::with('user')
            ->whereIn('id', $request->student_ids)
            ->get();

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $user = $student->user;
                $student->delete();
                if ($user) {
                    $user->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'deleted_count' => $students->count(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function listStudentAttendance($studentId)
    {
        $student = Student::with(['user', 'major.department', 'group.major.department'])->findOrFail($studentId);

        // Fetch the most recent 10 sessions for the student's group
        $sessions = AttendanceSession::whereHas('classRoom.groups', function ($q) use ($student) {
            $q->where('class_groups.id', $student->group_id);
        })
            ->where('start_time', '<=', now())
            ->latest('start_time')
            ->limit(10)
            ->get();

        $history = $sessions->map(function ($session) use ($studentId) {
            $att = Attendance::where('student_id', $studentId)
                ->where('session_id', $session->id)
                ->first();

            // Check for active permission on this session's date
            $sessionDate = \Carbon\Carbon::parse($session->start_time)->toDateString();
            $perm = \App\Models\StudentPermission::where('student_id', $studentId)
                ->where('start_date', '<=', $sessionDate)
                ->where('end_date', '>=', $sessionDate)
                ->first();

            $status = 'ABSENT';
            if ($att) {
                $status = strtoupper($att->status);
            } elseif ($perm) {
                $status = 'EXCUSED';
            }

            return [
                'id' => $session->id,
                'subject' => $session->classRoom->subject->name ?? 'N/A',
                'date' => \Carbon\Carbon::parse($session->start_time)->format('M d, Y'),
                'time' => $att && $att->scan_time ? \Carbon\Carbon::parse($att->scan_time)->format('H:i') : '—',
                'status' => $status,
                'method' => $att ? strtoupper($att->method ?? 'QR') : '—',
                'permission_reason' => $perm ? $perm->reason : null,
            ];
        });

        $presentCount = Attendance::where('student_id', $studentId)->whereIn('status', ['present', 'late', 'PRESENT', 'LATE'])->count();
        $absentCount = $sessions->count() - $presentCount;
        $totalSessions = AttendanceSession::whereHas('classRoom.groups', function ($q) use ($student) {
            $q->where('class_groups.id', $student->group_id);
        })->where('start_time', '<=', now())->count();
        $rate = $totalSessions === 0 ? 0 : round(($presentCount / $totalSessions) * 100);

        // Explicitly check for Major model instance to avoid conflict with legacy 'major' column
        $majorObj = ($student->major instanceof \App\Models\Major) ? $student->major : ($student->group->major ?? null);
        $deptName = ($majorObj instanceof \App\Models\Major) ? ($majorObj->department->name ?? 'N/A') : 'N/A';
        $majorName = ($majorObj instanceof \App\Models\Major) ? ($majorObj->name ?? 'N/A') : 'N/A';
        $yearLevel = $student->group->year_level ?? 1;

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'N/A',
                'email' => $student->user->email ?? 'N/A',
                'phone' => $student->user->phone ?? 'N/A',
                'student_code' => $student->student_code,
                'major' => $majorName,
                'department' => $deptName,
                'year_level' => $yearLevel,
                'status' => $student->status ?? 'active',
                'joined_at' => $student->created_at->format('M Y'),
                'attendance_rate' => $rate
            ],
            'summary' => [
                'present' => $presentCount,
                'absent' => $totalSessions - $presentCount,
            ],
            'history' => $history
        ]);
    }

    public function listSubjects()
    {
        return response()->json(Subject::with('department')->get());
    }

    public function storeSubject(Request $request)
    {
        $subject = Subject::create($request->all());
        return response()->json(['success' => true, 'subject' => $subject->load('department')]);
    }

    public function updateSubject(Request $request, $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $subject->update($request->all());
        return response()->json(['success' => true, 'subject' => $subject->load('department')]);
    }

    public function deleteSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        $subject->delete();
        return response()->json(['success' => true]);
    }

    public function listDepartments()
    {
        return response()->json(Department::withCount(['teachers', 'subjects'])->get());
    }

    public function showDepartment($deptId)
    {
        $dept = Department::with([
            'teachers' => function ($query) {
                $query->with('user')->withCount('classes');
            },
            'subjects' => function ($query) {
                $query->withCount('classes');
            }
        ])->findOrFail($deptId);

        return response()->json([
            'success' => true,
            'department' => $dept
        ]);
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:departments,name',
            'code' => 'required|string|unique:departments,code'
        ]);

        $dept = Department::create($request->all());

        ActivityLog::create([
            'action' => 'CREATE',
            'target' => "departments#{$dept->id}"
        ]);

        return response()->json([
            'success' => true,
            'department' => $dept->loadCount(['teachers', 'subjects'])
        ]);
    }

    public function updateDepartment(Request $request, $deptId)
    {
        $dept = Department::findOrFail($deptId);

        $request->validate([
            'name' => 'required|string|unique:departments,name,' . $deptId,
            'code' => 'required|string|unique:departments,code,' . $deptId
        ]);

        $dept->update($request->all());

        ActivityLog::create([
            'action' => 'UPDATE',
            'target' => "departments#{$deptId}"
        ]);

        return response()->json([
            'success' => true,
            'department' => $dept->loadCount(['teachers', 'subjects'])
        ]);
    }

    public function deleteDepartment($deptId)
    {
        $dept = Department::findOrFail($deptId);
        $dept->delete();

        ActivityLog::create([
            'action' => 'DELETE',
            'target' => "departments#{$deptId}"
        ]);

        return response()->json(['success' => true]);
    }

    public function storeInstructor(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'nullable|min:6',
        ]);

        DB::beginTransaction();
        try {
            $temporaryPassword = $request->password ?: Str::password(16);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($temporaryPassword),
                'role' => 'teacher',
            ]);

            $teacherData = [
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'specialization' => $request->specialization,
                'status' => $request->status ?? 'active',
            ];

            if (Teacher::hasTeacherCodeColumn()) {
                $teacherData['teacher_code'] = Teacher::generateTeacherCode();
            }

            $teacher = Teacher::create($teacherData);

            DB::commit();
            return response()->json([
                'success' => true,
                'teacher' => $teacher->load('user'),
                'temporary_password' => $request->password ? null : $temporaryPassword,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateInstructor(Request $request, $teacherId)
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        DB::beginTransaction();
        try {
            $teacher->user->update($request->only(['name', 'email', 'phone']));
            if ($request->password) {
                $teacher->user->update(['password' => Hash::make($request->password)]);
            }

            $teacher->update($request->only(['department_id', 'specialization', 'status']));

            DB::commit();
            return response()->json(['success' => true, 'teacher' => $teacher->load('user')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteInstructor($teacherId)
    {
        $teacher = Teacher::with('user')->withCount('classes')->findOrFail($teacherId);

        if ($teacher->classes_count > 0) {
            return response()->json([
                'success' => false,
                'error' => 'This instructor cannot be deleted while assigned classes exist.',
            ], 422);
        }

        $user = $teacher->user;

        DB::beginTransaction();
        try {
            $teacher->delete();
            if ($user) {
                $user->delete();
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteInstructors(Request $request)
    {
        $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,id',
        ]);

        $teachers = Teacher::with('user')
            ->withCount('classes')
            ->whereIn('id', $request->teacher_ids)
            ->get();

        $blocked = $teachers
            ->filter(fn ($teacher) => $teacher->classes_count > 0)
            ->map(fn ($teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->user->name ?? 'Instructor #' . $teacher->id,
                'classes_count' => $teacher->classes_count,
            ])
            ->values();

        if ($blocked->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'Some instructors cannot be deleted because they have assigned classes.',
                'blocked' => $blocked,
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($teachers as $teacher) {
                $user = $teacher->user;
                $teacher->delete();
                if ($user) {
                    $user->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'deleted_count' => $teachers->count(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function updateUserAccount(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $payload = [];
        if ($request->role) {
            $payload['role'] = $request->role;
        }
        if ($request->password) {
            $payload['password'] = Hash::make($request->password);
        }

        if (empty($payload)) {
            return response()->json(['error' => 'No updates provided.'], 400);
        }

        $user->update($payload);
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function exportStudents()
    {
        $students = Student::with(['user', 'group', 'major'])->get();
        $fileName = 'student_registry_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Name', 'Email', 'Student Code', 'Major', 'Year', 'Group', 'Status'];

        $callback = function () use ($students, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($students as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->user->name ?? 'N/A',
                    $s->user->email ?? 'N/A',
                    $s->student_code ?? 'N/A',
                    $s->major->name ?? 'N/A',
                    $s->year_level ?? 'N/A',
                    $s->group->name ?? 'Unassigned',
                    $s->status ?? 'active',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importStudents(Request $request)
    {
        $request->validate(['file' => 'required|file']);

        if (function_exists('set_time_limit')) {
            set_time_limit(120);
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), "r");
        $headerRow = fgetcsv($handle, 0, ",");

        if (!$headerRow) {
            return response()->json(['error' => 'Empty CSV file.'], 400);
        }

        // Normalize headers to identify column indices dynamically
        $headers = array_map(function ($h) {
            return strtolower(trim($h));
        }, $headerRow);

        // Define default fallback indices
        $nameIdx = 0;
        $codeIdx = 1;
        $emailIdx = 2;
        $phoneIdx = 3;
        $groupIdIdx = 4;
        $majorIdIdx = 5;
        $statusIdx = 6;

        // Map column indices dynamically based on headers
        if (in_array('name', $headers) || in_array('student_code', $headers)) {
            $nameIdx = array_search('name', $headers) !== false ? array_search('name', $headers) : 0;
            $codeIdx = array_search('student_code', $headers) !== false ? array_search('student_code', $headers) : 1;
            $emailIdx = array_search('email', $headers) !== false ? array_search('email', $headers) : 2;
            $phoneIdx = array_search('phone', $headers) !== false ? array_search('phone', $headers) : 3;
            $groupIdIdx = array_search('group_id', $headers) !== false ? array_search('group_id', $headers) : 4;
            $majorIdIdx = array_search('major_id', $headers) !== false ? array_search('major_id', $headers) : 5;
            $statusIdx = array_search('status', $headers) !== false ? array_search('status', $headers) : 6;
        }

        try {
            $now = now();
            $rows = [];
            $skipped = [];
            $seenEmails = [];
            $seenCodes = [];
            $rowNumber = 1;

            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $rowNumber++;

                if (count($data) < 2) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Not enough columns.'];
                    continue;
                }

                $name = trim($data[$nameIdx] ?? '');
                $studentCode = trim($data[$codeIdx] ?? '');
                $email = strtolower(trim($data[$emailIdx] ?? ''));
                $phone = trim($data[$phoneIdx] ?? '');
                $groupId = trim($data[$groupIdIdx] ?? '');
                $majorId = trim($data[$majorIdIdx] ?? '');
                $status = strtolower(trim($data[$statusIdx] ?? 'active'));

                if (empty($name) || empty($studentCode)) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => 'Name and student_code are required.'];
                    continue;
                }

                if (empty($email)) {
                    $email = strtolower(preg_replace('/[^a-z0-9]+/i', '', $name)) . '.' . strtolower($studentCode) . '@university.edu';
                }

                if (isset($seenEmails[$email])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Duplicate email in file: {$email}."];
                    continue;
                }

                if (isset($seenCodes[$studentCode])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Duplicate student code in file: {$studentCode}."];
                    continue;
                }

                $seenEmails[$email] = true;
                $seenCodes[$studentCode] = true;

                $rows[] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone !== '' ? $phone : null,
                    'student_code' => $studentCode,
                    'group_id' => is_numeric($groupId) ? (int) $groupId : null,
                    'major_id' => is_numeric($majorId) ? (int) $majorId : null,
                    'status' => $status !== '' ? $status : 'active',
                ];
            }

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid student rows found.',
                    'skipped' => $skipped,
                ], 422);
            }

            $emails = array_column($rows, 'email');
            $studentCodes = array_column($rows, 'student_code');
            $groupIds = array_values(array_unique(array_filter(array_column($rows, 'group_id'))));
            $majorIds = array_values(array_unique(array_filter(array_column($rows, 'major_id'))));

            $existingEmails = User::whereIn('email', $emails)->pluck('email')->flip();
            $existingCodes = Student::whereIn('student_code', $studentCodes)->pluck('student_code')->flip();
            $validGroups = empty($groupIds) ? collect() : ClassGroup::whereIn('id', $groupIds)->get()->keyBy('id');
            $validGroupIds = $validGroups->keys()->flip();
            $validMajorIds = empty($majorIds) ? collect() : Major::whereIn('id', $majorIds)->pluck('id')->flip();

            $validRows = [];
            foreach ($rows as $index => $row) {
                $sourceRow = $index + 2;

                if ($existingEmails->has($row['email'])) {
                    $skipped[] = ['row' => $sourceRow, 'reason' => "Email already exists: {$row['email']}."];
                    continue;
                }

                if ($existingCodes->has($row['student_code'])) {
                    $skipped[] = ['row' => $sourceRow, 'reason' => "Student code already exists: {$row['student_code']}."];
                    continue;
                }

                if ($row['group_id'] && !$validGroupIds->has($row['group_id'])) {
                    $skipped[] = ['row' => $sourceRow, 'reason' => "Invalid group_id: {$row['group_id']}."];
                    continue;
                }

                if ($row['major_id'] && !$validMajorIds->has($row['major_id'])) {
                    $skipped[] = ['row' => $sourceRow, 'reason' => "Invalid major_id: {$row['major_id']}."];
                    continue;
                }

                if (!$row['major_id'] && $row['group_id'] && $validGroups->has($row['group_id'])) {
                    $row['major_id'] = $validGroups[$row['group_id']]->major_id;
                }

                $validRows[] = $row;
            }

            if (empty($validRows)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No new students imported. All rows were skipped.',
                    'skipped' => $skipped,
                ], 422);
            }

            $temporaryPasswordHash = Hash::make(Str::password(16));

            DB::beginTransaction();

            User::insert(array_map(fn ($row) => [
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'password' => $temporaryPasswordHash,
                'role' => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ], $validRows));

            $usersByEmail = User::whereIn('email', array_column($validRows, 'email'))
                ->pluck('id', 'email');

            Student::insert(array_map(fn ($row) => [
                'user_id' => $usersByEmail[$row['email']],
                'student_code' => $row['student_code'],
                'group_id' => $row['group_id'],
                'major_id' => $row['major_id'],
                'status' => $row['status'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $validRows));

            ActivityLog::create([
                'action' => 'IMPORT',
                'target' => 'students.bulk_import.' . count($validRows),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'imported_count' => count($validRows),
                'skipped_count' => count($skipped),
                'skipped' => array_slice($skipped, 0, 25),
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        } finally {
            fclose($handle);
        }
    }

    public function exportClasses()
    {
        $classes = ClassRoom::with(['subject', 'teacher.user'])->get();
        $fileName = 'academic_catalog_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Subject', 'Instructor', 'Room', 'Schedule', 'Status'];

        $callback = function () use ($classes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($classes as $class) {
                fputcsv($file, [
                    $class->id,
                    $class->subject->name ?? 'N/A',
                    $class->teacher->user->name ?? 'N/A',
                    $class->room_number ?? 'N/A',
                    $class->schedule ?? 'N/A',
                    $class->status ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateAcademicCalendar(Request $request)
    {
        $request->validate([
            'academic_year' => 'required',
            'semester' => 'required|in:1,2',
            'start_date' => 'required|date',
            'sessions_count' => 'integer|min:1|max:60'
        ]);

        $year = $request->academic_year;
        $semester = $request->semester;
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $count = $request->sessions_count ?? 30;

        $classes = \App\Models\ClassRoom::all();
        $generated = 0;

        \DB::beginTransaction();
        try {
            foreach ($classes as $class) {
                // Update class metadata
                $class->update(['academic_year' => $year, 'semester' => $semester]);

                // Clear existing sessions for this class and semester to avoid duplicates
                $sessionIds = \App\Models\AttendanceSession::where('class_id', $class->id)
                    ->where('semester', $semester)
                    ->where('academic_year', $year)
                    ->pluck('id');
                $this->deleteCourseSessionsWithTeacherAttendance($sessionIds);

                // Generate N sessions using the unified generator
                $sessionsCreated = $this->generateAcademicSessions(
                    $class,
                    $year,
                    $semester,
                    $startDate,
                    $count,
                    null,
                    null
                );

                $generated += $sessionsCreated;
            }

            \DB::commit();
            return response()->json(['success' => true, 'generated' => $generated]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // ─────────────────────────────────────────────────────
    // SEMESTER ASSIGNMENTS
    // ─────────────────────────────────────────────────────

    public function listSemesterAssignments($classId)
    {
        $assignments = SemesterAssignment::where('class_id', $classId)
            ->orderBy('academic_year', 'desc')
            ->orderBy('semester', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments->map(fn(SemesterAssignment $a) => $this->formatAssignment($a))
        ]);
    }

    public function storeSemesterAssignment(Request $request, $classId)
    {
        $request->validate([
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1,2',
            'start_date' => 'required|date',
            'holiday_start' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'sessions_count' => 'nullable|integer|min:1|max:100',
            'time_start' => 'nullable|string',
            'time_end' => 'nullable|string',
        ]);

        $class = ClassRoom::findOrFail($classId);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addMonths(4);
        $holidayStart = $request->holiday_start ? \Carbon\Carbon::parse($request->holiday_start) : null;
        $holidayEnd = $holidayStart ? $holidayStart->copy()->addWeeks(3) : null;

        $proposedSchedule = $class->schedule;
        if ($request->time_start && $request->time_end) {
            $daysPart = 'Mon/Wed/Fri';
            if ($class->schedule) {
                $parts = explode(' ', $class->schedule);
                if ($parts[0]) {
                    $daysPart = $parts[0];
                }
            }
            $proposedSchedule = "$daysPart ($request->time_start-$request->time_end)";
        }

        if ($conflict = $this->findTeacherScheduleConflict((int) $class->teacher_id, $proposedSchedule, (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->teacherScheduleConflictMessage($conflict),
            ], 422);
        }

        if ($conflict = $this->findClassScheduleConflict($proposedSchedule, $class->room_number, $this->classGroupIds($class), (int) $class->id)) {
            return response()->json([
                'success' => false,
                'error' => $this->classScheduleConflictMessage($conflict),
            ], 422);
        }

        // Determine status
        $now = now();
        $status = 'upcoming';
        if ($now->between($startDate, $endDate))
            $status = 'active';
        if ($now->gt($endDate))
            $status = 'completed';

        DB::beginTransaction();
        try {
            // 2. Upsert Semester Assignment Record
            $assignment = SemesterAssignment::updateOrCreate(
                [
                    'class_id' => $class->id,
                    'academic_year' => $request->academic_year,
                    'semester' => (int) $request->semester,
                ],
                [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'holiday_start' => $holidayStart?->toDateString(),
                    'holiday_end' => $holidayEnd?->toDateString(),
                    'status' => $status,
                    'notes' => $request->notes
                ]
            );

            // 1. Update Class Schedule if times provided
            if ($request->time_start && $request->time_end) {
                // Determine days (fallback to first space-separated part or Mon/Wed/Fri)
                $daysPart = "Mon/Wed/Fri";
                if ($class->schedule) {
                    $parts = explode(' ', $class->schedule);
                    if ($parts[0])
                        $daysPart = $parts[0];
                }
                $class->update([
                    'schedule' => "$daysPart ($request->time_start-$request->time_end)",
                    'semester' => (int) $request->semester,
                    'academic_year' => $request->academic_year
                ]);
            } else {
                $class->update([
                    'semester' => (int) $request->semester,
                    'academic_year' => $request->academic_year
                ]);
            }

            // 3. GENERATE SESSIONS
            $sessionsTarget = $request->sessions_count ?? 30;
            $schedStr = $class->schedule;
            if (!$schedStr)
                throw new \Exception("Class schedule not defined.");

            // Parse Days
            $daysMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 0];
            $allowedDays = [];

            // Check for explicit "weekday" or "mon-fri" keywords
            if (stripos($schedStr, 'mon-fri') !== false || stripos($schedStr, 'weekday') !== false) {
                $allowedDays = [1, 2, 3, 4, 5];
            } else if (stripos($schedStr, 'sat/sun') !== false || stripos($schedStr, 'weekend') !== false) {
                $allowedDays = [6, 0];
            } else {
                // Otherwise, check for individual days
                foreach ($daysMap as $dStr => $dNum) {
                    if (stripos($schedStr, $dStr) !== false)
                        $allowedDays[] = $dNum;
                }
            }
            if (empty($allowedDays))
                $allowedDays = [1, 2, 3, 4, 5];

            // GENERATE SESSIONS (Unified)
            $sessionsCreated = $this->generateAcademicSessions(
                $class,
                $request->academic_year,
                $request->semester,
                $startDate,
                $sessionsTarget,
                $holidayStart,
                $holidayEnd
            );

            DB::commit();
            return response()->json(['success' => true, 'data' => $this->formatAssignment($assignment)]);
        } catch (\Exception $e) {
            DB::rollback();
            if (str_contains($e->getMessage(), 'uniq_class_sem')) {
                return response()->json(['error' => 'This class already has a Semester ' . $request->semester . ' assignment for ' . $request->academic_year . '.'], 422);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSemesterAssignment(Request $request, $assignmentId)
    {
        $assignment = SemesterAssignment::findOrFail($assignmentId);
        $request->validate([
            'holiday_start' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:upcoming,active,completed',
            'academic_year' => 'nullable|string',
            'semester' => 'nullable|integer'
        ]);

        if ($request->filled('holiday_start')) {
            $holidayStart = \Carbon\Carbon::parse($request->holiday_start);
            $assignment->holiday_start = $holidayStart->toDateString();
            $assignment->holiday_end = $holidayStart->copy()->addWeeks(3)->toDateString();
        }
        if ($request->filled('notes'))
            $assignment->notes = $request->notes;
        if ($request->filled('status'))
            $assignment->status = $request->status;
        if ($request->filled('academic_year'))
            $assignment->academic_year = $request->academic_year;
        if ($request->filled('semester'))
            $assignment->semester = $request->semester;
        $assignment->save();

        // Sync with ClassRoom if status is active
        if ($assignment->status === 'active') {
            $assignment->classRoom->update([
                'semester' => $assignment->semester,
                'academic_year' => $assignment->academic_year
            ]);
        }

        return response()->json(['success' => true, 'data' => $this->formatAssignment($assignment)]);
    }

    public function deleteSemesterAssignment($assignmentId)
    {
        $assignment = SemesterAssignment::findOrFail($assignmentId);

        DB::beginTransaction();
        try {
            // Purge all generated sessions for this class and semester
            $sessionIds = \App\Models\AttendanceSession::where('class_id', $assignment->class_id)
                ->where('academic_year', $assignment->academic_year)
                ->where('semester', (int) $assignment->semester)
                ->pluck('id');
            $this->deleteCourseSessionsWithTeacherAttendance($sessionIds);

            $assignment->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatAssignment(SemesterAssignment $a): array
    {
        $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : null;
        return [
            'id' => $a->id,
            'class_id' => $a->class_id,
            'academic_year' => $a->academic_year,
            'semester' => $a->semester,
            'start_date' => $fmt($a->start_date),
            'end_date' => $fmt($a->end_date),
            'holiday_start' => $fmt($a->holiday_start),
            'holiday_end' => $fmt($a->holiday_end),
            'status' => $a->status,
            'notes' => $a->notes,
            'progress' => $a->progress,
            'active_days' => $a->active_days,
            'in_holiday' => $a->in_holiday,
            'admin_score' => $a->admin_score,
            'teacher_score' => $a->teacher_score,
            'grading_status' => $a->grading_status,
            'created_at' => $a->created_at?->format('Y-m-d'),
        ];
    }

    public function getUserRoles()
    {
        return response()->json([
            'success' => true,
            'roles' => ['admin', 'teacher', 'student']
        ]);
    }

    /**
     * Get global activity for system-wide notifications (for admin & monitoring)
     */
    public function getGlobalActivity(Request $request)
    {
        try {
            $limit = min(max((int) $request->query('limit', 10), 1), 30);
            $lastId = (int) $request->query('last_id', 0);
            $activity = collect();

            // 1. Currently active class sessions.
            $activeSessions = \App\Models\AttendanceSession::where('status', 'active')
                ->with('classRoom.subject')
                ->get()
                ->map(function ($session) {
                    $time = $session->updated_at ?? $session->start_time ?? now();

                    return [
                        "id" => $this->activityId($time, 10, $session->id),
                        "action" => "ACTIVE",
                        "target" => "Session " . ($session->classRoom->subject->name ?? 'Class') . " is active",
                        "name" => "Active Class",
                        "subject" => ($session->classRoom->subject->name ?? 'Class') . " in progress",
                        "time" => $this->activityTime($time),
                        "type" => "active_session"
                    ];
                });
            $activity = $activity->merge($activeSessions);

            // 2. New admin registrations (super_admin only).
            if ($request->user() && ($request->user()->role === 'super_admin' || $request->user()->isSuperAdmin())) {
                $pendingAdmins = \App\Models\User::where('role', 'admin')
                    ->where('is_approved', false)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($u) {
                        return [
                            "id" => $this->activityId($u->created_at, 20, $u->id),
                            "action" => "REGISTERED",
                            "target" => "New admin " . $u->name . " registered",
                            "name" => "Admin Register",
                            "subject" => $u->name . " is pending approval",
                            "time" => $this->activityTime($u->created_at),
                            "type" => "new_admin"
                        ];
                    });
                $activity = $activity->merge($pendingAdmins);
            }

            // 3. Teacher account requests and approval changes.
            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_registration_requests')) {
                $teacherRequests = \App\Models\TeacherRegistrationRequest::with(['user', 'department', 'approvedBy'])
                    ->orderByDesc('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(function ($teacherRequest) {
                        $time = $teacherRequest->approved_at ?? $teacherRequest->updated_at ?? $teacherRequest->created_at;
                        $teacherName = $teacherRequest->user?->name ?? 'Teacher';
                        $status = strtoupper((string) $teacherRequest->status);

                        return [
                            "id" => $this->activityId($time, 30, $teacherRequest->id),
                            "action" => "TEACHER_" . $status,
                            "target" => "{$teacherName} teacher request {$teacherRequest->status}",
                            "name" => "Teacher {$status}",
                            "subject" => "{$teacherName} · " . ($teacherRequest->department?->name ?? 'No department'),
                            "time" => $this->activityTime($time),
                            "type" => $teacherRequest->status === 'pending' ? "teacher_request" : "teacher_review"
                        ];
                    });
                $activity = $activity->merge($teacherRequests);
            }

            // 4. Teacher active/inactive account status changes.
            $teacherStatusEvents = \App\Models\Teacher::with(['user', 'department'])
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get()
                ->map(function ($teacher) {
                    $time = $teacher->updated_at ?? $teacher->created_at;
                    $status = strtoupper((string) $teacher->status);
                    $teacherName = $teacher->user?->name ?? 'Teacher';

                    return [
                        "id" => $this->activityId($time, 35, $teacher->id),
                        "action" => "TEACHER_STATUS_" . $status,
                        "target" => "{$teacherName} is {$teacher->status}",
                        "name" => "Teacher {$status}",
                        "subject" => "{$teacherName} · " . ($teacher->department?->name ?? 'No department'),
                        "time" => $this->activityTime($time),
                        "type" => $teacher->status === 'active' ? "teacher_active" : "teacher_review"
                    ];
                });
            $activity = $activity->merge($teacherStatusEvents);

            // 5. Student permission requests and review changes.
            if (\Illuminate\Support\Facades\Schema::hasTable('student_permissions')) {
                $permissionRequests = \App\Models\StudentPermission::withoutGlobalScopes()
                    ->with(['student.user', 'requestedByTeacher.user', 'attendanceSession.classRoom.subject'])
                    ->orderByDesc('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(function ($permission) {
                        $time = $permission->reviewed_at ?? $permission->approved_at ?? $permission->updated_at ?? $permission->created_at;
                        $studentName = $permission->student?->user?->name ?? 'Student';
                        $subject = $permission->attendanceSession?->classRoom?->subject?->name;
                        $requestedBy = $permission->requestedByTeacher?->user?->name;
                        $status = strtoupper((string) $permission->status);

                        return [
                            "id" => $this->activityId($time, 40, $permission->id),
                            "action" => "PERMISSION_" . $status,
                            "target" => "{$studentName} permission {$permission->status}",
                            "name" => "Permission {$status}",
                            "subject" => trim($studentName . ($subject ? " · {$subject}" : '') . ($requestedBy ? " · by {$requestedBy}" : '')),
                            "time" => $this->activityTime($time),
                            "type" => $permission->status === 'pending' ? "permission_request" : "permission_review"
                        ];
                    });
                $activity = $activity->merge($permissionRequests);
            }

            // 6. Teacher class-change and attendance-correction requests.
            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_class_change_requests')) {
                $classChangeRequests = \App\Models\TeacherClassChangeRequest::with(['teacher.user', 'schedule.subject'])
                    ->orderByDesc('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(function ($changeRequest) {
                        $time = $changeRequest->reviewed_at ?? $changeRequest->updated_at ?? $changeRequest->created_at;
                        $teacherName = $changeRequest->teacher?->user?->name ?? 'Teacher';
                        $status = strtoupper((string) $changeRequest->status);

                        return [
                            "id" => $this->activityId($time, 50, $changeRequest->id),
                            "action" => "CLASS_CHANGE_" . $status,
                            "target" => "{$teacherName} class change {$changeRequest->status}",
                            "name" => "Class Change {$status}",
                            "subject" => "{$teacherName} · " . ($changeRequest->schedule?->subject?->name ?? $changeRequest->request_type),
                            "time" => $this->activityTime($time),
                            "type" => $changeRequest->status === 'pending' ? "teacher_request" : "teacher_review"
                        ];
                    });
                $activity = $activity->merge($classChangeRequests);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_attendance_corrections')) {
                $correctionRequests = \App\Models\TeacherAttendanceCorrection::with(['teacher.user', 'schedule.subject'])
                    ->orderByDesc('updated_at')
                    ->limit(20)
                    ->get()
                    ->map(function ($correction) {
                        $time = $correction->reviewed_at ?? $correction->updated_at ?? $correction->created_at;
                        $teacherName = $correction->teacher?->user?->name ?? 'Teacher';
                        $status = strtoupper((string) $correction->status);

                        return [
                            "id" => $this->activityId($time, 60, $correction->id),
                            "action" => "ATTENDANCE_CORRECTION_" . $status,
                            "target" => "{$teacherName} attendance correction {$correction->status}",
                            "name" => "Correction {$status}",
                            "subject" => "{$teacherName} · " . ($correction->schedule?->subject?->name ?? $correction->request_type),
                            "time" => $this->activityTime($time),
                            "type" => $correction->status === 'pending' ? "teacher_request" : "teacher_review"
                        ];
                    });
                $activity = $activity->merge($correctionRequests);
            }

            // 7. Backup/restore operational events.
            if (\Illuminate\Support\Facades\Schema::hasTable('backup_restore_logs')) {
                $backupLogs = \App\Models\BackupRestoreLog::orderByDesc('id')
                    ->limit(10)
                    ->get()
                    ->map(function ($backupLog) {
                        $time = $backupLog->completed_at ?? $backupLog->updated_at ?? $backupLog->created_at;
                        $status = strtoupper((string) $backupLog->status);

                        return [
                            "id" => $this->activityId($time, 70, $backupLog->id),
                            "action" => strtoupper($backupLog->action),
                            "target" => $backupLog->message ?? $backupLog->file_name ?? $backupLog->action,
                            "name" => ucwords(str_replace('_', ' ', $backupLog->action)) . " {$status}",
                            "subject" => $backupLog->file_name ?: ($backupLog->message ?? 'Backup system event'),
                            "time" => $this->activityTime($time),
                            "type" => $backupLog->status === 'failed' ? "system_alert" : "system"
                        ];
                    });
                $activity = $activity->merge($backupLogs);
            }

            // 8. Existing system activity logs.
            $logs = ActivityLog::orderBy("id", "desc")->limit(15)->get()->map(function ($log) {
                return [
                    "id" => $this->activityId($log->created_at, 80, $log->id),
                    "action" => $log->action,
                    "target" => $log->target,
                    "name" => $log->action . " Log",
                    "subject" => $log->target,
                    "time" => $this->activityTime($log->created_at),
                    "type" => "system"
                ];
            });
            $activity = $activity->merge($logs);

            // 9. Recent attendance scans.
            $newAttendances = \App\Models\Attendance::with(["student.user", "session.classRoom.subject"])
                ->orderBy("id", "desc")
                ->limit(10)
                ->get()
                ->map(function ($att) {
                    $time = $att->created_at ?? $att->scan_time ?? now();

                    return [
                        "id" => $this->activityId($time, 90, $att->id),
                        "action" => "ATTENDANCE",
                        "target" => ($att->student && $att->student->user ? $att->student->user->name : "Unknown") . " @ " . ($att->session && $att->session->classRoom && $att->session->classRoom->subject ? $att->session->classRoom->subject->name : "Unknown"),
                        "name" => ($att->student && $att->student->user ? $att->student->user->name : "Attendance"),
                        "subject" => "Checked in to " . ($att->session && $att->session->classRoom && $att->session->classRoom->subject ? $att->session->classRoom->subject->name : "Unknown"),
                        "time" => $this->activityTime($time),
                        "type" => "attendance"
                    ];
                });
            $activity = $activity->merge($newAttendances);

            if ($lastId > 0) {
                $activity = $activity->filter(fn ($item) => (int) $item['id'] > $lastId);
            }

            $sortedActivity = $activity->sortByDesc('id')->values()->take($limit);

            return response()->json(["success" => true, "activity" => $sortedActivity]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    private function activityId($time, int $bucket, int $id): int
    {
        $timestamp = $time ? \Carbon\Carbon::parse($time)->timestamp : now()->timestamp;

        return ($timestamp * 100000) + ($bucket * 1000) + ($id % 1000);
    }

    private function activityTime($time): string
    {
        return $time ? \Carbon\Carbon::parse($time)->format("h:i A") : "Just now";
    }

    public function bulkDeleteSessions(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:attendance_sessions,id',
        ]);

        $ids = $request->session_ids;

        // Safety: only allow deleting scheduled sessions (not active/completed)
        $sessions = \App\Models\AttendanceSession::whereIn('id', $ids)->get();
        $allowed = $sessions->filter(fn($s) => in_array($s->status, ['scheduled']));
        $skipped = $sessions->count() - $allowed->count();

        if ($allowed->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No deletable sessions found. Only "scheduled" sessions can be deleted.'
            ], 422);
        }

        $deleted = $this->deleteCourseSessionsWithTeacherAttendance($allowed->pluck('id'));

        return response()->json([
            'success' => true,
            'deleted' => $allowed->count(),
            'skipped' => $skipped,
            'teacher_attendance_deleted' => $deleted['teacher_attendance_sessions'],
            'teacher_schedules_deleted' => $deleted['teacher_schedules'],
            'message' => "Deleted {$allowed->count()} session(s)." . ($skipped > 0 ? " {$skipped} non-scheduled session(s) were skipped." : '')
        ]);
    }
}
