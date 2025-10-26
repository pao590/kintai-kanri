<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRequestController;

/*
|--------------------------------------------------------------------------
| Web Routesa
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//一般ユーザー
Route::middleware(['auth'])->group(function () {
    
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    Route::post('/attendance/detail/{id}/update', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::get('/stamp_correction_request/list',[StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
});

Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('attendance.index');
    }

    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.attendance.list');
    }

    return redirect('/login');
});


// 管理者ルート
Route::prefix('admin')->name('admin.')->group(function () {

    // ✅ ログイン関連（middlewareをつけない）
    Route::get('/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'login'])
        ->name('login.post');

    // ✅ ログアウト（認証済みのみ）
    Route::post('/signout', [App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('logout');

    // ✅ 認証が必要な管理画面
    Route::middleware(['auth:admin'])->group(function () {

        Route::get('/staff/list', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])
            ->name('staff.list');

        Route::get('/attendance/staff/{id}', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'stafflist'])
            ->name('attendance.staff');

        Route::get('/attendance/list', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'index'])
            ->name('attendance.list');

        Route::get('/attendances/{id}', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'show'])
            ->name('attendance.show');

        Route::put('/attendances/{id}', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'update'])
            ->name('attendance.update');

        Route::get('/requests', [App\Http\Controllers\Admin\AdminRequestController::class, 'index'])
            ->name('requests.index');

        Route::get('/requests/approve/{id}', [App\Http\Controllers\Admin\AdminRequestController::class, 'approveView'])
            ->name('requests.approve.view');

        Route::post('/requests/{id}/approve', [App\Http\Controllers\Admin\AdminRequestController::class, 'approve'])
            ->name('requests.approve');

        Route::get('/requests/{id}', [App\Http\Controllers\Admin\AdminRequestController::class, 'show'])
            ->name('requests.show');
    });
});




// Route::get('/email/verify', function () {
//     return view('user.auth.verify');
// })->middleware('auth')->name('verification.notice');

// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();
//     return redirect('/');
// })->middleware(['auth', 'signed'])->name('verification.verify');

// Route::post('/email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return back()->with('message', '認証メールを再送しました。');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
