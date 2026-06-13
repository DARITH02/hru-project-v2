<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class MaintenanceModeService
{
    private const CACHE_KEY = 'system_maintenance_mode';

    public function enabled(): bool
    {
        return Cache::remember(self::CACHE_KEY, 10, function () {
            return Setting::get('maintenance_mode_enabled', 'false') === 'true';
        });
    }

    public function message(): string
    {
        return Setting::get(
            'maintenance_mode_message',
            'System maintenance is active. Please try again later.'
        );
    }

    public function enable(?string $message = null, ?int $userId = null): void
    {
        Setting::set('maintenance_mode_enabled', 'true', 'system');

        if ($message !== null && trim($message) !== '') {
            Setting::set('maintenance_mode_message', trim($message), 'system');
        }

        Setting::set('maintenance_mode_enabled_by', (string) $userId, 'system');
        Setting::set('maintenance_mode_enabled_at', now()->toIso8601String(), 'system');

        Cache::forget(self::CACHE_KEY);
    }

    public function disable(?int $userId = null): void
    {
        Setting::set('maintenance_mode_enabled', 'false', 'system');
        Setting::set('maintenance_mode_disabled_by', (string) $userId, 'system');
        Setting::set('maintenance_mode_disabled_at', now()->toIso8601String(), 'system');

        Cache::forget(self::CACHE_KEY);
    }

    public function status(): array
    {
        return [
            'enabled' => $this->enabled(),
            'message' => $this->message(),
            'enabled_by' => Setting::get('maintenance_mode_enabled_by'),
            'enabled_at' => Setting::get('maintenance_mode_enabled_at'),
            'disabled_by' => Setting::get('maintenance_mode_disabled_by'),
            'disabled_at' => Setting::get('maintenance_mode_disabled_at'),
        ];
    }
}
