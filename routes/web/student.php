<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'demo.readonly'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/admin/students/overview', [DashboardController::class, 'index'])->name('admin.students.overview');
    Route::post('/regenerate-qr', [DashboardController::class, 'regenerateQr'])->name('regenerate-qr');
    Route::post('/simulate-scan', [DashboardController::class, 'simulateScan'])->name('simulate-scan');
});

Route::middleware(['auth', 'demo.readonly', 'role:student'])->group(function () {
    Route::get('/student/documents', [StudentDocumentController::class, 'index'])->name('student.documents.index');
    Route::get('/student/documents/{document}/download', [StudentDocumentController::class, 'download'])->name('student.documents.download');
});

Route::get('/scan/{session_id}', [DashboardController::class, 'studentScan'])->name('student.scan');
Route::post('/verify-attendance', [DashboardController::class, 'verifyAttendance'])
    ->middleware('demo.readonly')
    ->name('student.verify');
