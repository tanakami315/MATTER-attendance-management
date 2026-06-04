<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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
});