<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
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
    // 勤怠登録画面
    Route::get('/attendance', [StaffController::class, 'stamp']);
    Route::get('/redirect-after-login', function () {
        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }
        return redirect()->intended('/attendance');
    });
    Route::post('/start-work', [StaffController::class, 'start_work']);
    Route::post('/end-work', [StaffController::class, 'end_work']);
    Route::post('/start-break', [StaffController::class, 'start_break']);
    Route::post('/end-break', [StaffController::class, 'end_break']);
    // 勤怠一覧画面
    Route::get('/attendance/list', [StaffController::class, 'monthlyList']);
    // 勤怠詳細画面
    Route::get('/attendance/detail/{attendance_id}', [StaffController::class, 'detail']);
    // 申請登録
    Route::post('/stamp_correction_request/{attendance_id}', [StaffController::class, 'store']);
    // 申請一覧
    Route::get('/stamp_correction_request/list', [StaffController::class, 'correctRequestList']);
    // レポート
    Route::get('/attendance/report', [StaffController::class, 'report']);
});

Route::get('/admin/login', [AdminController::class, 'showLogin'])
    ->name('admin.admin_login');

Route::middleware(['auth', 'can:admin'])->group(function () {
    // 勤怠一覧（管理者）
    Route::get('/admin/attendance/list', [AdminController::class, 'adminDailyList'])
        ->name('admin.admin_daily_list');
    // 勤怠詳細（管理者）
    Route::get('/admin/attendance/{attendance_id}', [AdminController::class, 'adminDetail']);
    // スタッフ一覧（管理者）
    Route::get('/admin/staff/list', [AdminController::class, 'adminStaffList'])
        ->name('admin.admin_staff_list');
    // スタッフ別月次勤怠一覧（管理者）
    Route::get('/admin/attendance/staff/{user_id}', [AdminController::class, 'adminMonthlyList']);
    Route::post('/admin/attendance/staff/{user_id}/export', [AdminController::class, 'export']);
    // 申請詳細（管理者）
    Route::get(
        '/stamp_correction_request/approve/{attendance_correct_request_id}', 
        [AdminController::class, 'adminRequestDetail']
    );
    Route::post(
        '/admin/approve/{attendance_correct_request_id}', 
        [AdminController::class, 'approve']
    );
    Route::post(
        '/admin/correct/{attendance_id}', 
        [AdminController::class, 'updateAttendance']
    );
});