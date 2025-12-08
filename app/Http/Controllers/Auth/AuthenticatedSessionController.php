<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Afișează formularul de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesează cererea de autentificare.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validează datele și încearcă autentificarea
        $request->authenticate();

        // Regenerăm sesiunea pentru siguranță
        $request->session()->regenerate();

        // ✅ Redirecționăm utilizatorul către homepage (lista de anunțuri)
        return redirect()->intended(route('services.index'));
    }

    /**
     * Deloghează utilizatorul.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Îl trimitem înapoi pe homepage
        return redirect('/');
    }
}
