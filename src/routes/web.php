<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

// 認証画面
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::get('/admin/login', [AuthController::class, 'adminLogin'])->name('admin_login');


// 一般ユーザー
Route::get('/attendance', [StaffAttendanceController::class, 'index'])->name('attendance');
Route::get('/attendance/list', [StaffAttendanceController::class, 'list'])->name('attendance_list');
Route::get('/attendance/detail/{id}', [StaffAttendanceController::class, 'detail'])->name('attendance_detail');
Route::get('/stamp_correction_request/list', [StaffStampController::class, 'list'])->name('stamp_correction_list');


// 管理者
Route::prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'attendanceList'])->name('admin_attendance_list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin_attendance_detail');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin_attendance_staff');
    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff_list');
});
Route::get('/stamp_correction_request/list', [AdminStampController::class, 'list'])->name('admin_stamp_correction_list');
Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampController::class, 'approve'])->name('admin_stamp_correction_approve');