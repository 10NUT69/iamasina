<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (auth()->check()) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
