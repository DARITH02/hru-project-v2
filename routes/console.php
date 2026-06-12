<?php

use Illuminate\Foundation\Inspiring;
use App\Jobs\BackupJob;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notify:teachers')->dailyAt('07:15');
Schedule::command('teacher-attendance:process --sync')->everyFifteenMinutes();
Schedule::job(new BackupJob(null, true))->dailyAt('02:00')->name('backup-restore.daily-backup')->withoutOverlapping();
Schedule::call(function () {
    app(BackupService::class)->deleteOldLocalBackups(30);
    app(GoogleDriveService::class)->deleteOldBackups(90);
})->dailyAt('03:00')->name('backup-restore.cleanup')->withoutOverlapping();
