<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Vérification si l'utilisateur est authentifié
        if (!auth()->check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Vérification du rôle
        if (auth()->user()->role !== $role) {
            return response()->json([
                'message' => "Accès interdit. Rôle requis : $role.",
            ], 403);
        }

        return $next($request);
    }
}
