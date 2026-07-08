<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Get the logout redirect response.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function toResponse($request)
    {
        $loginType = $request->input('login_type');

        if ($loginType === 'admin') {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}