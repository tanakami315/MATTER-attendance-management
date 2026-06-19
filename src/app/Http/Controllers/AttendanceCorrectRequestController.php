<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakCorrectRequest;

class AttendanceCorrectRequestController extends Controller
{
    

    public function noRecord($user_id, $date)
    {
        $user = User::findOrFail($user_id);
        return view('admin.no_record', compact('user','date'));
    
    }

    

}
    