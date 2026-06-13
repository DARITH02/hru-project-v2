<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemMaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = app(MaintenanceModeService::class);

        if (!$maintenance->enabled() || $this->isAllowedDuringMaintenance($request)) {
            return $next($request);
        }

        $message = $maintenance->message();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'maintenance_mode' => true,
            ], 503);
        }

        return response()->view('errors.maintenance', [
            'message' => $message,
        ], 503);
    }

    private function isAllowedDuringMaintenance(Request $request): bool
    {
        if ($request->user()?->isSuperAdmin()) {
            return true;
        }

        if ($request->routeIs([
            'login',
            'login.post',
            'logout',
            'language.switch',
            'google-drive.oauth.callback',
        ])) {
            return true;
        }

        return $request->is([
            'build/*',
            'favicon.ico',
            'storage/*',
            'up',
        ]);
    }
}
