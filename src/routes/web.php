<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Staff\AttendanceController as StaffAttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StampCorrectionController as AdminStampController;
use App\Http\Controllers\Staff\StampCorrectionController as StaffStampController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* 認証関連 */
// 一般ユーザー
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// メール認証(一般ユーザーのみ)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verify/auto', [AuthController::class, 'Verify'])
        ->name('verification.auto');// ボタン押下で自動認証
    Route::post('/email/resend', [AuthController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

//管理者
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });
});


/* 認証後 */
// 一般ユーザー
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [StaffAttendanceController::class, 'index'])->name('attendance');
    Route::get('/attendance/list', [StaffAttendanceController::class, 'list'])->name('attendance_list');
    Route::get('/attendance/detail', [StaffAttendanceController::class, 'detail'])->name('attendance_detail');
    Route::get('/stamp_correction_request/list', [StaffStampController::class, 'list'])->name('stamp_correction_list');
});

// 管理者
Route::middleware('auth:admin')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::get('/attendance/list', [AdminAttendanceController::class, 'attendanceList'])->name('admin_attendance_list');
        Route::get('/attendance', [AdminAttendanceController::class, 'detail'])->name('admin_attendance_detail');
        Route::get('/attendance/staff', [AdminAttendanceController::class, 'staffAttendance'])->name('admin_attendance_staff');
        Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff_list');
    });
    Route::get('/stamp_correction_request/list', [AdminStampController::class, 'list'])->name('admin_stamp_correction_list');
    Route::get('/stamp_correction_request/approve', [AdminStampController::class, 'approve'])->name('admin_stamp_correction_approve');
});