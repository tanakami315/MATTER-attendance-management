<?php

use App\Http\Controllers\Api\AttendanceRecordController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// 勤怠一覧の取得
Route::get('/v1/attendance-records', [AttendanceRecordController::class, 'index']);
// 勤怠詳細の取得
Route::get('/v1/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);