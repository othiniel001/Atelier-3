<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Journal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Étape 1 : connexion / déconnexion, redirection selon le rôle. */
class ConnexionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $cle = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($cle).' secondes.',
            ]);
        }

        $identifiants = $request->only('email', 'password') + ['actif' => true];

        if (! Auth::attempt($identifiants, $request->boolean('remember'))) {
            RateLimiter::hit($cle);
            throw ValidationException::withMessages(['email' => 'Identifiants incorrects ou compte désactivé.']);
        }

        RateLimiter::clear($cle);
        $request->session()->regenerate();
        Journal::ecrire('connexion', $request->user());

        return redirect()->intended(route($request->user()->routeAccueil()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Journal::ecrire('deconnexion', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
