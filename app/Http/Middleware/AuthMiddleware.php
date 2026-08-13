<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('user_id')) {
            return redirect()->route('login')->withErrors(['auth' => 'กรุณาเข้าสู่ระบบก่อน']);
        }
        return $next($request);
    }
}
