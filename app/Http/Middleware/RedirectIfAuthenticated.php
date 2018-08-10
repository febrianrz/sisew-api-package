<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Config;
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        
        setConnectionEnv($request);
        if (Auth::guard($guard)->check()) {
            return redirect('/');
        }
        return $next($request);
    }
}
