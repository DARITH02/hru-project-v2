@extends('layouts.app')

@php
    $timezone = config('app.timezone', 'Asia/Phnom_Penh');
    $locale = app()->getLocale();
    $now = \Carbon\CarbonImmutable::now($timezone);

    $localTotalBytes = collect($localBackups)->sum(fn ($backup) => (int) ($backup['size'] ?? 0));
    $cloudTotalBytes = collect($cloudBackups)->sum(fn ($backup) => (int) ($backup['size'] ?? 0));
    $latestLocal = collect($localBackups)->first();
    $latestCloud = collect($cloudBackups)->first();
    $lastSuccess = $logs->getCollection()->firstWhere('status', 'success');
    $runningLogs = $logs->getCollection()->whereIn('status', ['pending', 'running', 'processing', 'started'])->count();

    $formatBytes = static function (int $bytes): string {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024 / 1024, 2) . ' GB';
        }

        return number_format($bytes / 1024 / 1024, 2) . ' MB';
    };

    $statusClass = static fn (?string $status): string => match ($status) {
        'success' => 'is-success',
        'failed', 'error' => 'is-danger',
        default => 'is-warning',
    };

    $formatDateTime = static fn ($value): string => \Carbon\Carbon::parse($value)
        ->timezone($timezone)
        ->locale($locale)
        ->translatedFormat('Y-m-d H:i:s');

    $formatNextRun = static function (\Carbon\CarbonImmutable $next) use ($now, $locale): string {
        $day = match (true) {
            $next->isSameDay($now) => __('admin.backup_restore.today'),
            $next->isSameDay($now->addDay()) => __('admin.backup_restore.tomorrow'),
            default => $next->locale($locale)->translatedFormat('M d'),
        };

        return $day . ' ' . $next->format('H:i') . ' (' . $next->locale($locale)->diffForHumans($now, [
            'parts' => 2,
            'join' => true,
            'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
        ]) . ')';
    };

    $nextDailyAt = static function (string $time) use ($now): \Carbon\CarbonImmutable {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $next = $now->setTime($hour, $minute);

        return $next->isPast() ? $next->addDay() : $next;
    };

    $nextFromDailyTimes = static function (array $times) use ($nextDailyAt): \Carbon\CarbonImmutable {
        return collect($times)
            ->map(fn (string $time) => $nextDailyAt($time))
            ->sort()
            ->first();
    };

    $nextWeeklyAt = static function (int $dayOfWeek, string $time) use ($now): \Carbon\CarbonImmutable {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $next = $now->next($dayOfWeek)->setTime($hour, $minute);
        $today = $now->setTime($hour, $minute);

        return $now->dayOfWeek === $dayOfWeek && !$today->isPast() ? $today : $next;
    };

    $nextMonthlyAt = static function (int $day, string $time) use ($now): \Carbon\CarbonImmutable {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $next = $now->setDay(min($day, $now->daysInMonth))->setTime($hour, $minute);

        if ($next->isPast()) {
            $nextMonth = $now->addMonthNoOverflow();
            return $nextMonth->setDay(min($day, $nextMonth->daysInMonth))->setTime($hour, $minute);
        }

        return $next;
    };

    $scheduledJobs = [
        [
            'name' => __('admin.backup_restore.schedule_postgresql_full'),
            'frequency' => __('admin.backup_restore.schedule_postgresql_full_frequency'),
            'next' => $formatNextRun($nextDailyAt('02:00')),
            'tone' => 'blue',
        ],
        [
            'name' => __('admin.backup_restore.schedule_user_files_incremental'),
            'frequency' => __('admin.backup_restore.schedule_user_files_incremental_frequency'),
            'next' => $formatNextRun($nextFromDailyTimes(['08:00', '12:00', '16:00', '20:00'])),
            'tone' => 'green',
        ],
        [
            'name' => __('admin.backup_restore.schedule_media_cdn_sync'),
            'frequency' => __('admin.backup_restore.schedule_media_cdn_sync_frequency'),
            'next' => $formatNextRun($nextWeeklyAt(\Carbon\CarbonInterface::SUNDAY, '03:00')),
            'tone' => 'amber',
        ],
        [
            'name' => __('admin.backup_restore.schedule_config_snapshot'),
            'frequency' => __('admin.backup_restore.schedule_config_snapshot_frequency'),
            'next' => $formatNextRun($nextMonthlyAt(1, '04:00')),
            'tone' => 'violet',
        ],
    ];
@endphp

@push('styles')
<style>
    .backup-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .backup-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: stretch;
        padding: 22px;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        background:
            linear-gradient(135deg, rgba(37, 99, 235, .1), transparent 44%),
            linear-gradient(90deg, rgba(34, 197, 94, .08), transparent 72%),
            var(--surface);
        overflow: hidden;
        position: relative;
    }

    .backup-header::before {
        content: '';
        position: absolute;
        inset: 0 0 auto;
        height: 2px;
        background: linear-gradient(90deg, var(--accent), var(--green), var(--amber));
    }

    .backup-title-wrap,
    .backup-run-card {
        position: relative;
        z-index: 1;
    }

    .backup-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .backup-title-copy {
        max-width: 760px;
    }

    .backup-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(34, 197, 94, .24);
        background: rgba(34, 197, 94, .09);
        color: var(--green);
        border-radius: 999px;
        padding: 6px 10px;
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        white-space: nowrap;
    }

    .backup-status-badge.is-warning {
        border-color: rgba(245, 158, 11, .26);
        background: rgba(245, 158, 11, .1);
        color: var(--amber);
    }

    .backup-status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 10px currentColor;
    }

    .backup-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .backup-meta {
        min-width: 0;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: color-mix(in srgb, var(--surface2) 88%, transparent);
    }

    .backup-meta span {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 9px;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .backup-meta strong {
        display: block;
        overflow: hidden;
        color: var(--text);
        font-size: 13px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .backup-run-card {
        width: 300px;
        display: grid;
        gap: 12px;
        align-content: center;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface);
        box-shadow: var(--shadow-sm);
    }

    .backup-run-card p {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .backup-run-card .btn-primary {
        width: 100%;
        min-height: 42px;
        font-weight: 800;
    }

    .backup-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 15px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--surface);
        font-size: 13px;
        font-weight: 700;
    }

    .backup-alert.is-success {
        border-color: rgba(34, 197, 94, .3);
        background: rgba(34, 197, 94, .08);
        color: var(--green);
    }

    .backup-alert.is-danger {
        border-color: rgba(239, 68, 68, .3);
        background: rgba(239, 68, 68, .08);
        color: var(--red);
    }

    .backup-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .backup-stat {
        min-width: 0;
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        background: var(--surface);
    }

    .backup-stat__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }

    .backup-stat__icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        background: rgba(37, 99, 235, .1);
        color: var(--accent);
    }

    .backup-stat__label {
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .backup-stat__value {
        overflow: hidden;
        color: var(--text);
        font-family: var(--font-display);
        font-size: 25px;
        font-weight: 800;
        line-height: 1.05;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .backup-stat__note {
        margin-top: 8px;
        overflow: hidden;
        color: var(--muted);
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .backup-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 18px;
        align-items: start;
    }

    .backup-stack {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .backup-schedule-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .backup-schedule-job {
        min-width: 0;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface2);
        padding: 14px;
        position: relative;
        overflow: hidden;
    }

    .backup-schedule-job::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 3px;
        background: var(--accent);
    }

    .backup-schedule-job.is-green::before {
        background: var(--green);
    }

    .backup-schedule-job.is-amber::before {
        background: var(--amber);
    }

    .backup-schedule-job.is-violet::before {
        background: var(--violet);
    }

    .backup-schedule-job__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .backup-schedule-job__name {
        color: var(--text);
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .backup-schedule-job__dot {
        width: 9px;
        height: 9px;
        flex: 0 0 auto;
        margin-top: 3px;
        border-radius: 999px;
        background: var(--accent);
        box-shadow: 0 0 12px rgba(37, 99, 235, .4);
    }

    .backup-schedule-job.is-green .backup-schedule-job__dot {
        background: var(--green);
        box-shadow: 0 0 12px rgba(34, 197, 94, .42);
    }

    .backup-schedule-job.is-amber .backup-schedule-job__dot {
        background: var(--amber);
        box-shadow: 0 0 12px rgba(245, 158, 11, .42);
    }

    .backup-schedule-job.is-violet .backup-schedule-job__dot {
        background: var(--violet);
        box-shadow: 0 0 12px rgba(139, 92, 246, .42);
    }

    .backup-schedule-job__frequency {
        color: var(--text2);
        font-size: 13px;
        font-weight: 700;
    }

    .backup-schedule-job__next {
        margin-top: 8px;
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 10px;
    }

    .backup-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .backup-panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text);
        font-size: 15px;
        font-weight: 800;
    }

    .backup-panel-title svg {
        width: 18px;
        height: 18px;
        color: var(--accent);
    }

    .backup-panel-subtitle {
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px;
    }

    .backup-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--surface2);
        color: var(--muted);
        padding: 5px 9px;
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .backup-chip.is-success {
        border-color: rgba(34, 197, 94, .25);
        background: rgba(34, 197, 94, .09);
        color: var(--green);
    }

    .backup-chip.is-danger {
        border-color: rgba(239, 68, 68, .25);
        background: rgba(239, 68, 68, .09);
        color: var(--red);
    }

    .backup-table-wrap {
        overflow-x: auto;
    }

    .backup-table {
        width: 100%;
        border-collapse: collapse;
    }

    .backup-table th {
        padding: 12px 20px;
        border-bottom: 1px solid var(--border);
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .1em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .backup-table td {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        color: var(--text2);
        font-size: 13px;
        vertical-align: middle;
    }

    .backup-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .backup-table tbody tr:hover td {
        background: color-mix(in srgb, var(--surface2) 62%, transparent);
    }

    .backup-file {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        min-width: 260px;
    }

    .backup-file__icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        background: rgba(37, 99, 235, .1);
        color: var(--accent);
    }

    .backup-file__name {
        overflow: hidden;
        color: var(--text);
        font-family: var(--font-mono);
        font-size: 11px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .backup-file__meta {
        margin-top: 3px;
        color: var(--muted);
        font-size: 11px;
    }

    .backup-actions {
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        flex-wrap: wrap;
        min-width: 255px;
    }

    .backup-action {
        min-height: 32px;
        padding: 0 10px;
        border-radius: var(--radius-sm);
        letter-spacing: .04em;
    }

    .backup-action.is-restore {
        color: var(--amber);
    }

    .backup-action.is-delete {
        color: var(--red);
    }

    .backup-empty {
        padding: 44px 20px;
        text-align: center;
    }

    .backup-empty__icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border-radius: var(--radius-md);
        background: var(--surface2);
        color: var(--muted);
    }

    .backup-empty strong {
        display: block;
        color: var(--text);
        font-size: 14px;
        margin-bottom: 4px;
    }

    .backup-empty span {
        color: var(--muted);
        font-size: 12px;
    }

    .backup-log-panel {
        align-self: start;
        position: sticky;
        top: calc(var(--topbar-h) + 18px);
    }

    .backup-log-list {
        display: grid;
        gap: 10px;
        max-height: 680px;
        overflow: auto;
        padding: 14px;
    }

    .backup-log {
        display: grid;
        grid-template-columns: 8px minmax(0, 1fr);
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface2);
    }

    .backup-log__rail {
        width: 8px;
        border-radius: 999px;
        background: var(--amber);
    }

    .backup-log.is-success .backup-log__rail {
        background: var(--green);
    }

    .backup-log.is-danger .backup-log__rail {
        background: var(--red);
    }

    .backup-log__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        min-width: 0;
    }

    .backup-log__action {
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.25;
    }

    .backup-log__status {
        flex: 0 0 auto;
        font-family: var(--font-mono);
        font-size: 9px;
        font-weight: 800;
        color: var(--amber);
    }

    .backup-log.is-success .backup-log__status {
        color: var(--green);
    }

    .backup-log.is-danger .backup-log__status {
        color: var(--red);
    }

    .backup-log__file {
        margin-top: 5px;
        overflow: hidden;
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .backup-log__message {
        margin-top: 6px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .backup-log__time {
        margin-top: 8px;
        color: var(--muted2);
        font-family: var(--font-mono);
        font-size: 9px;
    }

    .backup-modal-warning {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 10px;
        padding: 14px;
        border: 1px solid rgba(239, 68, 68, .3);
        border-radius: var(--radius-md);
        background: rgba(239, 68, 68, .08);
        color: var(--red);
        font-size: 12px;
        line-height: 1.6;
    }

    .backup-modal-file {
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface2);
    }

    #restoreFileLabel {
        color: var(--text);
        font-family: var(--font-mono);
        font-size: 11px;
        font-weight: 700;
        word-break: break-all;
    }

    #restoreSourceLabel {
        margin-top: 6px;
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 10px;
    }

    @media (max-width: 1100px) {
        .backup-header,
        .backup-workspace {
            grid-template-columns: 1fr;
        }

        .backup-schedule-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .backup-run-card {
            width: 100%;
        }

        .backup-log-panel {
            position: static;
        }
    }

    @media (max-width: 860px) {
        .backup-stats,
        .backup-meta-grid,
        .backup-schedule-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .backup-title-row,
        .backup-panel-head {
            flex-direction: column;
        }
    }

    @media (max-width: 560px) {
        .backup-header {
            padding: 18px;
        }

        .backup-stats,
        .backup-meta-grid,
        .backup-schedule-grid {
            grid-template-columns: 1fr;
        }

        .backup-actions {
            justify-content: flex-start;
            min-width: 0;
        }

        .backup-action {
            flex: 1 1 100px;
        }
    }
</style>
@endpush

@section('content')
<div class="backup-page">
    <div class="backup-header">
        <div class="backup-title-wrap">
            <div class="backup-title-row">
                <div class="backup-title-copy">
                    <div class="breadcrumb">
                        <span>{{ __('admin.backup_restore.breadcrumb_admin') }}</span>
                        <span class="breadcrumb-sep">/</span>
                        <span class="breadcrumb-current">{{ __('admin.backup_restore.breadcrumb_current') }}</span>
                    </div>
                    <h1 class="page-title">{{ __('admin.backup_restore.title') }}</h1>
                    <p class="page-subtitle">{{ __('admin.backup_restore.subtitle') }}</p>
                </div>
                <span class="backup-status-badge {{ $runningLogs > 0 ? 'is-warning' : '' }}">
                    {{ $runningLogs > 0 ? __('admin.backup_restore.job_active', ['count' => $runningLogs]) : __('admin.backup_restore.system_ready') }}
                </span>
            </div>

            <div class="backup-meta-grid">
                <div class="backup-meta">
                    <span>{{ __('admin.backup_restore.latest_local') }}</span>
                    <strong title="{{ $latestLocal['name'] ?? __('admin.backup_restore.no_local_backup_yet') }}">{{ $latestLocal['name'] ?? __('admin.backup_restore.no_local_backup_yet') }}</strong>
                </div>
                <div class="backup-meta">
                    <span>{{ __('admin.backup_restore.latest_cloud') }}</span>
                    <strong title="{{ $latestCloud['name'] ?? __('admin.backup_restore.no_cloud_backup_yet') }}">{{ $latestCloud['name'] ?? __('admin.backup_restore.no_cloud_backup_yet') }}</strong>
                </div>
                <div class="backup-meta">
                    <span>{{ __('admin.backup_restore.last_successful_action') }}</span>
                    <strong>{{ $lastSuccess ? $formatDateTime($lastSuccess->created_at) : __('admin.backup_restore.no_success_logs_yet') }}</strong>
                </div>
            </div>
        </div>

        <form class="backup-run-card" method="POST" action="{{ route('admin.backup-restore.backup') }}" id="manualBackupForm">
            @csrf
            <div>
                <div class="backup-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M5 21h14"/></svg>
                    {{ __('admin.backup_restore.run_manual_backup') }}
                </div>
                <p>{{ __('admin.backup_restore.run_manual_backup_desc') }}</p>
            </div>
            <button class="btn-primary" type="submit" id="manualBackupButton" data-loading-text="{{ __('admin.backup_restore.backup_running') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
                <span>{{ __('admin.backup_restore.backup_now') }}</span>
            </button>
        </form>
    </div>

    @foreach (['success' => 'is-success', 'error' => 'is-danger'] as $flash => $class)
        @if(session($flash))
            <div class="backup-alert {{ $class }}">
                <span>{{ $flash === 'success' ? __('admin.backup_restore.status') : __('admin.backup_restore.error') }}</span>
                <div>{{ session($flash) }}</div>
            </div>
        @endif
    @endforeach

    <div class="backup-stats">
        <div class="backup-stat">
            <div class="backup-stat__top">
                <span class="backup-stat__icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M5 7v13h14V7"/><path d="M8 7V4h8v3"/></svg></span>
                <span class="backup-stat__label">{{ __('admin.backup_restore.local_files') }}</span>
            </div>
            <div class="backup-stat__value">{{ count($localBackups) }}</div>
            <div class="backup-stat__note">{{ __('admin.backup_restore.stored_locally', ['size' => $formatBytes($localTotalBytes)]) }}</div>
        </div>
        <div class="backup-stat">
            <div class="backup-stat__top">
                <span class="backup-stat__icon" style="background:rgba(34,197,94,.1);color:var(--green);"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/></svg></span>
                <span class="backup-stat__label">{{ __('admin.backup_restore.cloud_status') }}</span>
            </div>
            <div class="backup-stat__value">{{ $googleDriveConfigured ? __('admin.backup_restore.online') : __('admin.backup_restore.offline') }}</div>
            <div class="backup-stat__note">{{ __('admin.backup_restore.files_with_size', ['count' => count($cloudBackups), 'size' => $formatBytes($cloudTotalBytes)]) }}</div>
        </div>
        <div class="backup-stat">
            <div class="backup-stat__top">
                <span class="backup-stat__icon" style="background:rgba(245,158,11,.12);color:var(--amber);"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/><path d="m8 7-5 5 5 5"/><path d="m16 7 5 5-5 5"/></svg></span>
                <span class="backup-stat__label">{{ __('admin.backup_restore.restore_guard') }}</span>
            </div>
            <div class="backup-stat__value">{{ __('admin.backup_restore.password') }}</div>
            <div class="backup-stat__note">{{ __('admin.backup_restore.super_admin_confirmation_required') }}</div>
        </div>
        <div class="backup-stat">
            <div class="backup-stat__top">
                <span class="backup-stat__icon" style="background:rgba(139,92,246,.12);color:var(--violet);"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V5a2 2 0 0 1 2-2h12v18H6a2 2 0 0 1-2-1.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg></span>
                <span class="backup-stat__label">{{ __('admin.backup_restore.audit_logs') }}</span>
            </div>
            <div class="backup-stat__value">{{ $logs->total() }}</div>
            <div class="backup-stat__note">{{ __('admin.backup_restore.newest_activity_first') }}</div>
        </div>
    </div>

    <section class="panel">
        <div class="backup-panel-head">
            <div>
                <div class="backup-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                    {{ __('admin.backup_restore.scheduled_jobs') }}
                </div>
                <div class="backup-panel-subtitle">{{ __('admin.backup_restore.scheduled_jobs_desc') }}</div>
            </div>
            <span class="backup-chip is-success">{{ __('admin.backup_restore.schedule_monitoring_enabled') }}</span>
        </div>
        <div class="backup-schedule-grid">
            @foreach($scheduledJobs as $job)
                <div class="backup-schedule-job is-{{ $job['tone'] }}">
                    <div class="backup-schedule-job__top">
                        <div class="backup-schedule-job__name">{{ $job['name'] }}</div>
                        <span class="backup-schedule-job__dot"></span>
                    </div>
                    <div class="backup-schedule-job__frequency">{{ $job['frequency'] }}</div>
                    <div class="backup-schedule-job__next">{{ __('admin.backup_restore.next_run', ['time' => $job['next']]) }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="backup-workspace">
        <div class="backup-stack">
            <section class="panel">
                <div class="backup-panel-head">
                    <div>
                        <div class="backup-panel-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M5 7v13h14V7"/><path d="M8 7V4h8v3"/></svg>
                            {{ __('admin.backup_restore.local_backups') }}
                        </div>
                        <div class="backup-panel-subtitle">{{ __('admin.backup_restore.local_backups_desc') }}</div>
                    </div>
                    <span class="backup-chip">{{ __('admin.backup_restore.files_count', ['count' => count($localBackups)]) }}</span>
                </div>
                <div class="backup-table-wrap">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.backup_restore.file') }}</th>
                                <th>{{ __('admin.backup_restore.size') }}</th>
                                <th>{{ __('admin.backup_restore.modified') }}</th>
                                <th style="text-align:right;">{{ __('admin.backup_restore.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($localBackups as $backup)
                                <tr>
                                    <td>
                                        <div class="backup-file">
                                            <span class="backup-file__icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg></span>
                                            <span>
                                                <span class="backup-file__name" title="{{ $backup['name'] }}">{{ $backup['name'] }}</span>
                                                <span class="backup-file__meta">{{ __('admin.backup_restore.local_archive') }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                    <td>{{ $backup['modified_at'] }}</td>
                                    <td>
                                        <div class="backup-actions">
                                            <a class="btn-secondary backup-action" href="{{ route('admin.backup-restore.download', $backup['name']) }}">{{ __('admin.backup_restore.download') }}</a>
                                            <button class="btn-secondary backup-action is-restore js-restore-backup" type="button" data-file-name="{{ $backup['name'] }}" data-source="local">{{ __('admin.backup_restore.restore') }}</button>
                                            <form method="POST" action="{{ route('admin.backup-restore.local.destroy', $backup['name']) }}" onsubmit="return confirmSubmit(event, @js(__('admin.backup_restore.delete_local_confirm')))">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-secondary backup-action is-delete" type="submit">{{ __('admin.backup_restore.delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="backup-empty">
                                            <span class="backup-empty__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M5 7v13h14V7"/><path d="M8 7V4h8v3"/></svg></span>
                                            <strong>{{ __('admin.backup_restore.no_local_backups_found') }}</strong>
                                            <span>{{ __('admin.backup_restore.no_local_backups_desc') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="backup-panel-head">
                    <div>
                        <div class="backup-panel-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H8a6 6 0 1 1 1.1-11.9A7 7 0 0 1 22 11.5"/><path d="M12 13v8"/><path d="m8 17 4 4 4-4"/></svg>
                            {{ __('admin.backup_restore.google_drive_backups') }}
                        </div>
                        <div class="backup-panel-subtitle">{{ __('admin.backup_restore.google_drive_backups_desc') }}</div>
                    </div>
                    <span class="backup-chip {{ $googleDriveConfigured ? 'is-success' : 'is-danger' }}">{{ $googleDriveConfigured ? __('admin.backup_restore.connected') : __('admin.backup_restore.not_configured') }}</span>
                </div>
                <div class="backup-table-wrap">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>{{ __('admin.backup_restore.file') }}</th>
                                <th>{{ __('admin.backup_restore.size') }}</th>
                                <th>{{ __('admin.backup_restore.created') }}</th>
                                <th style="text-align:right;">{{ __('admin.backup_restore.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cloudBackups as $backup)
                                <tr>
                                    <td>
                                        <div class="backup-file">
                                            <span class="backup-file__icon" style="background:rgba(34,197,94,.1);color:var(--green);"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H8a6 6 0 1 1 1.1-11.9A7 7 0 0 1 22 11.5"/><path d="M12 13v8"/><path d="m8 17 4 4 4-4"/></svg></span>
                                            <span>
                                                <span class="backup-file__name" title="{{ $backup['name'] ?? __('admin.backup_restore.unknown') }}">{{ $backup['name'] ?? __('admin.backup_restore.unknown') }}</span>
                                                <span class="backup-file__meta">{{ __('admin.backup_restore.google_drive') }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ isset($backup['size']) ? number_format(((int) $backup['size']) / 1024 / 1024, 2) . ' MB' : __('admin.backup_restore.not_available') }}</td>
                                    <td>{{ isset($backup['createdTime']) ? $formatDateTime($backup['createdTime']) : __('admin.backup_restore.not_available') }}</td>
                                    <td>
                                        <div class="backup-actions">
                                            <a class="btn-secondary backup-action" href="{{ route('admin.backup-restore.cloud.download', ['fileId' => $backup['id'], 'fileName' => $backup['name'] ?? __('admin.backup_restore.unknown')]) }}">{{ __('admin.backup_restore.download') }}</a>
                                            <button class="btn-secondary backup-action is-restore js-restore-backup" type="button" data-file-name="{{ $backup['name'] ?? __('admin.backup_restore.unknown') }}" data-source="cloud" data-file-id="{{ $backup['id'] }}">{{ __('admin.backup_restore.restore') }}</button>
                                            <form method="POST" action="{{ route('admin.backup-restore.cloud.destroy', $backup['id']) }}" onsubmit="return confirmSubmit(event, @js(__('admin.backup_restore.delete_google_drive_confirm')))">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-secondary backup-action is-delete" type="submit">{{ __('admin.backup_restore.delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="backup-empty">
                                            <span class="backup-empty__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H8a6 6 0 1 1 1.1-11.9A7 7 0 0 1 22 11.5"/><path d="M12 13v8"/><path d="m8 17 4 4 4-4"/></svg></span>
                                            <strong>{{ __('admin.backup_restore.no_google_drive_backups_found') }}</strong>
                                            <span>{{ $googleDriveConfigured ? __('admin.backup_restore.cloud_backups_after_upload') : __('admin.backup_restore.configure_google_drive') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="panel backup-log-panel">
            <div class="backup-panel-head">
                <div>
                    <div class="backup-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                        {{ __('admin.backup_restore.backup_logs') }}
                    </div>
                    <div class="backup-panel-subtitle">{{ __('admin.backup_restore.backup_logs_desc') }}</div>
                </div>
            </div>
            <div class="backup-log-list">
                @forelse($logs as $log)
                    <div class="backup-log {{ $statusClass($log->status) }}">
                        <span class="backup-log__rail"></span>
                        <div style="min-width:0;">
                            <div class="backup-log__head">
                                <div class="backup-log__action">{{ strtoupper(str_replace('_', ' ', $log->action)) }}</div>
                                <span class="backup-log__status">{{ strtoupper($log->status) }}</span>
                            </div>
                            <div class="backup-log__file" title="{{ $log->file_name ?: __('admin.backup_restore.no_file') }}">{{ $log->file_name ?: __('admin.backup_restore.no_file') }}</div>
                            <div class="backup-log__message">{{ $log->message }}</div>
                            <div class="backup-log__time">{{ $log->created_at ? $formatDateTime($log->created_at) : '' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="backup-empty">
                        <span class="backup-empty__icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg></span>
                        <strong>{{ __('admin.backup_restore.no_logs_yet') }}</strong>
                        <span>{{ __('admin.backup_restore.no_logs_desc') }}</span>
                    </div>
                @endforelse
            </div>
            <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $logs->links() }}</div>
        </aside>
    </div>
</div>

<div id="restoreModal" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-head">
            <span class="modal-title">{{ __('admin.backup_restore.confirm_restore') }}</span>
            <button type="button" onclick="closeRestoreModal()" class="modal-close">×</button>
        </div>
        <form method="POST" action="{{ route('admin.backup-restore.restore') }}" id="restoreForm">
            @csrf
            <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;">
                <input type="hidden" name="file_name" id="restoreFileName">
                <input type="hidden" name="file_id" id="restoreFileId">
                <div class="backup-modal-warning">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-md);background:rgba(239,68,68,.12);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </span>
                    <div>{{ __('admin.backup_restore.restore_warning') }}</div>
                </div>
                <div class="backup-modal-file">
                    <label class="form-label">{{ __('admin.backup_restore.backup_file') }}</label>
                    <div id="restoreFileLabel"></div>
                    <div id="restoreSourceLabel"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('admin.backup_restore.confirm_super_admin_password') }}</label>
                    <input class="form-input" type="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeRestoreModal()" class="btn-secondary">{{ __('admin.backup_restore.cancel') }}</button>
                <button type="submit" class="btn-primary" style="background:var(--red);border:none;">{{ __('admin.backup_restore.start_restore') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    const localRestoreAction = @json(route('admin.backup-restore.restore'));
    const cloudRestoreAction = @json(route('admin.backup-restore.restore.cloud'));
    const sourceGoogleDriveLabel = @json(__('admin.backup_restore.source_google_drive'));
    const sourceLocalBackupLabel = @json(__('admin.backup_restore.source_local_backup'));
    const manualBackupForm = document.getElementById('manualBackupForm');
    const manualBackupButton = document.getElementById('manualBackupButton');

    if (manualBackupForm && manualBackupButton) {
        manualBackupForm.addEventListener('submit', () => {
            manualBackupButton.disabled = true;
            manualBackupButton.style.opacity = '.72';
            manualBackupButton.style.cursor = 'wait';
            const label = manualBackupButton.querySelector('span');
            if (label) {
                label.textContent = manualBackupButton.dataset.loadingText;
            }
        });
    }

    function openRestoreModal(fileName, source = 'local', fileId = '') {
        document.getElementById('restoreForm').action = source === 'cloud' ? cloudRestoreAction : localRestoreAction;
        document.getElementById('restoreFileName').value = fileName;
        document.getElementById('restoreFileId').value = fileId;
        document.getElementById('restoreFileLabel').textContent = fileName;
        document.getElementById('restoreSourceLabel').textContent = source === 'cloud' ? sourceGoogleDriveLabel : sourceLocalBackupLabel;
        document.getElementById('restoreModal').classList.add('open');
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.remove('open');
    }

    document.querySelectorAll('.js-restore-backup').forEach((button) => {
        button.addEventListener('click', () => {
            openRestoreModal(button.dataset.fileName || '', button.dataset.source || 'local', button.dataset.fileId || '');
        });
    });

    document.getElementById('restoreModal').addEventListener('click', (event) => {
        if (event.target.id === 'restoreModal') {
            closeRestoreModal();
        }
    });
</script>
@endsection
