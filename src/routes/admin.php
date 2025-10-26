<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(['guest'])
        ->name('admin.login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['guest'])
        ->name('admin.login.store');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth'])
        ->name('admin.logout');

        Route::middleware(['auth'])->group(function () {
            Route::get('/attendance', [AdminAttendanceController::class, 'index'])
                ->name('admin.attendance.index');
        });
});