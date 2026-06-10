<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

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



Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/redirect-after-login', function () {
        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }
        return redirect()->intended('/attendance');
    });
    Route::post('/start-work', [AttendanceController::class, 'start_work']);
    Route::post('/end-work', [AttendanceController::class, 'end_work']);
    Route::post('/start-break', [AttendanceController::class, 'start_break']);
    Route::post('/end-break', [AttendanceController::class, 'end_break']);

    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{attendance_id}', [AttendanceController::class, 'detail']);
    Route::post('/application/{attendance_id}', [ApplicationController::class, 'store']);
    Route::get('/stamp_correction_request/list', [ApplicationController::class, 'applicationList']);
});

Route::get('/admin/login', [AuthController::class, 'showLogin'])
    ->name('admin.admin-login');

Route::middleware(['auth', 'can:admin'])->group(function () {
    // 勤怠一覧（管理者）
    Route::get('/admin/attendance/list', [AdminController::class, 'adminDailyList'])
        ->name('admin.admin-daily-list');
    // 勤怠詳細（管理者）
    Route::get('/admin/attendance/{attendance_id}', [AttendanceController::class, 'detail']);
    // スタッフ一覧（管理者）
    Route::get('/admin/staff/list', [AdminController::class, 'adminStaffList'])
        ->name('admin.admin-staff-list');
    // スタッフ別月次勤怠一覧（管理者）
    Route::get('/admin/attendance/staff/{user_id}', [AdminController::class, 'adminMonthlyList'])
        ->name('admin.admin-monthly-list');
    Route::get(
        '/stamp_correction_request/approve/{application_id}', 
        [ApplicationController::class, 'showApprove']
    );
});