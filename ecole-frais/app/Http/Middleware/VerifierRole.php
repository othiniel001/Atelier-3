<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Étape 1 : restreint une route à un ou plusieurs rôles.
 * Usage : ->middleware('role:admin,comptable')
 */
class VerifierRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->actif) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'Votre compte est désactivé.']);
        }

        if (! $user->aRole(...$roles)) {
            abort(403, 'Accès réservé : '.implode(', ', $roles).'.');
        }

        return $next($request);
    }
}
