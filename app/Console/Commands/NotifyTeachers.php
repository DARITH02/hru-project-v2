<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;
use App\Services\TelegramService;
use Carbon\Carbon;

class NotifyTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily telegram notifications to teachers about their scheduled sessions';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService)
    {
        $today = Carbon::today();
        
        $sessions = AttendanceSession::whereDate('start_time', $today)
            ->with(['classRoom.teacher.user', 'classRoom.subject'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions scheduled for today.');
            return;
        }

        $sessionsByTeacher = $sessions->groupBy(function($session) {
            return $session->classRoom->teacher_id;
        });

        $count = 0;
        foreach ($sessionsByTeacher as $teacherId => $teacherSessions) {
            $teacher = $teacherSessions->first()->classRoom->teacher;
            
            if ($teacher && $teacher->telegram_id) {
                $teacherName = $teacher->user->name;
                
                $message = "🏫 <b>Academic Schedule Reminder</b>\n\n";
                $message .= "Good morning, <b>{$teacherName}</b>!\n";
                $message .= "Here is your schedule for today (" . $today->format('d M Y') . "):\n\n";

                foreach ($teacherSessions as $session) {
                    $subjectName = $session->classRoom->subject->name;
                    $startTime = Carbon::parse($session->start_time)->format('H:i');
                    $endTime = Carbon::parse($session->end_time)->format('H:i');
                    $room = $session->classRoom->room_number;
                    
                    $message .= "📚 <b>" . e($subjectName) . "</b>\n";
                    $message .= "   ⏰ {$startTime} - {$endTime} | 📍 Room " . e($room) . "\n\n";
                }

                $message .= "Please remember to activate check-in for your classes on time. Have a productive day! 🚀";

                if ($telegramService->sendMessage($teacher->telegram_id, $message)) {
                    $count++;
                }
            }
        }

        $this->info("Daily notifications sent successfully to {$count} teachers.");
    }
}
