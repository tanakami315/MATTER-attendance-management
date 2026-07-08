<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Get the login redirect response.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function toResponse($request)
    {
        if (auth()->user()->admin_status === 1) {
            return redirect('/admin/attendance/list');
        }

        return redirect('/redirect-after-login');
    }
}