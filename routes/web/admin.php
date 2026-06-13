<?php

use App\Http\Controllers\Admin\AdminController as AdminUIController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\RestoreController;
use App\Http\Controllers\Admin\TeacherAttendanceController as AdminTeacherAttendanceController;
use App\Http\Controllers\Admin\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'demo.readonly', 'role:admin,super_admin'])->group(function () {
    Route::get('/admin', [AdminUIController::class, 'mainDashboard'])->name('admin.dashboard');
    Route::get('/admin/instructors', [AdminUIController::class, 'instructors'])->name('admin.instructors');
    Route::get('/admin/results', [AdminUIController::class, 'analytics'])->name('admin.results');
    Route::get('/admin/attendance-issues', [AdminUIController::class, 'attendanceIssues'])->name('admin.attendance-issues');
    Route::post('/admin/attendance-issues/{id}/toggle-blacklist', [AdminUIController::class, 'toggleBlacklist'])->name('admin.attendance-issues.toggle-blacklist');
    Route::get('/admin/attendance-issues/export/pdf', [AdminUIController::class, 'exportAttendanceIssuesPdf'])->name('admin.attendance-issues.export.pdf');
    Route::post('/admin/attendance-issues/send-telegram', [AdminUIController::class, 'sendAttendanceIssuesToTelegram'])->name('admin.attendance-issues.send-telegram');
    Route::get('/admin/teacher-accounts', [AdminUIController::class, 'teacherAccounts'])->name('admin.teacher-accounts');

    Route::get('/admin/teacher-attendance', [AdminTeacherAttendanceController::class, 'dashboard'])->name('admin.teacher-attendance');
    Route::post('/admin/teacher-attendance/sync', [AdminTeacherAttendanceController::class, 'sync'])->name('admin.teacher-attendance.sync');
    Route::get('/admin/teacher-attendance/scan-qr', [AdminTeacherAttendanceController::class, 'scanQr'])->name('admin.teacher-attendance.scan-qr');
    Route::get('/admin/teacher-attendance/scan-monitor', [AdminTeacherAttendanceController::class, 'scanMonitor'])->name('admin.teacher-attendance.scan-monitor');
    Route::get('/admin/teacher-attendance/scan-monitor/data', [AdminTeacherAttendanceController::class, 'scanMonitorData'])->name('admin.teacher-attendance.scan-monitor.data');
    Route::get('/admin/teacher-attendance/sessions/{session}/qr-token', [AdminTeacherAttendanceController::class, 'qrToken'])->name('admin.teacher-attendance.sessions.qr-token');
    Route::put('/admin/teacher-attendance/sessions/{session}', [AdminTeacherAttendanceController::class, 'updateSession'])->name('admin.teacher-attendance.sessions.update');
    Route::post('/admin/teacher-attendance/sessions/{session}/check-in', [AdminTeacherAttendanceController::class, 'manualCheckIn'])->name('admin.teacher-attendance.sessions.check-in');
    Route::post('/admin/teacher-attendance/sessions/{session}/check-out', [AdminTeacherAttendanceController::class, 'manualCheckOut'])->name('admin.teacher-attendance.sessions.check-out');
    Route::get('/admin/teacher-attendance/corrections', [AdminTeacherAttendanceController::class, 'corrections'])->name('admin.teacher-attendance.corrections');
    Route::post('/admin/teacher-attendance/corrections/{correction}/approve', [AdminTeacherAttendanceController::class, 'approveCorrection'])->name('admin.teacher-attendance.corrections.approve');
    Route::post('/admin/teacher-attendance/corrections/{correction}/reject', [AdminTeacherAttendanceController::class, 'rejectCorrection'])->name('admin.teacher-attendance.corrections.reject');
    Route::get('/admin/teacher-attendance/class-change-requests', [AdminTeacherAttendanceController::class, 'changeRequests'])->name('admin.teacher-attendance.class-change');
    Route::post('/admin/teacher-attendance/class-change-requests/{changeRequest}/approve', [AdminTeacherAttendanceController::class, 'approveChangeRequest'])->name('admin.teacher-attendance.class-change.approve');
    Route::post('/admin/teacher-attendance/class-change-requests/{changeRequest}/reject', [AdminTeacherAttendanceController::class, 'rejectChangeRequest'])->name('admin.teacher-attendance.class-change.reject');
    Route::get('/admin/teacher-attendance/reports', [AdminTeacherAttendanceController::class, 'reports'])->name('admin.teacher-attendance.reports');
    Route::get('/admin/teacher-attendance/reports/export/pdf', [AdminTeacherAttendanceController::class, 'exportReportsPdf'])->name('admin.teacher-attendance.reports.export.pdf');
    Route::post('/admin/teacher-attendance/reports/send-telegram', [AdminTeacherAttendanceController::class, 'sendReportsToTelegram'])->name('admin.teacher-attendance.reports.send-telegram');

    Route::middleware(['auth', 'role:super_admin'])->group(function () {
        Route::post('/admin/users/{id}/approve', [AdminUIController::class, 'approveUser'])->name('admin.users.approve');
        Route::delete('/admin/users/{id}', [AdminUIController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::delete('/admin/attendance-issues/history/drop-all', [AdminUIController::class, 'dropAllHistory'])->name('admin.attendance-issues.history.drop-all');
        Route::delete('/admin/attendance-issues/history/bulk-drop', [AdminUIController::class, 'bulkDropHistory'])->name('admin.attendance-issues.history.bulk-drop');

        Route::get('/admin/backup-restore', [BackupController::class, 'index'])->name('admin.backup-restore');
        Route::post('/admin/backup-restore/backup', [BackupController::class, 'store'])->name('admin.backup-restore.backup');
        Route::get('/admin/backup-restore/download/{fileName}', [BackupController::class, 'download'])->name('admin.backup-restore.download');
        Route::get('/admin/backup-restore/cloud/download/{fileId}/{fileName}', [BackupController::class, 'downloadCloud'])->name('admin.backup-restore.cloud.download');
        Route::delete('/admin/backup-restore/local/{fileName}', [BackupController::class, 'destroyLocal'])->name('admin.backup-restore.local.destroy');
        Route::delete('/admin/backup-restore/cloud/{fileId}', [BackupController::class, 'destroyCloud'])->name('admin.backup-restore.cloud.destroy');
        Route::post('/admin/backup-restore/restore', [RestoreController::class, 'store'])->name('admin.backup-restore.restore');
        Route::post('/admin/backup-restore/restore/cloud', [RestoreController::class, 'storeCloud'])->name('admin.backup-restore.restore.cloud');
    });

    Route::get('/admin/students', [AdminUIController::class, 'students'])->name('admin.students');
    Route::get('/admin/courses', [AdminUIController::class, 'courses'])->name('admin.courses');
    Route::get('/admin/pre-end-review/{id}', [AdminUIController::class, 'coursePreEnd'])->name('admin.courses.pre-end');
    Route::get('/admin/pre-end-review/{id}/export', [AdminUIController::class, 'exportCoursePreEnd'])->name('admin.courses.pre-end.export');
    Route::get('/admin/classes', [AdminUIController::class, 'classes'])->name('admin.classes');
    Route::get('/admin/subjects', [AdminUIController::class, 'subjects'])->name('admin.subjects');
    Route::get('/admin/departments', [AdminUIController::class, 'departments'])->name('admin.departments');
    Route::get('/admin/permissions', [AdminUIController::class, 'permissions'])->name('admin.permissions');
    Route::post('/admin/permissions', [AdminUIController::class, 'storePermission'])->name('admin.permissions.store');
    Route::delete('/admin/permissions/{id}', [AdminUIController::class, 'destroyPermission'])->name('admin.permissions.destroy');
    Route::get('/admin/settings', [AdminUIController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminUIController::class, 'updateSettings'])->name('admin.settings.update');
    Route::get('/admin/settings/export', [AdminUIController::class, 'exportSummaryReport'])->name('admin.settings.export');
    Route::post('/admin/cache/clear', [AdminUIController::class, 'clearCache'])->name('admin.cache.clear');

    Route::get('/admin/export/instructors', [AdminUIController::class, 'exportInstructors'])->name('admin.export.instructors');
    Route::get('/admin/export/students', [AdminUIController::class, 'exportStudents'])->name('admin.export.students');
    Route::get('/admin/export/courses', [AdminUIController::class, 'exportCourses'])->name('admin.export.courses');
    Route::get('/admin/export/subjects', [AdminUIController::class, 'exportSubjects'])->name('admin.export.subjects');
    Route::get('/admin/export/departments', [AdminUIController::class, 'exportDepartments'])->name('admin.export.departments');
    Route::get('/admin/export/classes', [AdminUIController::class, 'exportClasses'])->name('admin.export.classes');
    Route::get('/admin/results/export/excel', [AdminUIController::class, 'exportResultsExcel'])->name('admin.results.export.excel');
    Route::get('/admin/results/export/pdf', [AdminUIController::class, 'exportResultsPdf'])->name('admin.results.export.pdf');
    Route::post('/admin/results/send-telegram', [AdminUIController::class, 'sendResultsToTelegram'])->name('admin.results.send-telegram');

    Route::get('/admin/telegram-bots', fn () => redirect()->route('admin.settings'));
    Route::post('/admin/telegram-bots', [TelegramBotController::class, 'store'])->name('admin.telegram-bots.store');
    Route::post('/admin/telegram-bots/{id}/active', [TelegramBotController::class, 'setActive'])->name('admin.telegram-bots.active');
    Route::post('/admin/telegram-bots/{id}/sync', [TelegramBotController::class, 'sync'])->name('admin.telegram-bots.sync');
    Route::delete('/admin/telegram-bots/{id}', [TelegramBotController::class, 'destroy'])->name('admin.telegram-bots.destroy');
    Route::post('/admin/telegram-bots/{id}/test', [TelegramBotController::class, 'sendTest'])->name('admin.telegram-bots.test');
});
