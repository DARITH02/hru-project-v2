<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/teacher/attendance/qr-scan', [TeacherAttendanceController::class, 'publicScan'])->name('teacher.attendance.public-scan');
Route::post('/teacher/attendance/qr-scan', [TeacherAttendanceController::class, 'publicQrCheckIn'])
    ->middleware('throttle:teacher-qr')
    ->name('teacher.attendance.public-qr-check-in');

Route::middleware(['auth', 'demo.readonly'])->group(function () {
    Route::get('/teacher/reports', [DashboardController::class, 'teacherReports'])->name('teacher.reports');
});

Route::middleware(['auth', 'demo.readonly', 'role:teacher'])->group(function () {
    Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index'])->name('teacher.attendance');
    Route::get('/teacher/attendance/scan', [TeacherAttendanceController::class, 'scan'])->name('teacher.attendance.scan');
    Route::post('/teacher/attendance/qr-check-in', [TeacherAttendanceController::class, 'qrCheckIn'])->name('teacher.attendance.qr-check-in');
    Route::get('/teacher/attendance/checkout', [TeacherAttendanceController::class, 'checkoutPage'])->name('teacher.attendance.checkout');
    Route::post('/teacher/attendance/{session}/check-in', [TeacherAttendanceController::class, 'checkIn'])->name('teacher.attendance.check-in');
    Route::post('/teacher/attendance/{session}/check-out', [TeacherAttendanceController::class, 'checkOut'])->name('teacher.attendance.check-out');
    Route::post('/teacher/attendance/corrections', [TeacherAttendanceController::class, 'storeCorrection'])->name('teacher.attendance.corrections.store');
    Route::post('/teacher/attendance/class-change-requests', [TeacherAttendanceController::class, 'storeClassChange'])->name('teacher.attendance.class-change.store');
    Route::post('/teacher/student-permissions', [TeacherAttendanceController::class, 'storeStudentPermissionRequest'])->name('teacher.student-permissions.store');

    Route::get('/teacher/documents', [TeacherDocumentController::class, 'index'])->name('teacher.documents.index');
    Route::get('/teacher/documents/create', [TeacherDocumentController::class, 'create'])->name('teacher.documents.create');
    Route::post('/teacher/documents', [TeacherDocumentController::class, 'store'])->name('teacher.documents.store');
    Route::get('/teacher/documents/{document}/download', [TeacherDocumentController::class, 'download'])->name('teacher.documents.download');
});
