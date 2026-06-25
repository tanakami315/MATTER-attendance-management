<?php

use App\Http\Controllers\Api\V1\AttendanceRecordController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// 勤怠一覧の取得
Route::get('/v1/attendance-records', [AttendanceRecordController::class, 'index']);
// 勤怠詳細の取得
Route::get('/v1/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);

Route::post('/v1/tokens', [AuthTokenController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    // 勤怠登録
    Route::post('/v1/attendance-records', [AttendanceRecordController::class, 'store']);
});
