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

        // 2. LISTA DE EMAIL-URI PERMISE (Adaugă aici alte email-uri dacă e nevoie)
        $admins = [
            'ionut.pirlogea@yahoo.com',
        ];

        // 3. Verificăm dacă email-ul utilizatorului curent este în listă
        if (!in_array(Auth::user()->email, $admins)) {
            // Dacă nu e Ionuț, aruncăm eroare 404 (Not Found) pentru discreție
            abort(404);
        }

        return $next($request);
    }
}