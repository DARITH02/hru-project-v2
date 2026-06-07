<?php

namespace App\Console\Commands;

use App\Services\TeacherAttendanceService;
use Illuminate\Console\Command;

class ProcessTeacherAttendance extends Command
{
    protected $signature = 'teacher-attendance:process {--sync : Sync teacher schedules from existing class attendance sessions first}';

    protected $description = 'Process teacher attendance automation such as absent and missing check-out statuses.';

    public function handle(TeacherAttendanceService $attendanceService): int
    {
        if ($this->option('sync')) {
            $created = $attendanceService->syncFromStudentAttendanceSessions();
            $this->info("Synced {$created} new teacher schedules.");
        }

        $result = $attendanceService->markAutomatedStatuses();
        $this->info("Marked {$result['absent']} absent sessions.");
        $this->info("Marked {$result['missingCheckout']} missing check-out sessions.");

        return self::SUCCESS;
    }
}
