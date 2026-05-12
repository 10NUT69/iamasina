<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Dacă utilizatorul nu este logat, îl trimitem la login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // 2. Verificăm rolul din baza de date, nu o listă hardcodată de emailuri.
        if (!Auth::user()->is_admin) {
            // Dacă nu este admin, aruncăm eroare 404 (Not Found) pentru discreție.
            abort(404);
        }

        return $next($request);
    }
}
