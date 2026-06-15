<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $loginType = $request->input('login_type');

        if ($loginType === 'admin') {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}