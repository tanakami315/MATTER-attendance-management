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

Route::apiResource('v1/attendance-records', AttendanceRecordController::class)
    ->parameters([
        'attendance-records' => 'attendanceRecord',
    ])
    ->only(['index', 'show']);

Route::post('/v1/tokens', [AuthTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/attendance-records', AttendanceRecordController::class)
        ->parameters([
            'attendance-records' => 'attendanceRecord',
        ])
        ->only(['store', 'update', 'destroy']);
});
