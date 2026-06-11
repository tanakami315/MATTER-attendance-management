<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->login_type === 'admin') {
            session([
                'login_type' => 'admin'
            ]);
        
            return redirect('/admin/attendance/list');
        }

        session([
            'login_type' => 'staff'
        ]);
        
        return redirect('/attendance');
    }
}