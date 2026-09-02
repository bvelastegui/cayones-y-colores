<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $allowedRoles = array_map(
            static fn (string $role): string => UserRole::tryFrom($role)->value ?? $role,
            $roles,
        );

        if (! in_array($user->role->value, $allowedRoles, true)) {
            return response()->json(['message' => 'No tiene permisos para acceder a este recurso.'], 403);
        }

        return $next($request);
    }
}
