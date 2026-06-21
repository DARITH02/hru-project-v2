<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notify:teachers')->dailyAt('07:15');
Schedule::command('teacher-attendance:process --sync')->everyFifteenMinutes();

Schedule::command('chat:cleanup-old-history --days=7')
    ->dailyAt('01:30')
    ->name('chat.cleanup-old-history')
    ->withoutOverlapping();

Schedule::command('backup:run full')
    ->dailyAt('02:00')
    ->name('backup-restore.daily-full')
    ->withoutOverlapping();

foreach (['08:00', '12:00', '16:00', '20:00'] as $time) {
    Schedule::command('backup:run incremental')
        ->dailyAt($time)
        ->name('backup-restore.incremental-' . str_replace(':', '', $time))
        ->withoutOverlapping();
}

Schedule::command('backup:run weekly')
    ->weeklyOn(0, '03:00')
    ->name('backup-restore.weekly')
    ->withoutOverlapping();

Schedule::command('backup:run monthly')
    ->monthlyOn(1, '04:00')
    ->name('backup-restore.monthly')
    ->withoutOverlapping();

Schedule::command('backup:cleanup')
    ->dailyAt('05:00')
    ->name('backup-restore.cleanup')
    ->withoutOverlapping();

Schedule::command('backup:verify')
    ->weeklyOn(0, '04:00')
    ->name('backup-restore.weekly-verification')
    ->withoutOverlapping();

Schedule::command('backup:restore-test')
    ->dailyAt('06:00')
    ->when(fn () => now()->isSunday() && now()->day <= 7)
    ->name('backup-restore.monthly-restore-test')
    ->withoutOverlapping();
