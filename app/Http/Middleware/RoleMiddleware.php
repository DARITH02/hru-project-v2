<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles) || !$request->user()->is_approved) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized or pending approval'], 403);
            }
            
            if (!$request->user()->is_approved) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Your account is pending approval.');
            }

            return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
}
