<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Handle response after user logs in.
     */
    public function toResponse($request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.attendance.list');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('attendance.index');
        }

        return redirect('/login');
    }
}
