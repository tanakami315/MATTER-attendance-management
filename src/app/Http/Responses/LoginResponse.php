<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // dd(auth()->user()->id, auth()->user()->admin_status);
        if (auth()->user()->admin_status == 1) {
            return redirect('/admin/attendance/list');
        }
        
        return redirect('/attendance');
    }
}