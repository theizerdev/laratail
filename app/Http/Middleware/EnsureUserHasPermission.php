<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @param  string|null  $guard
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $guard = null): Response
    {
        $authGuard = Auth::guard($guard);

        if (! $authGuard->check()) {
            return $this->unauthenticated($request);
        }

        $user = $authGuard->user();

        // Support multiple permissions separated by |
        $permissions = explode('|', $permission);

        if ($user->hasAnyPermission($permissions, $guard)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have the required permission.');
    }

    protected function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
