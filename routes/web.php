<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\SectionHeadController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/registration', [AuthController::class, 'showLoginForm'])->name('registration');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('kpidashboard');
    Route::get('/academicreports', [DashboardController::class, 'academicReports'])->name('academicreports');

    // No route-level gate here on purpose: leadership is redirected to the
    // dashboard (not 403'd) by the controller via canAccessQuantQualObservationPages().
    Route::get('/quantitative-observations', [DashboardController::class, 'quantitativeObservations'])->name('quantitativeobservations');
    Route::get('/qualitative-observations', [DashboardController::class, 'qualitativeObservations'])->name('qualitativeobservations');

    Route::get('/adminpanel', [DashboardController::class, 'adminPanel'])->name('adminpanel')->middleware('can:adminpanel.view');

    Route::get('/systemsettings', [DashboardController::class, 'systemSettings'])->name('systemsettings')->middleware('can:settings.view');
    Route::put('/systemsettings/avatar', [DashboardController::class, 'updateOwnAvatar'])->name('systemsettings.avatar')->middleware('can:settings.updateOwnAvatar');

    Route::middleware('can:sectionheads.view')->group(function (): void {
        Route::get('/sechead', [SectionHeadController::class, 'index'])->name('sechead');
    });
    Route::post('/section-heads', [SectionHeadController::class, 'store'])->name('section-heads.store')->middleware('can:sectionheads.manage');
    Route::put('/section-heads/{user}', [SectionHeadController::class, 'update'])->name('section-heads.update')->middleware('can:sectionheads.manage');
    Route::delete('/section-heads/{user}', [SectionHeadController::class, 'destroy'])->name('section-heads.destroy')->middleware('can:sectionheads.delete');

    Route::get('/teachermanagement', [FacultyController::class, 'index'])->name('teachermanagement')->middleware('can:faculty.view');
    Route::post('/faculty', [FacultyController::class, 'store'])->name('faculty.store')->middleware('can:faculty.manage');
    Route::put('/faculty/{user}', [FacultyController::class, 'update'])->name('faculty.update')->middleware('can:faculty.manage');
    Route::delete('/faculty/{user}', [FacultyController::class, 'destroy'])->name('faculty.destroy')->middleware('can:faculty.delete');
    Route::get('/faculty', fn () => redirect()->route('teachermanagement'));

    Route::get('/observations', [DashboardController::class, 'observations'])->name('observations')->middleware('can:observations.view');
    Route::post('/observations', [DashboardController::class, 'storeObservation'])->name('observations.store')->middleware('can:observations.record');
    Route::put('/observations/{observation}', [DashboardController::class, 'updateObservation'])->name('observations.update')->middleware('can:observations.record');
});
