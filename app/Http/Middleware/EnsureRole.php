<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès à une route à une liste de rôles.
 * Usage: ->middleware('role:admin,gestionnaire')
 * Le rôle "super_admin" a toujours accès à tout, quelle que soit la liste.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->role === 'super_admin' || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, "Vous n'avez pas accès à cette page.");
    }
}
