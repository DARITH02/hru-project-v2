<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoReadOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user?->email === 'demo@example.com'
            && !$request->isMethodSafe()
            && !$request->routeIs('logout')
            && !$request->is('api/logout')
        ) {
            $message = 'Demo mode is read-only. Data changes are disabled.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
