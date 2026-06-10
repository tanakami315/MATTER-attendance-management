<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if (session('login_type') === 'admin') {
            session()->flush();
            return redirect('/admin/login');
        }
        session()->flush();
        return redirect('/login');
    }

}