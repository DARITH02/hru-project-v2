<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon"
        href="https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png"
        type="image/png" sizes="32x32" />
    <title>{{ $title ?? 'HRU · Dashboard' }}</title>
    <script>
        window.confirmAction = function(message, onConfirm, options = {}) {
            const modal = document.getElementById('appConfirmModal');
            const msgEl = document.getElementById('appConfirmMessage');
            const titleEl = document.getElementById('appConfirmTitle');
            const cancelBtn = document.getElementById('appConfirmCancel');
            const okBtn = document.getElementById('appConfirmOk');

            if (!modal || !msgEl || !titleEl || !cancelBtn || !okBtn) {
                return Promise.resolve(false);
            }

            titleEl.textContent = options.title || 'Confirm Action';
            msgEl.textContent = message || 'Continue with this action?';
            okBtn.textContent = options.confirmText || 'CONFIRM';
            cancelBtn.textContent = options.cancelText || 'CANCEL';
            modal.style.display = 'flex';

            return new Promise(resolve => {
                const finish = accepted => {
                    modal.style.display = 'none';
                    okBtn.onclick = null;
                    cancelBtn.onclick = null;
                    modal.onclick = null;
                    document.removeEventListener('keydown', onKeyDown);
                    if (accepted && typeof onConfirm === 'function') onConfirm();
                    resolve(accepted);
                };
                const onKeyDown = event => {
                    if (event.key === 'Escape') finish(false);
                };

                okBtn.onclick = () => finish(true);
                cancelBtn.onclick = () => finish(false);
                modal.onclick = event => {
                    if (event.target === modal) finish(false);
                };
                document.addEventListener('keydown', onKeyDown);
            });
        };

        window.confirmSubmit = function(event, message, options = {}) {
            event.preventDefault();
            const form = event.currentTarget;
            window.confirmAction(message, () => form.submit(), options);
            return false;
        };
    </script>

    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <div id="appConfirmModal"
        style="position:fixed;inset:0;z-index:3000;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.56);backdrop-filter:blur(4px);padding:20px">
        <div role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle"
            style="width:min(420px,100%);background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:0 24px 70px rgba(15,23,42,.28);overflow:hidden">
            <div style="padding:30px 24px 20px;text-align:center">
                <div
                    style="width:46px;height:46px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--red) 10%,transparent);color:var(--red);border:1px solid color-mix(in srgb,var(--red) 24%,transparent)">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </div>
                <div id="appConfirmTitle"
                    style="font-family:var(--font-display);font-size:16px;font-weight:800;color:var(--text);margin-bottom:8px">
                    Confirm Action</div>
                <div id="appConfirmMessage"
                    style="font-family:var(--font-mono);font-size:10px;color:var(--muted);letter-spacing:.05em;line-height:1.7">
                    Continue with this action?
                </div>
            </div>
            <div
                style="display:flex;justify-content:flex-end;gap:10px;padding:14px 18px;background:var(--surface2);border-top:1px solid var(--border)">
                <button type="button" id="appConfirmCancel"
                    style="display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:0 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text2);font-family:var(--font-mono);font-size:10px;letter-spacing:.08em;font-weight:700;cursor:pointer">
                    CANCEL
                </button>
                <button type="button" id="appConfirmOk"
                    style="display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:0 16px;border-radius:10px;border:0;background:linear-gradient(135deg,var(--red),#F87171);color:#fff;font-family:var(--font-mono);font-size:10px;letter-spacing:.08em;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(239,68,68,.24)">
                    CONFIRM
                </button>
            </div>
        </div>
    </div>

    <!-- SIDEBAR OVERLAY -->
    <div id="sidebar-overlay"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(2px); z-index:95; display:none;">
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div style="display: flex; align-items: center; gap: 10px; width: 100%;">
                @if ($logo = \App\Models\Setting::get('app_logo'))
                    <div class="brand-logo-wrap">
                        <img id="sidebar-logo-img" src="{{ $logo }}" alt="Logo">
                    </div>
                @else
                    <div class="brand-dot"></div>
                @endif
                <div class="brand-info">
                    <div class="brand-name">{{ \App\Models\Setting::get('app_name', 'ATTENDAI') }}</div>
                    <div class="brand-sub">{{ \App\Models\Setting::get('app_sub', 'MANAGEMENT SYSTEM') }}</div>
                </div>
                <!-- MOBILE CLOSE -->
                <!-- <button id="sidebar-close" class="mobile-only sidebar-close-btn" title="Close Sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button> -->
            </div>
        </div>

        <nav class="sidebar-nav">
            @if (Auth::user()->isAdmin())
                <div class="nav-section-label">{{ __('admin.sections.main') }}</div>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="11" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="18" width="7" height="3" rx="1.5" />
                        </svg>
                    </span>
                    <span class="nav-text">{{ __('admin.nav.dashboard') }}</span>
                </a>

                <a href="{{ route('admin.results') }}"
                    class="nav-link {{ request()->is('admin/results') ? 'active' : '' }}">
                    <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-graduation-cap-icon lucide-graduation-cap">
                            <path
                                d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" />
                            <path d="M22 10v6" />
                            <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.result_grading') }}</span>
                </a>


                <a href="{{ route('admin.attendance-issues') }}"
                    class="nav-link {{ request()->is('admin/attendance-issues') ? 'active' : '' }}">
                    <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-alert-triangle">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.attendance_issues') }}</span>
                </a>

                <button type="button" class="nav-link" data-ai-open style="width:100%;border:0;background:transparent;text-align:left">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <rect x="2.5" y="5" width="11" height="8" rx="2" stroke="currentColor" stroke-width="1.3" />
                            <path d="M8 2.5V5M5.5 8.5h.01M10.5 8.5h.01M5.5 11h5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="nav-text">AI Assistant</span>
                </button>


                <div class="nav-section-label">{{ __('admin.sections.teachers') }}</div>
                <a href="{{ route('admin.instructors') }}" data-tooltip="{{ __('admin.nav.instructors') }}"
                    class="nav-link {{ request()->is('admin/instructors') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <circle cx="6" cy="5" r="2.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M1 13c0-2.76 2.24-5 5-5s5 2.24 5 5" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.instructors') }}</span></a>

                <a href="{{ route('admin.teacher-attendance') }}" data-tooltip="{{ __('admin.nav.teacher_attendance') }}"
                    class="nav-link {{ request()->routeIs('admin.teacher-attendance') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 2.5h10v11H3z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5.5 6h5M5.5 9h3" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                            <path d="M11.5 1.5v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.teacher_attendance') }}</span></a>
                <a href="{{ route('admin.teacher-attendance.scan-qr') }}" data-tooltip="{{ __('admin.nav.teacher_qr_scan') }}"
                    class="nav-link {{ request()->is('admin/teacher-attendance/scan-qr') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 2h4v4H2V2ZM10 2h4v4h-4V2ZM2 10h4v4H2v-4Z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M10 10h1.5v1.5H10V10ZM12.5 10H14v4h-4v-1.5h2.5V10Z" stroke="currentColor"
                                stroke-width="1.3" stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.teacher_qr_scan') }}</span></a>
                <a href="{{ route('admin.teacher-attendance.scan-monitor') }}" data-tooltip="{{ __('admin.nav.scan_monitor') }}"
                    class="nav-link {{ request()->is('admin/teacher-attendance/scan-monitor*') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2.5 3h11v8h-11V3Z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 13h6M8 11v2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M5 7.5l1.5 1.5L11 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.scan_monitor') }}</span></a>

                <div class="nav-section-label">{{ __('admin.sections.students') }}</div>
                <a href="{{ route('admin.students.overview') }}" class="nav-link {{ request()->routeIs('admin.students.overview') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                        </svg>
                    </span>
                    <span class="nav-text">{{ __('admin.nav.overview') }}</span>
                </a>
                <a href="{{ route('admin.students') }}" data-tooltip="{{ __('admin.nav.students') }}"
                    class="nav-link {{ request()->is('admin/students') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="6" r="2.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M2 13c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.students') }}</span></a>
                <a href="{{ route('admin.permissions') }}" data-tooltip="{{ __('admin.nav.permissions') }}"
                    class="nav-link {{ request()->is('admin/permissions') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 13v-8a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1Z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 8h6M5 11h4" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.permissions') }}</span></a>
                <a href="{{ route('admin.classes') }}" data-tooltip="{{ __('admin.nav.student_groups') }}"
                    class="nav-link {{ request()->is('admin/classes') ? 'active' : '' }}"><span class="nav-icon"><svg
                            width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 13c0-2.5 1-4 4-4s4 1.5 4 4M2 5a3 3 0 0 1 6 0 3 3 0 0 1-6 0z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M10 5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1-5 0z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M12 13c0-2 1-3 3-3s3 1 3 3" stroke="currentColor" stroke-width="1.3" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.student_groups') }}</span></a>

                <div class="nav-section-label">{{ __('admin.sections.academic_setup') }}</div>
                <a href="{{ route('admin.departments') }}" data-tooltip="{{ __('admin.nav.departments') }}"
                    class="nav-link {{ request()->is('admin/departments') ? 'active' : '' }}"><span class="nav-icon">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-hotel-icon lucide-hotel">
                            <path d="M10 22v-6.57" />
                            <path d="M12 11h.01" />
                            <path d="M12 7h.01" />
                            <path d="M14 15.43V22" />
                            <path d="M15 16a5 5 0 0 0-6 0" />
                            <path d="M16 11h.01" />
                            <path d="M16 7h.01" />
                            <path d="M8 11h.01" />
                            <path d="M8 7h.01" />
                            <rect x="4" y="2" width="18" height="20" rx="2" />
                        </svg>


                    </span><span class="nav-text">{{ __('admin.nav.departments') }}</span></a>
                <a href="{{ route('admin.subjects') }}"
                    class="nav-link {{ request()->is('admin/subjects') ? 'active' : '' }}" data-tooltip="{{ __('admin.nav.subjects') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-library-big-icon lucide-library-big">
                        <rect width="8" height="18" x="3" y="3" rx="1" />
                        <path d="M7 3v18" />
                        <path
                            d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z" />
                    </svg>
                    </span><span class="nav-text">{{ __('admin.nav.subjects') }}</span></a>
                <a href="{{ route('admin.courses') }}" data-tooltip="{{ __('admin.nav.academic_classes') }}"
                    class="nav-link {{ request()->is('admin/courses') ? 'active' : '' }}"><span class="nav-icon"><svg
                            width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 3h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 3V2M11 3V2M1 6h14" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.academic_classes') }}</span></a>


                <div class="nav-section-label">{{ __('admin.sections.system') }}</div>
                <a href="{{ route('admin.teacher-accounts') }}" data-tooltip="{{ __('admin.nav.accounts') }}"
                    class="nav-link {{ request()->is('admin/teacher-accounts') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M1 14.5V13a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <circle cx="8" cy="5" r="3.5" stroke="currentColor"
                                stroke-width="1.3" />
                        </svg></span><span class="nav-text">{{ __('admin.nav.accounts') }}</span></a>
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.settings') }}" data-tooltip="{{ __('admin.nav.settings') }}"
                        class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}"><span
                            class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                                <path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor"
                                    stroke-width="1.3" />
                                <path
                                    d="M8 1v1M8 14v1M1 8h1M14 8h1M3.05 3.05l.7.7M12.25 12.25l.7.7M3.05 12.25l.7-.7M12.25 3.05l.7-.7"
                                    stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            </svg></span><span class="nav-text">{{ __('admin.nav.settings') }}</span></a>
                    <a href="{{ route('admin.backup-restore') }}" data-tooltip="{{ __('admin.nav.backup_restore') }}"
                        class="nav-link {{ request()->is('admin/backup-restore*') ? 'active' : '' }}"><span
                            class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                                <path d="M3 3.5h10v9H3z" stroke="currentColor" stroke-width="1.3" />
                                <path d="M5.5 6.5h5M5.5 9.5h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                <path d="M8 1.5v2M8 12.5v2M6.5 13.5h3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            </svg></span><span class="nav-text">{{ __('admin.nav.backup_restore') }}</span></a>
                @endif

            @endif

            @if (Auth::user()->role === 'teacher')
                <div class="nav-section-label">{{ __('admin.sections.academic') }}</div>
                <a href="{{ route('teacher.attendance') }}"
                    class="nav-link {{ request()->is('teacher/attendance') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 2.5h10v11H3z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M11.5 1.5v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.attendance') }}</span>
                </a>
                <a href="{{ route('teacher.attendance.scan') }}"
                    class="nav-link {{ request()->is('teacher/attendance/scan') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 2h4v4H2V2ZM10 2h4v4h-4V2ZM2 10h4v4H2v-4Z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M10 10h1.5v1.5H10V10ZM12.5 10H14v4h-4v-1.5h2.5V10Z" stroke="currentColor"
                                stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.qr_check_in') }}</span>
                </a>
                <a href="{{ route('teacher.attendance.checkout') }}"
                    class="nav-link {{ request()->is('teacher/attendance/checkout') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h7M7 4l4 4-4 4" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13 2.5v11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.checkout') }}</span>
                </a>
                <a href="/teacher/reports" class="nav-link {{ request()->is('teacher/reports') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 3h12v10H2V3Z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">{{ __('admin.nav.reports') }}</span>
                </a>
            @endif
        </nav>

        <div class="sidebar-profile">
            <div class="avatar" style="background:var(--accent)">
                {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div class="profile-name" style="font-size:13px; font-weight:600;">
                    {{ Auth::user()?->name ?? 'User' }}
                </div>
                <div class="profile-role"
                    style="font-family:var(--font-mono); font-size:9px; color:var(--muted); text-transform:uppercase;">
                    {{ str_replace('_', ' ', Auth::user()?->role ?? 'Guest') }}
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="action-btn"
                    style="border:none; background:transparent; padding:0; cursor:pointer;" title="{{ __('admin.logout') }}">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                        stroke-width="1.5">
                        <path d="M10 3H13a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-3M6 12l-4-4 4-4M2 8h8" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left" style="display:flex; align-items:center; gap:20px;">
            <button id="sidebar-toggle" class="topbar-btn" title="{{ __('admin.toggle_sidebar') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="sys-badge mobile-hidden">{{ __('admin.system_optimal') }}</div>
            <div class="mobile-hidden" style="height:24px; width:1px; background:var(--border);"></div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <div id="live-clock"
                    style="font-family:var(--font-mono); font-size:24px; font-weight:700; color:var(--text); letter-spacing:-0.02em;">
                    00:00:00</div>
                <div id="live-date"
                    style="font-family:var(--font-mono); font-size:10px; color:var(--muted2); text-transform:uppercase;">
                    MARCH 29, 2026</div>
            </div>
        </div>
        <div class="topbar-right" style="display:flex; align-items:center; gap:16px;">
            <div style="display:flex; align-items:center; gap:8px;">
                @php
                    $nextLocale = app()->getLocale() === 'km' ? 'en' : 'km';
                    $nextLocaleLabel = config("app.supported_locales.$nextLocale");
                @endphp
                <form class="language-switcher" action="{{ route('language.switch', $nextLocale) }}" method="POST"
                    aria-label="{{ __('admin.language') }}">
                    @csrf
                    <button type="submit" class="language-switcher__btn" title="{{ $nextLocaleLabel }}">
                        {{ $nextLocale === 'km' ? 'ខ្មែរ' : 'EN' }}
                    </button>
                </form>
                <div id="theme-toggle"
                    style="width:34px; height:34px; border-radius:10px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--muted2); cursor:pointer;"
                    title="{{ __('admin.change_mode') }}">
                    <svg id="theme-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"
                        stroke="currentColor" stroke-width="1.3">
                        <path
                            d="M8 3v1m0 8v1M3 8h1m11 0h1M4.5 4.5l.7.7m7.1 7.1.7.7M4.5 11.5l.7-.7m7.1-7.1.7-.7M8 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z" />
                    </svg>
                </div>
                <div id="notif-wrapper" style="position:relative;">
                    <div id="notif-bell"
                        style="width:34px; height:34px; border-radius:10px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--muted2); cursor:pointer; position:relative;"
                        title="{{ __('admin.notifications') }}">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M8 1a5 5 0 0 1 5 5v3.5l1.5 1.5H1.5L3 9.5V6a5 5 0 0 1 5-5Z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M6 13a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.3" />
                        </svg>
                        <div id="notif-badge"
                            style="position:absolute; top:8px; right:8px; width:6px; height:6px; background:var(--red); border-radius:50%; border:1.5px solid var(--bg); display:none; box-shadow:0 0 5px var(--red);">
                        </div>
                    </div>

                    {{-- Dropdown --}}
                    <div id="notif-dropdown"
                        style="display:none; position:absolute; top:45px; right:0; width:280px; background:var(--surface2); backdrop-filter:blur(15px); border:1px solid var(--border); border-radius:12px; z-index:100; box-shadow:0 15px 35px rgba(0,0,0,0.4); overflow:hidden;">
                        <div
                            style="padding:12px 16px; border-bottom:1px solid var(--border); background:var(--surface3);">
                            <div
                                style="font-family:var(--font-mono); font-size:9px; font-weight:800; color:var(--text); letter-spacing:.1em; display:flex; justify-content:space-between; align-items:center;">
                                <span>{{ __('admin.latest_activity') }}</span>
                                <span
                                    style="background:var(--accent); color:white; padding:2px 6px; border-radius:4px; font-size:8px;">{{ __('admin.live') }}</span>
                            </div>
                        </div>
                        <div id="notif-list" style="max-height:300px; overflow-y:auto; padding:5px 0;">
                            <div
                                style="padding:25px; text-align:center; color:var(--muted); font-size:10px; font-family:var(--font-mono);">
                                {{ __('admin.no_recent_events') }}
                            </div>
                        </div>
                        <div
                            style="padding:10px; border-top:1px solid var(--border); text-align:center; background:var(--surface3);">
                            <a href="#"
                                style="font-family:var(--font-mono); font-size:8px; color:var(--accent); font-weight:700; text-decoration:none; letter-spacing:.05em;">{{ __('admin.view_full_logs') }}</a>
                        </div>
                    </div>
                </div>
                <div class="avatar" style="width:34px; height:34px;">
                    {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="wrapper">
            @yield('content')
        </div>
    </main>

    <div class="toast" id="toast">{{ __('admin.copied') }}</div>

    @if (Auth::user()?->canUseChat())
        <button type="button" class="chat-fab" id="chatFab" title="Chat">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none">
                <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7A8.4 8.4 0 0 1 4 11.5 8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 12h.01M12 12h.01M16 12h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
            <span id="chatFabBadge" class="chat-fab-badge is-idle" aria-label="No unread chat messages"></span>
        </button>

        <div id="chatModal" class="chat-modal" aria-hidden="true">
            <section class="chat-dialog" role="dialog" aria-modal="true" aria-labelledby="chatTitle">
                <aside class="chat-sidebar-panel">
                    <div class="chat-side-head">
                        <div>
                            <div id="chatTitle" class="chat-title">Messages</div>
                            <div class="chat-subtitle">HRU ATS staff chat</div>
                        </div>
                        <button type="button" class="chat-icon-btn" id="chatClose" aria-label="Close chat">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <div class="chat-search-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="m21 21-4.3-4.3M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input id="chatSearchInput" type="search" placeholder="Search people or chats">
                    </div>

                    <div id="chatStartList" class="chat-start-list" hidden></div>
                    <div id="chatConversationList" class="chat-conversation-list">
                        <div class="chat-empty-state">Open chat to load conversations.</div>
                    </div>
                </aside>

                <main class="chat-main-panel">
                    <div id="chatWelcome" class="chat-welcome">
                        <div class="chat-welcome-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M21 11.5a8.5 8.5 0 0 1-12.3 7.6L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Select a conversation</h3>
                        <p>Chat with admins, super admins, and teachers inside ATS.</p>
                    </div>

                    <div id="chatRoom" class="chat-room" hidden>
                        <header class="chat-room-head">
                            <button type="button" class="chat-icon-btn chat-back-btn" id="chatBack" aria-label="Back to conversations">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="chat-room-avatar" id="chatRoomAvatar">?</div>
                            <div class="chat-room-meta">
                                <div id="chatRoomName" class="chat-room-name">Conversation</div>
                                <div id="chatRoomStatus" class="chat-room-status">Offline</div>
                            </div>
                            <button type="button" class="chat-icon-btn" id="chatRefresh" aria-label="Refresh chat">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 12a9 9 0 0 1-15.2 6.5M3 12A9 9 0 0 1 18.2 5.5M18 2v4h-4M6 22v-4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </header>

                        <div id="chatMessages" class="chat-messages"></div>
                        <div id="chatTyping" class="chat-typing" hidden><span></span><span></span><span></span><em>Typing</em></div>

                        <div id="chatReplyBar" class="chat-reply-bar" hidden>
                            <div>
                                <strong>Replying to</strong>
                                <span id="chatReplyText"></span>
                            </div>
                            <button type="button" id="chatReplyClear" aria-label="Cancel reply">×</button>
                        </div>

                        <div id="chatEditBar" class="chat-reply-bar" hidden>
                            <div>
                                <strong>Editing message</strong>
                                <span id="chatEditText"></span>
                            </div>
                            <button type="button" id="chatEditClear" aria-label="Cancel edit">×</button>
                        </div>

                        <div id="chatAttachPreview" class="chat-attach-preview" hidden></div>

                        <div id="chatEmojiPanel" class="chat-emoji-panel" hidden></div>

                        <form id="chatForm" class="chat-form">
                            <input id="chatFileInput" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.docx,.xlsx,.zip,.txt,image/jpeg,image/png,image/webp,application/pdf,text/plain">
                            <button type="button" class="chat-tool-btn" id="chatAttachBtn" aria-label="Attach file">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                                    <path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l8.5-8.5a4 4 0 0 1 5.7 5.7l-8.6 8.5a2 2 0 0 1-2.8-2.8l7.8-7.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button type="button" class="chat-tool-btn" id="chatEmojiBtn" aria-label="Emoji">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                    <path d="M8 14s1.3 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <textarea id="chatInput" rows="1" placeholder="Write a message..."></textarea>
                            <button type="submit" class="chat-send-btn" id="chatSendBtn" aria-label="Send message">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </main>
            </section>
        </div>

        <div id="chatLightbox" class="chat-lightbox" hidden>
            <button type="button" id="chatLightboxClose" aria-label="Close image">×</button>
            <img id="chatLightboxImage" src="" alt="Chat image preview">
        </div>

        <div id="chatReactionModal" class="chat-sheet-modal" aria-hidden="true">
            <section class="chat-sheet" role="dialog" aria-modal="true" aria-labelledby="chatReactionTitle">
                <header class="chat-sheet-head">
                    <div>
                        <strong id="chatReactionTitle">Choose reaction</strong>
                        <span id="chatReactionPreview">React to this message</span>
                    </div>
                    <button type="button" class="chat-sheet-close" data-chat-sheet-close aria-label="Close reactions">×</button>
                </header>
                <div class="chat-reaction-tabs" id="chatReactionTabs"></div>
                <div class="chat-reaction-grid" id="chatReactionGrid"></div>
            </section>
        </div>

    @endif

    @if (Auth::user()?->isAdmin())
        <button type="button" class="ai-assistant-fab" data-ai-open title="AI Assistant">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                <rect x="2.5" y="5" width="11" height="8" rx="2" stroke="currentColor" stroke-width="1.5" />
                <path d="M8 2.5V5M5.5 8.5h.01M10.5 8.5h.01M5.5 11h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>

        <div id="aiAssistantModal" class="ai-assistant-modal" aria-hidden="true">
            <div class="ai-assistant-dialog" role="dialog" aria-modal="true" aria-labelledby="aiAssistantTitle">
                <div class="ai-assistant-header">
                    <div class="ai-assistant-mark">
                        <svg width="22" height="22" viewBox="0 0 16 16" fill="none">
                            <rect x="2.5" y="5" width="11" height="8" rx="2" stroke="currentColor" stroke-width="1.4" />
                            <path d="M8 2.5V5M5.5 8.5h.01M10.5 8.5h.01M5.5 11h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div>
                        <div id="aiAssistantTitle" class="ai-assistant-title">{{ app()->getLocale() === 'km' ? 'ជំនួយការ AI វត្តមាន HRU' : 'HRU Attendance AI Assistant' }}</div>
                        <div class="ai-assistant-subtitle">{{ app()->getLocale() === 'km' ? 'ចម្លើយត្រូវបានបង្កើតតែពីទិន្នន័យវត្តមានដែលមានប៉ុណ្ណោះ។' : 'Answers are generated only from available attendance records.' }}</div>
                    </div>
                    <button type="button" class="ai-assistant-close" id="aiAssistantClose" aria-label="Close AI Assistant">
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="ai-assistant-scope">
                    <input id="aiAcademicYear" type="text" placeholder="Academic year">
                    <input id="aiSemester" type="number" min="1" max="3" placeholder="Semester">
                    <input id="aiDateFrom" type="date" title="Date from">
                    <input id="aiDateTo" type="date" title="Date to">
                    <input id="aiClassId" type="number" min="1" placeholder="Class ID">
                    <input id="aiTeacherId" type="number" min="1" placeholder="Teacher ID">
                </div>

                <div id="aiAssistantMessages" class="ai-assistant-messages">
                    <div class="ai-message ai-message-bot">
                        <div class="ai-avatar">AI</div>
                        <div class="ai-bubble">
                            <strong>{{ app()->getLocale() === 'km' ? 'សង្ខេប៖' : 'Summary:' }}</strong>
                            <p>{{ app()->getLocale() === 'km' ? 'សួរអំពីហានិភ័យសិស្ស បញ្ហាវត្តមានគ្រូ របាយការណ៍ប្រចាំខែ ឬការប្រៀបធៀបតាមដេប៉ាតឺម៉ង់។ ខ្ញុំនឹងប្រើតែទិន្នន័យក្នុងប្រព័ន្ធនេះប៉ុណ្ណោះ។' : 'Ask about student risk, teacher attendance issues, monthly reports, or department comparisons. I will use only database records available in this system.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="ai-assistant-prompts">
                    <button type="button" data-ai-prompt="{{ app()->getLocale() === 'km' ? 'តើសិស្សណាខ្លះមានហានិភ័យខ្ពស់ក្នុងឆមាសនេះ?' : 'Which students are high risk this semester?' }}">{{ app()->getLocale() === 'km' ? 'សិស្សហានិភ័យខ្ពស់' : 'High risk students' }}</button>
                    <button type="button" data-ai-prompt="{{ app()->getLocale() === 'km' ? 'តើគ្រូណាខ្លះមានបញ្ហាវត្តមាន?' : 'Which teachers have attendance issues?' }}">{{ app()->getLocale() === 'km' ? 'បញ្ហាគ្រូ' : 'Teacher issues' }}</button>
                    <button type="button" data-ai-prompt="{{ app()->getLocale() === 'km' ? 'បង្កើតសង្ខេបវត្តមានប្រចាំខែ។' : 'Generate a monthly attendance summary.' }}">{{ app()->getLocale() === 'km' ? 'សង្ខេបប្រចាំខែ' : 'Monthly summary' }}</button>
                </div>

                <form id="aiAssistantForm" class="ai-assistant-form">
                    <textarea id="aiAssistantInput" rows="1" placeholder="{{ app()->getLocale() === 'km' ? 'សួរអំពីកំណត់ត្រាវត្តមាន...' : 'Ask about attendance records...' }}"></textarea>
                    <button type="submit" id="aiAssistantSend" class="flex justify-center items-center">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M14 2 7 9M14 2l-4 12-3-5-5-3 12-4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // 🕒 SYNC TIME WITH SERVER
        const serverTimeStr = "{{ now()->toIso8601String() }}";
        const serverTime = new Date(serverTimeStr);
        const clientTime = new Date();
        const serverOffset = serverTime - clientTime;

        window.getServerTime = function() {
            return new Date(Date.now() + serverOffset);
        };

        // 🛡️ SECURITY: Global XSS Eraser
        window.escapeHTML = function(str) {
            if (!str) return "";
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        };

        const legacyTranslations = @json(is_array(trans('admin_legacy')) ? trans('admin_legacy') : []);
        const normalizeLegacyText = (value) => String(value ?? '').replace(/\s+/g, ' ').trim();
        window.__t = function(value) {
            const text = normalizeLegacyText(value);
            if (legacyTranslations[text]) return legacyTranslations[text];
            return value;
        };

        function translateLegacyNode(root = document.body) {
            if (!legacyTranslations || !Object.keys(legacyTranslations).length || !root) return;

            const translateElementAttributes = (el) => {
                if (!el || el.nodeType !== Node.ELEMENT_NODE) return;
                ['placeholder', 'title', 'aria-label'].forEach((attr) => {
                    if (!el.hasAttribute(attr)) return;
                    const current = el.getAttribute(attr);
                    const translated = window.__t(current);
                    if (translated !== current) el.setAttribute(attr, translated);
                });
            };

            const translateTextNode = (node) => {
                const parent = node.parentElement;
                if (!parent || ['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT'].includes(parent.tagName)) return;
                const original = node.nodeValue;
                const trimmed = normalizeLegacyText(original);
                if (!trimmed) return;
                const translated = window.__t(trimmed);
                if (translated === trimmed) return;
                node.nodeValue = original.replace(trimmed, translated);
            };

            if (root.nodeType === Node.TEXT_NODE) {
                translateTextNode(root);
                return;
            }

            if (root.nodeType === Node.ELEMENT_NODE) {
                translateElementAttributes(root);
            }

            const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT);
            let node;
            while ((node = walker.nextNode())) {
                if (node.nodeType === Node.TEXT_NODE) {
                    translateTextNode(node);
                } else {
                    translateElementAttributes(node);
                }
            }
        }

        if (Object.keys(legacyTranslations).length) {
            const bootLegacyTranslator = () => {
                translateLegacyNode();

                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => translateLegacyNode(node));
                        if (mutation.type === 'characterData') translateLegacyNode(mutation.target.parentElement);
                    });
                });
                observer.observe(document.body, {
                    childList: true,
                    characterData: true,
                    subtree: true,
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootLegacyTranslator, { once: true });
            } else {
                bootLegacyTranslator();
            }
        }

        function updateClock() {
            const now = window.getServerTime();
            const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            const clockEl = document.getElementById('live-clock');
            const dateEl = document.getElementById('live-date');

            if (clockEl) {
                clockEl.textContent = now.toLocaleTimeString('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }
            if (dateEl) {
                dateEl.textContent =
                    `${months[now.getMonth()]} ${String(now.getDate()).padStart(2, '0')}, ${now.getFullYear()}`;
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        function showToast(msg) {
            const t = document.getElementById('toast');
            if (!t) return;
            t.innerText = window.__t ? window.__t(msg) : msg;
            t.style.display = 'flex';
            setTimeout(() => t.style.display = 'none', 3000);
        }

        // Theme Toggle Logic
        const themeBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const sunSvg =
            `<path d="M8 3v1m0 8v1M3 8h1m11 0h1M4.5 4.5l.7.7m7.1 7.1.7.7M4.5 11.5l.7-.7m7.1-7.1.7-.7M8 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z"/>`;
        const moonSvg =
            `<path d="M12.1 11.4c-1 .6-2.2.9-3.5.9-3.6 0-6.6-2.9-6.6-6.6 0-1.3.4-2.5 1-3.5-.7.4-1.2 1-1.6 1.7C1 5.3 1 7.2 2.3 8.7 3.5 10.3 5.3 11 7.3 11c1.3 0 2.4-.3 3.4-.8 1-.5 1.7-1.3 2.1-2.2-.2.6-.5 1.1-.7 1.4z"/>`;

        function updateThemeUI(theme) {
            if (themeIcon) themeIcon.innerHTML = theme === 'light' ? moonSvg : sunSvg;
        }

        updateThemeUI(document.documentElement.getAttribute('data-theme'));

        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                let current = document.documentElement.getAttribute('data-theme');
                let next = current === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                updateThemeUI(next);
            });
        }

        // Sidebar Toggle Logic
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const body = document.body;

        // Load preference
        if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
            body.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                const isOpen = body.classList.toggle('sidebar-mobile-open');
                sidebarOverlay.style.display = isOpen ? 'block' : 'none';
                if (isOpen) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            } else {
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
            }
        }

        function closeSidebar() {
            body.classList.remove('sidebar-mobile-open');
            sidebarOverlay.style.display = 'none';
            body.style.overflow = '';
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        // 🔔 GLOBAL NOTIFICATIONS POLLING (Admin Monitoring)
        let globalLastActivityId = 0;
        async function fetchInitialActivity() {
            try {
                const res = await fetch('/api/admin/global-activity?limit=1', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.activity && data.activity.length > 0) {
                    globalLastActivityId = data.activity[0].id;
                    data.activity.forEach(addNotifItem);
                }
            } catch (e) {}
        }
        fetchInitialActivity();

        async function checkGlobalActivity() {
            if (!"{{ Auth::user()->isAdmin() }}") return;
            try {
                const res = await fetch(`/api/admin/global-activity?last_id=${globalLastActivityId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = await res.json();
                if (data.activity && data.activity.length > 0) {
                    const badge = document.getElementById('notif-badge');
                    if (badge) {
                        badge.style.display = 'block';
                        badge.style.animation = 'pulse-red 1s infinite alternate';
                    }
                    data.activity.reverse().forEach(act => {
                        if (act.id > globalLastActivityId) {
                            globalLastActivityId = act.id;
                            addNotifItem(act);
                        }
                    });
                }
            } catch (e) {}
        }

        function addNotifItem(act) {
            const list = document.getElementById('notif-list');
            if (!list) return;
            // Remove empty state
            const empty = list.querySelector('div[style*="text-align:center"]');
            if (empty) empty.remove();

            const item = document.createElement('div');
            item.style =
                `padding: 10px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: start; gap: 10px; transition: background .2s; cursor: pointer; animation: slideDown .3s ease;`;
            item.onmouseover = () => item.style.background = 'color-mix(in srgb, var(--accent) 3%, transparent)';
            item.onmouseout = () => item.style.background = 'transparent';

            const initials = act.name ? act.name.trim().split(/\s+/).map(n => n[0]).join('').substring(0, 2).toUpperCase() :
                'SYS';

            let avatarBg = 'color-mix(in srgb, var(--accent) 9%, transparent)';
            let avatarBorder = 'color-mix(in srgb, var(--accent) 19%, transparent)';
            let avatarColor = 'var(--accent)';
            let statusDot = '';

            if (act.type === 'active_session') {
                avatarBg = 'rgba(16, 185, 129, 0.15)'; // Green
                avatarBorder = 'rgba(16, 185, 129, 0.3)';
                avatarColor = '#10b981';
                statusDot =
                    `<span style="position:absolute; bottom:0; right:0; width:8px; height:8px; background:#10b981; border-radius:50%; border:1.5px solid var(--surface2); box-shadow: 0 0 5px #10b981; animation: pulse-green 1s infinite alternate;"></span>`;
            } else if (act.type === 'new_admin') {
                avatarBg = 'rgba(245, 158, 11, 0.15)'; // Amber
                avatarBorder = 'rgba(245, 158, 11, 0.3)';
                avatarColor = '#f59e0b';
                statusDot =
                    `<span style="position:absolute; bottom:0; right:0; width:8px; height:8px; background:#f59e0b; border-radius:50%; border:1.5px solid var(--surface2); box-shadow: 0 0 5px #f59e0b; animation: pulse-amber 1s infinite alternate;"></span>`;
            }

            item.innerHTML = `
                <div style="position:relative; flex-shrink:0;">
                    <div style="width:28px; height:28px; border-radius:50%; background:${avatarBg}; border:1px solid ${avatarBorder}; color:${avatarColor}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px;">${initials}</div>
                    ${statusDot}
                </div>
                <div style="flex:1">
                    <div style="font-size:11px; font-weight:700; color:var(--text); line-height:1.3">${escapeHTML(act.name)}</div>
                    <div style="font-size:9px; color:var(--muted2); margin-top:2px">${escapeHTML(act.subject)} · ${act.time}</div>
                </div>
            `;
            list.prepend(item);
            // Cap at 10 items
            if (list.children.length > 10) list.lastElementChild.remove();
        }

        // Poll every 8 seconds for global admin oversight
        if ("{{ Auth::user()->isAdmin() }}") {
            setInterval(checkGlobalActivity, 8000);
        }

        // Notification Dropdown Toggle
        const bell = document.getElementById('notif-bell');
        const dropdown = document.getElementById('notif-dropdown');
        if (bell && dropdown) {
            bell.onclick = (e) => {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';
                dropdown.style.display = isOpen ? 'none' : 'block';
                const badge = document.getElementById('notif-badge');
                if (badge) badge.style.display = 'none';
            };
            dropdown.onclick = (e) => e.stopPropagation();
        }
        document.addEventListener('click', () => {
            if (dropdown) dropdown.style.display = 'none';
        });

        window.closeTopModal = function() {
            const appConfirm = document.getElementById('appConfirmModal');
            if (appConfirm && getComputedStyle(appConfirm).display !== 'none') {
                return false;
            }

            const modalSelectors = [
                '.modal-overlay',
                '#finalModal',
                '#teacherMonitorModal',
                '#actionModal',
                '#unauthorizedModal',
                '.chat-sheet-modal',
                '.chat-lightbox'
            ];

            const visibleModals = Array.from(document.querySelectorAll(modalSelectors.join(',')))
                .filter((modal) => {
                    const style = getComputedStyle(modal);
                    return style.display !== 'none' &&
                        style.visibility !== 'hidden' &&
                        style.opacity !== '0';
                })
                .sort((a, b) => {
                    const az = parseInt(getComputedStyle(a).zIndex, 10) || 0;
                    const bz = parseInt(getComputedStyle(b).zIndex, 10) || 0;
                    if (az !== bz) return az - bz;
                    return Array.prototype.indexOf.call(document.body.querySelectorAll('*'), a) -
                        Array.prototype.indexOf.call(document.body.querySelectorAll('*'), b);
                });

            const modal = visibleModals[visibleModals.length - 1];
            if (!modal) return false;

            if (modal.classList.contains('chat-sheet-modal')) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                return true;
            }

            if (modal.id === 'chatLightbox') {
                modal.hidden = true;
                modal.style.display = '';
                const image = document.getElementById('chatLightboxImage');
                if (image) image.src = '';
                return true;
            }

            if (modal.id === 'finalModal' && typeof window.closeFinalModal === 'function') {
                window.closeFinalModal();
                return true;
            }

            if (modal.id === 'actionModal' && typeof window.closeActionModal === 'function') {
                window.closeActionModal();
                return true;
            }

            if (modal.id === 'unauthorizedModal' && typeof window.closeUnauthorizedModal === 'function') {
                window.closeUnauthorizedModal();
                return true;
            }

            modal.classList.remove('open', 'is-open');

            if (!modal.classList.contains('modal-overlay')) {
                modal.style.display = 'none';
            } else if (!modal.classList.contains('open') && getComputedStyle(modal).display !== 'none') {
                const hadInlineDisplay = modal.style.display && modal.style.display !== 'none';
                if (hadInlineDisplay) modal.style.display = 'none';
            }

            return true;
        };

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (window.closeTopModal()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        @if (Auth::user()?->canUseChat())
            const chatCurrentUser = @json(Auth::user()->only(['id', 'name', 'role']));
            const chatModal = document.getElementById('chatModal');
            const chatFab = document.getElementById('chatFab');
            const chatFabBadge = document.getElementById('chatFabBadge');
            const chatClose = document.getElementById('chatClose');
            const chatBack = document.getElementById('chatBack');
            const chatRefresh = document.getElementById('chatRefresh');
            const chatConversationList = document.getElementById('chatConversationList');
            const chatStartList = document.getElementById('chatStartList');
            const chatSearchInput = document.getElementById('chatSearchInput');
            const chatWelcome = document.getElementById('chatWelcome');
            const chatRoom = document.getElementById('chatRoom');
            const chatMessages = document.getElementById('chatMessages');
            const chatRoomName = document.getElementById('chatRoomName');
            const chatRoomStatus = document.getElementById('chatRoomStatus');
            const chatRoomAvatar = document.getElementById('chatRoomAvatar');
            const chatTyping = document.getElementById('chatTyping');
            const chatForm = document.getElementById('chatForm');
            const chatInput = document.getElementById('chatInput');
            const chatFileInput = document.getElementById('chatFileInput');
            const chatAttachBtn = document.getElementById('chatAttachBtn');
            const chatAttachPreview = document.getElementById('chatAttachPreview');
            const chatEmojiBtn = document.getElementById('chatEmojiBtn');
            const chatEmojiPanel = document.getElementById('chatEmojiPanel');
            const chatReplyBar = document.getElementById('chatReplyBar');
            const chatReplyText = document.getElementById('chatReplyText');
            const chatReplyClear = document.getElementById('chatReplyClear');
            const chatEditBar = document.getElementById('chatEditBar');
            const chatEditText = document.getElementById('chatEditText');
            const chatEditClear = document.getElementById('chatEditClear');
            const chatLightbox = document.getElementById('chatLightbox');
            const chatLightboxImage = document.getElementById('chatLightboxImage');
            const chatLightboxClose = document.getElementById('chatLightboxClose');
            const chatReactionModal = document.getElementById('chatReactionModal');
            const chatReactionTabs = document.getElementById('chatReactionTabs');
            const chatReactionGrid = document.getElementById('chatReactionGrid');
            const chatReactionPreview = document.getElementById('chatReactionPreview');
            const chatDialog = chatModal?.querySelector('.chat-dialog');

            const chatState = {
                conversations: [],
                users: [],
                activeConversation: null,
                messages: [],
                nextPageUrl: null,
                loadingOlder: false,
                selectedFiles: [],
                replyTo: null,
                editingMessage: null,
                echoChannel: null,
                typingTimer: null,
                typingUsers: new Map(),
                openedOnce: false,
                isOpen: false,
                echoSubscribed: false,
                pollTimer: null,
                activePollTimer: null,
                unreadTotal: 0,
                modalMessage: null,
                activeReactionCategory: 'Smileys',
                activeEmojiCategory: 'Smileys',
                pendingReaction: '',
            };

            const chatReactionCategories = {
                Smileys: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😋', '😛', '😜', '🤪', '😎', '🥳', '😏', '😌', '😔', '🥺', '😢', '😭', '😤', '😡', '🤯', '😳', '😱', '😮', '🤔', '🤫', '🤭', '🫡'],
                Hands: ['👍', '👎', '👏', '🙌', '🫶', '🙏', '🤝', '💪', '👌', '✌️', '🤞', '🤟', '🤘', '👋', '🤙', '🖐️', '✋', '☝️', '👆', '👇', '👈', '👉'],
                Hearts: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝'],
                Work: ['✅', '☑️', '✔️', '❌', '⚠️', '📌', '📎', '📚', '📝', '📅', '⏰', '💬', '📣', '🔔', '💡', '🎯', '🏆', '⭐', '🌟', '🔥', '✨', '🎉', '📊', '📈', '📋', '🗂️', '🧾', '💻'],
                Objects: ['🌹', '🌸', '🌼', '🌻', '🌙', '☀️', '🌈', '☁️', '⚡', '🍀', '🍎', '🍕', '☕', '🎁', '🎈', '🎓', '🏫', '🚀', '🚲', '🚗', '📱', '💎', '🔒', '🔑'],
            };

            const chatMessageSummary = (message) => {
                if (!message) return '';
                if (message.deleted_at) return 'Deleted message';
                if (message.message) return message.message;
                if (message.attachments?.length) return `${message.attachments.length} attachment${message.attachments.length > 1 ? 's' : ''}`;
                return 'Message';
            };

            const chatApi = async (url, options = {}) => {
                const headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    ...(options.headers || {}),
                };

                if (!(options.body instanceof FormData) && !headers['Content-Type']) {
                    headers['Content-Type'] = 'application/json';
                }

                const response = await fetch(url, { ...options, headers });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.success === false) {
                    throw new Error(payload.message || 'Chat request failed.');
                }

                return payload;
            };

            const chatInitials = (name) => String(name || '?')
                .trim()
                .split(/\s+/)
                .slice(0, 2)
                .map(part => part[0])
                .join('')
                .toUpperCase() || '?';

            const chatTime = (value) => {
                if (!value) return '';
                return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };

            const chatDate = (value) => {
                if (!value) return '';
                return new Date(value).toLocaleDateString([], { month: 'short', day: 'numeric' });
            };

            const chatOtherParticipants = (conversation) => (conversation?.participants || [])
                .filter(user => Number(user.id) !== Number(chatCurrentUser.id));

            const chatConversationName = (conversation) => {
                const others = chatOtherParticipants(conversation);
                if (!others.length) return 'Saved messages';
                return others.map(user => user.name).join(', ');
            };

            const chatConversationStatus = (conversation) => {
                const others = chatOtherParticipants(conversation);
                if (!others.length) return 'Private notes';
                if (others.some(user => user.online)) return 'Active now';
                const lastSeen = others.find(user => user.last_seen_at)?.last_seen_at;
                return lastSeen ? `Last seen ${chatDate(lastSeen)} ${chatTime(lastSeen)}` : 'Offline';
            };

            const chatLatestMessage = (conversation) => {
                const message = conversation?.messages?.[0];
                if (!message) return 'No messages yet';
                if (message.message) return message.message;
                if (message.attachments?.length) return `${message.attachments.length} attachment${message.attachments.length > 1 ? 's' : ''}`;
                return 'Message';
            };

            const setChatBadge = () => {
                chatState.unreadTotal = chatState.conversations.reduce((total, conversation) => total + Number(conversation.unread_messages_count || 0), 0);
                if (!chatFabBadge) return;
                if (chatState.unreadTotal > 0) {
                    chatFabBadge.classList.remove('is-idle');
                    chatFabBadge.setAttribute('aria-label', `${chatState.unreadTotal} unread chat message${chatState.unreadTotal === 1 ? '' : 's'}`);
                    chatFabBadge.textContent = chatState.unreadTotal > 99 ? '99+' : chatState.unreadTotal;
                } else {
                    chatFabBadge.classList.add('is-idle');
                    chatFabBadge.setAttribute('aria-label', 'No unread chat messages');
                    chatFabBadge.textContent = '';
                }
            };

            const renderConversations = () => {
                if (!chatConversationList) return;

                if (!chatState.conversations.length) {
                    chatConversationList.innerHTML = '<div class="chat-empty-state">No conversations yet. Start with someone below.</div>';
                    setChatBadge();
                    return;
                }

                chatConversationList.innerHTML = chatState.conversations.map(conversation => {
                    const active = chatState.activeConversation?.id === conversation.id;
                    const name = chatConversationName(conversation);
                    const status = chatConversationStatus(conversation);
                    const others = chatOtherParticipants(conversation);
                    const isOnline = others.some(user => user.online);
                    const unread = Number(conversation.unread_messages_count || 0);
                    return `
                        <button type="button" class="chat-conversation-item ${active ? 'active' : ''}" data-conversation-id="${conversation.id}">
                            <span class="chat-list-avatar">${escapeHTML(chatInitials(name))}${isOnline ? '<i></i>' : ''}</span>
                            <span class="chat-list-copy">
                                <strong>${escapeHTML(name)}</strong>
                                <em>${escapeHTML(chatLatestMessage(conversation))}</em>
                            </span>
                            <span class="chat-list-side">
                                <small>${escapeHTML(status === 'Active now' ? 'Now' : chatTime(conversation.updated_at))}</small>
                                ${unread ? `<b>${unread > 99 ? '99+' : unread}</b>` : ''}
                            </span>
                        </button>
                    `;
                }).join('');

                setChatBadge();
            };

            const renderStartUsers = () => {
                if (!chatStartList) return;

                chatStartList.hidden = false;
                if (!chatState.users.length) {
                    chatStartList.innerHTML = '<div class="chat-empty-state compact">No available chat users found.</div>';
                    return;
                }

                chatStartList.innerHTML = `
                    <div class="chat-start-heading">${chatSearchInput.value.trim() ? 'People' : 'Start a chat'}</div>
                    ${chatState.users.map(user => `
                        <button type="button" class="chat-start-user" data-user-id="${user.id}">
                            <span class="chat-list-avatar">${escapeHTML(chatInitials(user.name))}${user.online ? '<i></i>' : ''}</span>
                            <span>
                                <strong>${escapeHTML(user.name)}</strong>
                                <em>${escapeHTML(user.role?.replace('_', ' ') || '')}${user.online ? ' · Active now' : ''}</em>
                            </span>
                        </button>
                    `).join('')}
                `;
            };

            const renderAttachment = (attachment) => {
                const url = attachment.url || `/storage/${attachment.file_path}`;
                const name = attachment.file_name || 'Attachment';
                if (String(attachment.mime_type || '').startsWith('image/')) {
                    return `<button type="button" class="chat-image-attachment" data-image-url="${escapeHTML(url)}"><img src="${escapeHTML(url)}" alt="${escapeHTML(name)}"></button>`;
                }
                return `
                    <a class="chat-file-attachment" href="${escapeHTML(url)}" download>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                        <span>${escapeHTML(name)}</span>
                    </a>
                `;
            };

            const chatIsNearBottom = () => {
                if (!chatMessages) return true;
                return chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < 96;
            };

            const renderMessages = (append = false, keepPosition = false) => {
                if (!chatMessages) return;
                const oldHeight = chatMessages.scrollHeight;
                const oldTop = chatMessages.scrollTop;
                const html = chatState.messages.map(message => {
                    const mine = Number(message.sender_id) === Number(chatCurrentUser.id);
                    const deleted = !!message.deleted_at;
                    const reply = message.reply_to ? `
                        <div class="chat-reply-preview">
                            <strong>${escapeHTML(message.reply_to.sender?.name || 'Message')}</strong>
                            <span>${escapeHTML(chatMessageSummary(message.reply_to))}</span>
                        </div>
                    ` : '';
                    const attachments = (message.attachments || []).map(renderAttachment).join('');
                    const reactionGroups = (message.reactions || []).reduce((groups, reaction) => {
                        const key = reaction.reaction;
                        if (!groups[key]) groups[key] = { reaction: key, count: 0, mine: false };
                        groups[key].count += 1;
                        if (Number(reaction.user_id) === Number(chatCurrentUser.id) || Number(reaction.user?.id) === Number(chatCurrentUser.id)) {
                            groups[key].mine = true;
                        }
                        return groups;
                    }, {});
                    const reactions = Object.values(reactionGroups).length
                        ? `<div class="chat-reaction-row">${Object.values(reactionGroups).map(reaction => `<button type="button" class="${reaction.mine ? 'mine' : ''}" data-chat-action="react" title="React">${escapeHTML(reaction.reaction)}${reaction.count > 1 ? `<small>${reaction.count}</small>` : ''}</button>`).join('')}</div>`
                        : '';
                    const status = mine
                        ? `${message.pending ? 'Sending' : message.is_read ? 'Read' : 'Sent'} · ${chatTime(message.created_at)}`
                        : `${message.sender?.name || ''} · ${chatTime(message.created_at)}`;

                    return `
                        <div class="chat-message-row ${mine ? 'mine' : ''}" data-message-id="${message.id}">
                            <div class="chat-message-avatar">${escapeHTML(chatInitials(message.sender?.name || chatCurrentUser.name))}</div>
                            <div class="chat-message-stack">
                                <div class="chat-message-author">${escapeHTML(mine ? 'You' : (message.sender?.name || 'User'))}</div>
                                <div class="chat-bubble">
                                    ${deleted ? '<em class="chat-deleted-message">This message was deleted.</em>' : `
                                        ${reply}
                                        ${message.message ? `<p>${escapeHTML(message.message)}</p>` : ''}
                                        ${attachments}
                                        ${message.is_edited ? '<span class="chat-edited">(edited)</span>' : ''}
                                    `}
                                </div>
                                ${reactions}
                                <div class="chat-message-meta">
                                    <small>${escapeHTML(status)}</small>
                                    ${!deleted ? `
                                        <span class="chat-message-actions">
                                            <button type="button" data-chat-action="reply">Reply</button>
                                            <button type="button" data-chat-action="react">React</button>
                                            ${mine && message.message ? '<button type="button" data-chat-action="edit">Edit</button>' : ''}
                                            <button type="button" data-chat-action="delete-me">Delete for me</button>
                                            ${mine ? '<button type="button" data-chat-action="delete-everyone">Delete for everyone</button>' : ''}
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                if (append) {
                    chatMessages.innerHTML = html;
                    chatMessages.scrollTop = chatMessages.scrollHeight - oldHeight;
                } else if (keepPosition) {
                    chatMessages.innerHTML = html || '<div class="chat-empty-state">No messages yet. Send the first one.</div>';
                    chatMessages.scrollTop = oldTop + (chatMessages.scrollHeight - oldHeight);
                } else {
                    chatMessages.innerHTML = html || '<div class="chat-empty-state">No messages yet. Send the first one.</div>';
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            };

            const refreshConversations = async () => {
                const payload = await chatApi('/api/chat/conversations');
                chatState.conversations = payload.conversations?.data || [];
                renderConversations();
            };

            const refreshConversationsWhenOpen = () => {
                if (!chatState.isOpen) return;
                refreshConversations().catch(() => {});
                refreshActiveConversationMessages().catch(() => {});
            };

            const startChatPolling = () => {
                if (!chatState.pollTimer) {
                    chatState.pollTimer = setInterval(refreshConversationsWhenOpen, 15000);
                }
                if (!chatState.activePollTimer) {
                    chatState.activePollTimer = setInterval(() => {
                        refreshActiveConversationMessages().catch(() => {});
                    }, 3000);
                }
            };

            const stopChatPolling = () => {
                if (chatState.pollTimer) {
                    clearInterval(chatState.pollTimer);
                    chatState.pollTimer = null;
                }
                if (chatState.activePollTimer) {
                    clearInterval(chatState.activePollTimer);
                    chatState.activePollTimer = null;
                }
            };

            const subscribeChatRealtime = () => {
                if (!window.Echo || chatState.echoSubscribed) return;
                chatState.echoSubscribed = true;

                window.Echo.private(`App.Models.User.${chatCurrentUser.id}`)
                    .notification(refreshConversationsWhenOpen);

                window.Echo.join('chat.presence')
                    .here(refreshConversationsWhenOpen)
                    .joining(refreshConversationsWhenOpen)
                    .leaving(refreshConversationsWhenOpen)
                    .listen('.user.presence.changed', refreshConversationsWhenOpen);
            };

            const refreshStartUsers = async (term = '') => {
                const payload = await chatApi(`/api/chat/users${term ? `?q=${encodeURIComponent(term)}` : ''}`);
                chatState.users = payload.users || [];
                renderStartUsers();
            };

            const searchChat = async () => {
                const term = chatSearchInput.value.trim();
                const [conversations, users] = await Promise.all([
                    chatApi(`/api/chat/conversations?q=${encodeURIComponent(term)}`),
                    chatApi(`/api/chat/users${term ? `?q=${encodeURIComponent(term)}` : ''}`),
                ]);
                chatState.conversations = conversations.conversations?.data || [];
                chatState.users = users.users || [];
                renderConversations();
                renderStartUsers();
            };

            const subscribeConversation = (conversationId) => {
                if (!window.Echo) return;
                if (chatState.echoChannel) {
                    window.Echo.leave(`chat.conversation.${chatState.echoChannel}`);
                }
                chatState.echoChannel = conversationId;
                window.Echo.private(`chat.conversation.${conversationId}`)
                    .listen('.message.sent', event => {
                        if (!chatState.activeConversation || Number(event.message.conversation_id) !== Number(chatState.activeConversation.id)) return;
                        if (!chatState.messages.some(message => Number(message.id) === Number(event.message.id))) {
                            chatState.messages.push(event.message);
                            renderMessages();
                            markMessages('delivered', [event.message.id]);
                            markMessages('read', [event.message.id]);
                        }
                        refreshConversations().catch(() => {});
                    })
                    .listen('.message.updated', event => {
                        chatState.messages = chatState.messages.map(message => Number(message.id) === Number(event.message.id) ? event.message : message);
                        renderMessages();
                    })
                    .listen('.message.deleted', event => {
                        if (event.mode !== 'everyone') return;
                        chatState.messages = chatState.messages.map(message => Number(message.id) === Number(event.message_id) ? { ...message, deleted_at: new Date().toISOString() } : message);
                        renderMessages();
                    })
                    .listen('.message.reaction.updated', event => {
                        chatState.messages = chatState.messages.map(message => Number(message.id) === Number(event.message_id) ? { ...message, reactions: event.reactions } : message);
                        renderMessages();
                    })
                    .listen('.message.receipt.updated', event => {
                        if (event.status === 'read') {
                            chatState.messages = chatState.messages.map(message => event.messageIds?.includes(message.id) || event.message_ids?.includes(message.id) ? { ...message, is_read: true } : message);
                            renderMessages();
                        }
                    })
                    .listen('.user.typing', event => {
                        if (Number(event.user?.id) === Number(chatCurrentUser.id)) return;
                        if (event.typing) {
                            chatState.typingUsers.set(event.user.id, event.user.name);
                        } else {
                            chatState.typingUsers.delete(event.user.id);
                        }
                        renderTyping();
                    });
            };

            const openConversation = async (conversation) => {
                chatState.activeConversation = conversation;
                chatWelcome.hidden = true;
                chatRoom.hidden = false;
                chatDialog?.classList.add('chat-mobile-room');
                chatRoomName.textContent = chatConversationName(conversation);
                chatRoomStatus.textContent = chatConversationStatus(conversation);
                chatRoomAvatar.textContent = chatInitials(chatConversationName(conversation));
                renderConversations();
                subscribeConversation(conversation.id);

                const payload = await chatApi(`/api/chat/conversations/${conversation.id}/messages`);
                const paginator = payload.messages || {};
                chatState.messages = (paginator.data || []).reverse();
                chatState.nextPageUrl = paginator.next_page_url;
                renderMessages();
                markVisibleRead();
            };

            const refreshActiveConversationMessages = async () => {
                if (!chatState.isOpen || !chatState.activeConversation || chatState.loadingOlder) return;

                const wasNearBottom = chatIsNearBottom();
                const payload = await chatApi(`/api/chat/conversations/${chatState.activeConversation.id}/messages`);
                const paginator = payload.messages || {};
                const latest = (paginator.data || []).reverse();
                if (!latest.length && !chatState.messages.length) return;

                const byId = new Map(chatState.messages.map(message => [String(message.id), message]));
                latest.forEach(message => byId.set(String(message.id), message));
                const latestIds = new Set(latest.map(message => String(message.id)));
                const latestCreatedTimes = latest
                    .map(message => new Date(message.created_at || 0).getTime())
                    .filter(Boolean);
                const oldestLatestTime = latestCreatedTimes.length ? Math.min(...latestCreatedTimes) : 0;
                const merged = Array.from(byId.values())
                    .filter(message => {
                        if (!oldestLatestTime) return true;
                        const createdAt = new Date(message.created_at || 0).getTime();
                        return createdAt < oldestLatestTime || latestIds.has(String(message.id));
                    })
                    .sort((a, b) => {
                    const left = new Date(a.created_at || 0).getTime();
                    const right = new Date(b.created_at || 0).getTime();
                    if (left !== right) return left - right;
                    return String(a.id).localeCompare(String(b.id), undefined, { numeric: true });
                });

                const beforeIds = chatState.messages.map(message => `${message.id}:${message.updated_at || ''}:${message.deleted_at || ''}:${(message.reactions || []).length}`).join('|');
                const afterIds = merged.map(message => `${message.id}:${message.updated_at || ''}:${message.deleted_at || ''}:${(message.reactions || []).length}`).join('|');
                if (beforeIds === afterIds) return;

                chatState.messages = merged;
                chatState.nextPageUrl = paginator.next_page_url || chatState.nextPageUrl;
                renderMessages(false, !wasNearBottom);
                markVisibleRead();
                refreshConversations().catch(() => {});
            };

            const startConversation = async (userId) => {
                const payload = await chatApi('/api/chat/conversations', {
                    method: 'POST',
                    body: JSON.stringify({ participant_ids: [Number(userId)], type: 'direct' }),
                });
                chatSearchInput.value = '';
                chatState.users = [];
                renderStartUsers();
                await refreshConversations();
                await refreshStartUsers();
                await openConversation(payload.conversation);
            };

            const markMessages = async (status, ids) => {
                if (!chatState.activeConversation || !ids.length) return;
                try {
                    await chatApi(`/api/chat/conversations/${chatState.activeConversation.id}/${status}`, {
                        method: 'POST',
                        body: JSON.stringify({ message_ids: ids }),
                    });
                } catch (error) {}
            };

            const markVisibleRead = () => {
                const ids = chatState.messages
                    .filter(message => Number(message.sender_id) !== Number(chatCurrentUser.id) && !message.is_read)
                    .map(message => message.id);
                if (!ids.length) return;
                markMessages('delivered', ids);
                markMessages('read', ids);
            };

            const renderTyping = () => {
                if (!chatTyping) return;
                const names = Array.from(chatState.typingUsers.values());
                chatTyping.hidden = !names.length;
                const label = chatTyping.querySelector('em');
                if (label) label.textContent = names.length ? `${names[0]} is typing...` : 'Typing';
            };

            const sendTyping = (typing) => {
                if (!chatState.activeConversation) return;
                chatApi(`/api/chat/conversations/${chatState.activeConversation.id}/typing`, {
                    method: 'POST',
                    body: JSON.stringify({ typing }),
                }).catch(() => {});
            };

            const renderFilePreview = () => {
                if (!chatAttachPreview) return;
                if (!chatState.selectedFiles.length) {
                    chatAttachPreview.hidden = true;
                    chatAttachPreview.innerHTML = '';
                    return;
                }
                chatAttachPreview.hidden = false;
                chatAttachPreview.innerHTML = chatState.selectedFiles.map((file, index) => `
                    <span>
                        ${file.type.startsWith('image/') ? 'Image' : 'File'} · ${escapeHTML(file.name)}
                        <button type="button" data-remove-file="${index}">×</button>
                    </span>
                `).join('');
            };

            const sendMessage = async () => {
                if (!chatState.activeConversation) return;
                const text = chatInput.value.trim();

                if (chatState.editingMessage) {
                    if (!text) return;
                    await editMessage(chatState.editingMessage, text);
                    return;
                }

                if (!text && !chatState.selectedFiles.length) return;

                const tempId = `tmp-${Date.now()}`;
                const tempMessage = {
                    id: tempId,
                    conversation_id: chatState.activeConversation.id,
                    sender_id: chatCurrentUser.id,
                    sender: chatCurrentUser,
                    message: text,
                    type: chatState.selectedFiles.length ? 'mixed' : 'text',
                    created_at: new Date().toISOString(),
                    pending: true,
                    attachments: [],
                    reactions: [],
                    reply_to: chatState.replyTo,
                };
                chatState.messages.push(tempMessage);
                renderMessages();

                const formData = new FormData();
                if (text) formData.append('message', text);
                if (chatState.replyTo) formData.append('reply_to_message_id', chatState.replyTo.id);
                chatState.selectedFiles.forEach(file => formData.append('attachments[]', file));

                chatInput.value = '';
                chatState.selectedFiles = [];
                chatState.replyTo = null;
                renderFilePreview();
                renderReply();

                try {
                    const payload = await chatApi(`/api/chat/conversations/${chatState.activeConversation.id}/messages`, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    });
                    chatState.messages = chatState.messages.map(message => message.id === tempId ? payload.message : message);
                    renderMessages();
                    await refreshConversations();
                } catch (error) {
                    chatState.messages = chatState.messages.map(message => message.id === tempId ? { ...message, message: `${message.message || 'Attachment'} (failed)` } : message);
                    renderMessages();
                    showToast(error.message || 'Unable to send message.');
                }
            };

            const renderReply = () => {
                if (!chatReplyBar) return;
                if (!chatState.replyTo) {
                    chatReplyBar.hidden = true;
                    chatReplyText.textContent = '';
                    return;
                }
                chatReplyBar.hidden = false;
                chatReplyText.textContent = chatMessageSummary(chatState.replyTo);
            };

            const renderEdit = () => {
                if (!chatEditBar) return;
                if (!chatState.editingMessage) {
                    chatEditBar.hidden = true;
                    chatEditText.textContent = '';
                    if (chatAttachBtn) chatAttachBtn.disabled = false;
                    return;
                }
                chatEditBar.hidden = false;
                chatEditText.textContent = chatMessageSummary(chatState.editingMessage);
                if (chatAttachBtn) chatAttachBtn.disabled = true;
            };

            const closeChatSheets = () => {
                [chatReactionModal].forEach(modal => {
                    if (!modal) return;
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                });
                chatState.modalMessage = null;
            };

            const renderReactionPicker = () => {
                if (!chatReactionTabs || !chatReactionGrid) return;
                const categories = Object.keys(chatReactionCategories);
                chatReactionTabs.innerHTML = categories.map(category => `
                    <button type="button" class="${category === chatState.activeReactionCategory ? 'active' : ''}" data-reaction-category="${escapeHTML(category)}">${escapeHTML(category)}</button>
                `).join('');
                chatReactionGrid.innerHTML = (chatReactionCategories[chatState.activeReactionCategory] || []).map(emoji => `
                    <button type="button" data-reaction-value="${escapeHTML(emoji)}">${escapeHTML(emoji)}</button>
                `).join('');
            };

            const renderInputEmojiPanel = () => {
                if (!chatEmojiPanel) return;
                const categories = Object.keys(chatReactionCategories);
                chatEmojiPanel.innerHTML = `
                    <div class="chat-emoji-tabs">
                        ${categories.map(category => `
                            <button type="button" class="${category === chatState.activeEmojiCategory ? 'active' : ''}" data-emoji-category="${escapeHTML(category)}">${escapeHTML(category)}</button>
                        `).join('')}
                    </div>
                    <div class="chat-emoji-grid">
                        ${(chatReactionCategories[chatState.activeEmojiCategory] || []).map(emoji => `
                            <button type="button" data-emoji-value="${escapeHTML(emoji)}">${escapeHTML(emoji)}</button>
                        `).join('')}
                    </div>
                `;
            };

            const openReactionModal = (message) => {
                chatState.modalMessage = message;
                if (chatReactionPreview) chatReactionPreview.textContent = chatMessageSummary(message);
                renderReactionPicker();
                chatReactionModal?.classList.add('open');
                chatReactionModal?.setAttribute('aria-hidden', 'false');
            };

            const beginEditMessage = (message) => {
                chatState.editingMessage = message;
                chatState.replyTo = null;
                chatState.selectedFiles = [];
                chatInput.value = message.message || '';
                renderReply();
                renderEdit();
                renderFilePreview();
                chatInput?.focus();
                chatForm?.scrollIntoView({ block: 'nearest' });
            };

            const clearEditMessage = () => {
                chatState.editingMessage = null;
                chatInput.value = '';
                renderEdit();
            };

            const editMessage = async (message, next) => {
                if (!next.trim()) return;
                const payload = await chatApi(`/api/chat/messages/${message.id}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ message: next.trim() }),
                });
                chatState.messages = chatState.messages.map(row => Number(row.id) === Number(message.id) ? payload.message : row);
                chatInput.value = '';
                chatState.editingMessage = null;
                renderEdit();
                renderMessages();
            };

            const deleteMessage = async (message, mode = 'me') => {
                const accepted = await window.confirmAction(
                    mode === 'everyone' ? 'Delete this message for everyone?' : 'Delete this message from your chat?',
                    null,
                    {
                        title: 'Delete message',
                        confirmText: mode === 'everyone' ? 'DELETE FOR EVERYONE' : 'DELETE FOR ME',
                        cancelText: 'CANCEL',
                    }
                );
                if (!accepted) return;

                await chatApi(`/api/chat/messages/${message.id}`, {
                    method: 'DELETE',
                    body: JSON.stringify({ mode }),
                });
                if (mode === 'everyone') {
                    chatState.messages = chatState.messages.map(row => Number(row.id) === Number(message.id) ? { ...row, deleted_at: new Date().toISOString() } : row);
                } else {
                    chatState.messages = chatState.messages.filter(row => Number(row.id) !== Number(message.id));
                }
                if (chatState.replyTo && Number(chatState.replyTo.id) === Number(message.id)) {
                    chatState.replyTo = null;
                    renderReply();
                }
                if (chatState.editingMessage && Number(chatState.editingMessage.id) === Number(message.id)) {
                    chatState.editingMessage = null;
                    renderEdit();
                }
                renderMessages();
            };

            const reactMessage = async (message) => {
                const reaction = chatState.pendingReaction;
                if (!reaction) return;
                const payload = await chatApi(`/api/chat/messages/${message.id}/reactions`, {
                    method: 'POST',
                    body: JSON.stringify({ reaction }),
                });
                chatState.messages = chatState.messages.map(row => Number(row.id) === Number(message.id) ? payload.message : row);
                renderMessages();
                closeChatSheets();
            };

            const openChat = async () => {
                chatModal.classList.add('open');
                chatModal.setAttribute('aria-hidden', 'false');
                chatState.isOpen = true;
                subscribeChatRealtime();
                startChatPolling();
                if (!chatState.openedOnce) {
                    chatState.openedOnce = true;
                    await Promise.all([
                        refreshConversations(),
                        refreshStartUsers(),
                    ]).catch(error => showToast(error.message));
                } else {
                    refreshConversations().catch(error => showToast(error.message));
                }
                chatSearchInput?.focus();
                chatApi('/api/chat/presence', { method: 'POST', body: JSON.stringify({ online: true }) }).catch(() => {});
            };

            const closeChat = () => {
                chatModal.classList.remove('open');
                chatModal.setAttribute('aria-hidden', 'true');
                chatState.isOpen = false;
                stopChatPolling();
                chatApi('/api/chat/presence', { method: 'POST', body: JSON.stringify({ online: false }) }).catch(() => {});
            };

            chatFab?.addEventListener('click', openChat);
            chatClose?.addEventListener('click', closeChat);
            chatModal?.addEventListener('click', event => {
                if (event.target === chatModal) closeChat();
            });
            chatBack?.addEventListener('click', () => {
                chatRoom.hidden = true;
                chatWelcome.hidden = false;
                chatDialog?.classList.remove('chat-mobile-room');
                chatState.activeConversation = null;
                renderConversations();
            });
            chatRefresh?.addEventListener('click', async () => {
                if (chatState.activeConversation) await openConversation(chatState.activeConversation);
                await refreshConversations();
            });

            let chatSearchTimer = null;
            chatSearchInput?.addEventListener('input', () => {
                clearTimeout(chatSearchTimer);
                chatSearchTimer = setTimeout(() => searchChat().catch(() => {}), 260);
            });

            chatConversationList?.addEventListener('click', event => {
                const item = event.target.closest('[data-conversation-id]');
                if (!item) return;
                const conversation = chatState.conversations.find(row => Number(row.id) === Number(item.dataset.conversationId));
                if (conversation) openConversation(conversation).catch(error => showToast(error.message));
            });

            chatStartList?.addEventListener('click', event => {
                const item = event.target.closest('[data-user-id]');
                if (!item) return;
                startConversation(item.dataset.userId).catch(error => showToast(error.message));
            });

            chatMessages?.addEventListener('scroll', async () => {
                if (chatMessages.scrollTop > 48 || !chatState.nextPageUrl || chatState.loadingOlder) return;
                chatState.loadingOlder = true;
                try {
                    const payload = await chatApi(chatState.nextPageUrl);
                    const paginator = payload.messages || {};
                    chatState.messages = [...(paginator.data || []).reverse(), ...chatState.messages];
                    chatState.nextPageUrl = paginator.next_page_url;
                    renderMessages(true);
                } finally {
                    chatState.loadingOlder = false;
                }
            });

            chatMessages?.addEventListener('click', event => {
                const imageButton = event.target.closest('[data-image-url]');
                if (imageButton) {
                    chatLightboxImage.src = imageButton.dataset.imageUrl;
                    chatLightbox.hidden = false;
                    return;
                }

                const actionButton = event.target.closest('[data-chat-action]');
                if (!actionButton) return;
                const row = actionButton.closest('[data-message-id]');
                const message = chatState.messages.find(item => String(item.id) === String(row?.dataset.messageId));
                if (!message) return;
                const action = actionButton.dataset.chatAction;
                if (action === 'reply') {
                    chatState.replyTo = message;
                    renderReply();
                    chatInput?.focus();
                    chatForm?.scrollIntoView({ block: 'nearest' });
                }
                if (action === 'edit') beginEditMessage(message);
                if (action === 'delete-me') deleteMessage(message, 'me').catch(error => showToast(error.message));
                if (action === 'delete-everyone') deleteMessage(message, 'everyone').catch(error => showToast(error.message));
                if (action === 'react') openReactionModal(message);
            });

            document.querySelectorAll('[data-chat-sheet-close]').forEach(button => {
                button.addEventListener('click', closeChatSheets);
            });

            [chatReactionModal].forEach(modal => {
                modal?.addEventListener('click', event => {
                    if (event.target === modal) closeChatSheets();
                });
            });

            chatReactionTabs?.addEventListener('click', event => {
                const button = event.target.closest('[data-reaction-category]');
                if (!button) return;
                chatState.activeReactionCategory = button.dataset.reactionCategory;
                renderReactionPicker();
            });

            chatReactionGrid?.addEventListener('click', event => {
                const button = event.target.closest('[data-reaction-value]');
                if (!button || !chatState.modalMessage) return;
                chatState.pendingReaction = button.dataset.reactionValue;
                reactMessage(chatState.modalMessage)
                    .catch(error => showToast(error.message))
                    .finally(() => { chatState.pendingReaction = ''; });
            });

            chatLightboxClose?.addEventListener('click', () => {
                chatLightbox.hidden = true;
                chatLightboxImage.src = '';
            });

            chatAttachBtn?.addEventListener('click', () => chatFileInput?.click());
            chatFileInput?.addEventListener('change', () => {
                chatState.selectedFiles = Array.from(chatFileInput.files || []);
                renderFilePreview();
                chatFileInput.value = '';
            });
            chatAttachPreview?.addEventListener('click', event => {
                const button = event.target.closest('[data-remove-file]');
                if (!button) return;
                chatState.selectedFiles.splice(Number(button.dataset.removeFile), 1);
                renderFilePreview();
            });
            chatEmojiBtn?.addEventListener('click', () => {
                renderInputEmojiPanel();
                chatEmojiPanel.hidden = !chatEmojiPanel.hidden;
            });
            chatEmojiPanel?.addEventListener('click', event => {
                const categoryButton = event.target.closest('[data-emoji-category]');
                if (categoryButton) {
                    chatState.activeEmojiCategory = categoryButton.dataset.emojiCategory;
                    renderInputEmojiPanel();
                    return;
                }

                const button = event.target.closest('[data-emoji-value]');
                if (!button) return;
                chatInput.value += button.dataset.emojiValue;
                chatInput.focus();
            });
            chatReplyClear?.addEventListener('click', () => {
                chatState.replyTo = null;
                renderReply();
            });
            chatEditClear?.addEventListener('click', clearEditMessage);
            chatForm?.addEventListener('submit', event => {
                event.preventDefault();
                sendMessage();
            });
            chatInput?.addEventListener('keydown', event => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    chatForm.requestSubmit();
                }
            });
            chatInput?.addEventListener('input', () => {
                sendTyping(true);
                clearTimeout(chatState.typingTimer);
                chatState.typingTimer = setTimeout(() => sendTyping(false), 1200);
            });

            window.addEventListener('beforeunload', () => {
                if (!chatState.openedOnce) return;
                fetch('/api/chat/presence', {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ online: false }),
                }).catch(() => {});
            });
        @endif

        @if (Auth::user()?->isAdmin())
            const aiAssistantModal = document.getElementById('aiAssistantModal');
            const aiAssistantMessages = document.getElementById('aiAssistantMessages');
            const aiAssistantForm = document.getElementById('aiAssistantForm');
            const aiAssistantInput = document.getElementById('aiAssistantInput');
            const aiAssistantSend = document.getElementById('aiAssistantSend');
            const aiAssistantClose = document.getElementById('aiAssistantClose');
            const aiAnalyzeUrl = @json(route('admin.attendance-assistant.analyze'));
            const aiLocale = @json(app()->getLocale());
            const aiText = aiLocale === 'km' ? {
                answer: 'ចម្លើយ',
                summary: 'សង្ខេប',
                analysis: 'ការវិភាគ',
                riskAssessment: 'ការវាយតម្លៃហានិភ័យ',
                highRiskStudents: 'សិស្សហានិភ័យខ្ពស់',
                highRiskTeachers: 'គ្រូហានិភ័យខ្ពស់',
                recommendations: 'អនុសាសន៍',
                confidence: 'កម្រិតជឿជាក់',
                noAnswer: 'មិនមានចម្លើយទេ។',
                noSummary: 'មិនមានសេចក្តីសង្ខេបទេ។',
                noItems: 'មិនមានទិន្នន័យក្នុងប្រភេទនេះទេ។',
                unknown: 'មិនស្គាល់',
                noGroup: 'គ្មានក្រុម',
                studentsAnalyzed: 'សិស្សបានវិភាគ',
                highRiskStudentsCount: 'សិស្សហានិភ័យខ្ពស់',
                teachersAnalyzed: 'គ្រូបានវិភាគ',
                teacherIssues: 'បញ្ហាគ្រូ',
                absences: 'អវត្តមាន',
                late: 'យឺត',
                missed: 'ខកខាន',
                fallback: 'ប្រើវិភាគតាមច្បាប់ទិន្នន័យ',
                highRisk: 'ហានិភ័យខ្ពស់',
                mediumRisk: 'ហានិភ័យមធ្យម',
                lowRisk: 'ហានិភ័យទាប',
            } : {
                answer: 'Answer',
                summary: 'Summary',
                analysis: 'Analysis',
                riskAssessment: 'Risk Assessment',
                highRiskStudents: 'High Risk Students',
                highRiskTeachers: 'High Risk Teachers',
                recommendations: 'Recommendations',
                confidence: 'Confidence',
                noAnswer: 'No answer available.',
                noSummary: 'No summary available.',
                noItems: 'No items available from the current data.',
                unknown: 'Unknown',
                noGroup: 'No group',
                studentsAnalyzed: 'Students analyzed',
                highRiskStudentsCount: 'High risk students',
                teachersAnalyzed: 'Teachers analyzed',
                teacherIssues: 'Teacher issues',
                absences: 'absences',
                late: 'late',
                missed: 'missed',
                fallback: 'Database rules fallback',
                highRisk: 'High Risk',
                mediumRisk: 'Medium Risk',
                lowRisk: 'Low Risk',
            };

            function translateRisk(level) {
                if (level === 'High Risk') return aiText.highRisk;
                if (level === 'Medium Risk') return aiText.mediumRisk;
                if (level === 'Low Risk') return aiText.lowRisk;
                return level || '';
            }

            function openAiAssistant() {
                if (!aiAssistantModal) return;
                aiAssistantModal.classList.add('open');
                aiAssistantModal.setAttribute('aria-hidden', 'false');
                setTimeout(() => aiAssistantInput?.focus(), 80);
            }

            function closeAiAssistant() {
                if (!aiAssistantModal) return;
                aiAssistantModal.classList.remove('open');
                aiAssistantModal.setAttribute('aria-hidden', 'true');
            }

            function aiScopePayload() {
                const values = {
                    academic_year: document.getElementById('aiAcademicYear')?.value,
                    semester: document.getElementById('aiSemester')?.value,
                    date_from: document.getElementById('aiDateFrom')?.value,
                    date_to: document.getElementById('aiDateTo')?.value,
                    class_id: document.getElementById('aiClassId')?.value,
                    teacher_id: document.getElementById('aiTeacherId')?.value,
                };

                return Object.fromEntries(Object.entries(values).filter(([, value]) => value !== null && value !== ''));
            }

            function appendAiMessage(role, html) {
                if (!aiAssistantMessages) return null;
                const row = document.createElement('div');
                row.className = `ai-message ai-message-${role}`;
                row.innerHTML = role === 'user'
                    ? `<div class="ai-bubble">${html}</div><div class="ai-avatar">ME</div>`
                    : `<div class="ai-avatar">AI</div><div class="ai-bubble">${html}</div>`;
                aiAssistantMessages.appendChild(row);
                aiAssistantMessages.scrollTop = aiAssistantMessages.scrollHeight;
                return row;
            }

            function riskClass(level) {
                if (level === 'High Risk') return 'ai-risk-high';
                if (level === 'Medium Risk') return 'ai-risk-medium';
                return 'ai-risk-low';
            }

            function renderAiList(items) {
                if (!items || !items.length) return `<p>${escapeHTML(aiText.noItems)}</p>`;
                return `<ul>${items.map(item => `<li>${escapeHTML(item)}</li>`).join('')}</ul>`;
            }

            function renderAiPeople(rows, type) {
                if (!rows || !rows.length) return `<p>${escapeHTML(aiText.noItems)}</p>`;
                const limitedRows = rows.slice(0, 6);
                return `
                    <div class="ai-mini-table">
                        ${limitedRows.map(row => `
                            <div class="ai-mini-row">
                                <div>
                                    <strong>${escapeHTML(row.name || aiText.unknown)}</strong>
                                    <span>${type === 'student'
                                        ? `${escapeHTML(row.group || aiText.noGroup)} · ${row.absence_count || 0} ${aiText.absences} · ${row.late_count || 0} ${aiText.late}`
                                        : `${row.late_check_ins || 0} ${aiText.late} · ${row.missed_classes || 0} ${aiText.missed}`}</span>
                                </div>
                                <em>${escapeHTML(translateRisk(row.risk_level || ''))}</em>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            function renderAiResult(data) {
                const students = data.analysis?.students || {};
                const teachers = data.analysis?.teachers || {};
                const riskLevel = data.risk_assessment?.level || 'Low Risk';
                const answerMode = data.answer_mode || 'attendance';
                const modelLabel = data.agent?.used_external_ai
                    ? `${escapeHTML((data.agent.provider || 'AI').toUpperCase())} ${escapeHTML(data.agent.model || '')}`
                    : aiText.fallback;

                if (answerMode !== 'attendance') {
                    return `
                        <div class="ai-result">
                            <div class="ai-result-section ai-direct-answer">
                                <h4>${escapeHTML(aiText.answer)}</h4>
                                <p>${escapeHTML(data.ai_answer || data.summary?.overview || aiText.noAnswer)}</p>
                            </div>
                            <div class="ai-result-section">
                                <h4>${escapeHTML(aiText.confidence)}</h4>
                                <p>${escapeHTML(data.confidence || 'Low')} · ${modelLabel}</p>
                                ${data.agent?.reason ? `<p>${escapeHTML(data.agent.reason)}</p>` : ''}
                            </div>
                        </div>
                    `;
                }

                return `
                    <div class="ai-result">
                        ${data.ai_answer ? `
                            <div class="ai-result-section ai-direct-answer">
                                <h4>${escapeHTML(aiText.answer)}</h4>
                                <p>${escapeHTML(data.ai_answer)}</p>
                            </div>
                        ` : ''}
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.summary)}</h4>
                            <p>${escapeHTML(data.summary?.overview || aiText.noSummary)}</p>
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.analysis)}</h4>
                            ${data.ai_analysis ? `<p>${escapeHTML(data.ai_analysis)}</p>` : ''}
                            <div class="ai-count-grid">
                                <span>${escapeHTML(aiText.studentsAnalyzed)} <strong>${students.total_students_analyzed || 0}</strong></span>
                                <span>${escapeHTML(aiText.highRiskStudentsCount)} <strong>${students.counts?.high || 0}</strong></span>
                                <span>${escapeHTML(aiText.teachersAnalyzed)} <strong>${teachers.total_teachers_analyzed || 0}</strong></span>
                                <span>${escapeHTML(aiText.teacherIssues)} <strong>${(teachers.counts?.high || 0) + (teachers.counts?.medium || 0)}</strong></span>
                            </div>
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.riskAssessment)}</h4>
                            <div class="ai-risk-pill ${riskClass(riskLevel)}">${escapeHTML(translateRisk(riskLevel))}</div>
                            <p>${escapeHTML(data.risk_assessment?.reason || '')}</p>
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.highRiskStudents)}</h4>
                            ${renderAiPeople(students.high_risk_students || [], 'student')}
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.highRiskTeachers)}</h4>
                            ${renderAiPeople(teachers.high_risk_teachers || [], 'teacher')}
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.recommendations)}</h4>
                            ${renderAiList(data.recommendations || [])}
                        </div>
                        <div class="ai-result-section">
                            <h4>${escapeHTML(aiText.confidence)}</h4>
                            <p>${escapeHTML(data.confidence || 'Low')} · ${modelLabel}</p>
                            ${data.agent?.reason ? `<p>${escapeHTML(data.agent.reason)}</p>` : ''}
                        </div>
                    </div>
                `;
            }

            async function askAiAssistant(question) {
                const cleanQuestion = String(question || '').trim();
                if (!cleanQuestion) return;

                appendAiMessage('user', `<p>${escapeHTML(cleanQuestion)}</p>`);
                const loadingRow = appendAiMessage('bot', '<div class="ai-typing"><span></span><span></span><span></span></div>');
                if (aiAssistantSend) aiAssistantSend.disabled = true;

                try {
                    const response = await fetch(aiAnalyzeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            ...aiScopePayload(),
                            question: cleanQuestion,
                        }),
                    });
                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'AI assistant request failed.');
                    }

                    loadingRow.querySelector('.ai-bubble').innerHTML = renderAiResult(payload.data);
                } catch (error) {
                    loadingRow.querySelector('.ai-bubble').innerHTML =
                        `<strong>Summary:</strong><p>${escapeHTML(error.message || 'AI assistant request failed.')}</p>`;
                } finally {
                    if (aiAssistantSend) aiAssistantSend.disabled = false;
                    aiAssistantMessages.scrollTop = aiAssistantMessages.scrollHeight;
                }
            }

            document.querySelectorAll('[data-ai-open]').forEach(button => {
                button.addEventListener('click', openAiAssistant);
            });

            document.querySelectorAll('[data-ai-prompt]').forEach(button => {
                button.addEventListener('click', () => askAiAssistant(button.dataset.aiPrompt));
            });

            if (aiAssistantClose) aiAssistantClose.addEventListener('click', closeAiAssistant);
            if (aiAssistantModal) {
                aiAssistantModal.addEventListener('click', event => {
                    if (event.target === aiAssistantModal) closeAiAssistant();
                });
            }

            if (aiAssistantForm) {
                aiAssistantForm.addEventListener('submit', event => {
                    event.preventDefault();
                    const value = aiAssistantInput.value;
                    aiAssistantInput.value = '';
                    askAiAssistant(value);
                });
            }

            if (aiAssistantInput) {
                aiAssistantInput.addEventListener('keydown', event => {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        aiAssistantForm.requestSubmit();
                    }
                });
            }
        @endif
    </script>
    <style>
        .chat-fab {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 910;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            border: 0;
            background: var(--accent);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 18px 45px rgba(37, 99, 235, .25);
            cursor: pointer;
            transition: transform .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .chat-fab:hover {
            background: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 20px 50px rgba(37, 99, 235, .34);
        }

        .chat-fab:active {
            transform: scale(.96);
        }

        .chat-fab-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 24px;
            min-height: 24px;
            padding: 0 7px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--red);
            color: #fff;
            border: 2px solid var(--bg);
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
            box-shadow: 0 8px 20px rgba(239, 68, 68, .3);
        }

        .chat-fab-badge.is-idle {
            top: -2px;
            right: -2px;
            min-width: 14px;
            min-height: 14px;
            width: 14px;
            height: 14px;
            padding: 0;
            background: var(--emerald);
            box-shadow: none;
        }

        .chat-fab ~ .ai-assistant-fab {
            bottom: 90px;
        }

        .chat-modal {
            position: fixed;
            inset: 0;
            z-index: 2450;
            display: none;
            align-items: center;
            justify-content: flex-end;
            padding: 18px 22px;
            background: rgba(15, 23, 42, .46);
            backdrop-filter: blur(7px);
        }

        .chat-modal.open {
            display: flex;
        }

        .chat-dialog {
            width: min(980px, calc(100vw - 44px));
            height: min(720px, calc(100vh - 36px));
            display: grid;
            grid-template-columns: 330px minmax(0, 1fr);
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: 0 30px 90px rgba(0, 0, 0, .34);
            animation: chatPanelIn .24s ease-out;
        }

        @keyframes chatPanelIn {
            from { opacity: 0; transform: translateY(12px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .chat-sidebar-panel {
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border);
            background: var(--surface);
        }

        .chat-side-head,
        .chat-room-head {
            min-height: 72px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
        }

        .chat-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--text);
        }

        .chat-subtitle,
        .chat-room-status {
            margin-top: 1px;
            font-family: var(--font-mono);
            font-size: 9px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .chat-icon-btn,
        .chat-tool-btn,
        .chat-send-btn {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
        }

        .chat-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            margin-left: auto;
        }

        .chat-back-btn {
            display: none;
            margin-left: 0;
        }

        .chat-search-wrap {
            margin: 12px;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface2);
            color: var(--muted);
        }

        .chat-search-wrap input {
            min-width: 0;
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--text);
            font-size: 13px;
        }

        .chat-start-list {
            max-height: 190px;
            overflow-y: auto;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: color-mix(in srgb, var(--accent2) 5%, var(--surface));
        }

        .chat-start-heading {
            padding: 10px 14px 4px;
            color: var(--muted);
            font-family: var(--font-mono);
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .chat-conversation-list {
            flex: 1;
            overflow-y: auto;
            padding: 6px;
        }

        .chat-conversation-item,
        .chat-start-user {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--text);
            padding: 10px;
            text-align: left;
        }

        .chat-conversation-item:hover,
        .chat-start-user:hover,
        .chat-conversation-item.active {
            background: color-mix(in srgb, var(--accent) 9%, transparent);
        }

        .chat-list-avatar,
        .chat-room-avatar,
        .chat-message-avatar,
        .chat-welcome-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid color-mix(in srgb, var(--accent) 28%, transparent);
            background: linear-gradient(135deg, color-mix(in srgb, var(--accent2) 18%, var(--surface)), color-mix(in srgb, var(--green) 10%, var(--surface)));
            color: var(--accent);
            font-weight: 900;
        }

        .chat-list-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            font-size: 12px;
        }

        .chat-list-avatar i {
            position: absolute;
            right: -1px;
            bottom: -1px;
            width: 11px;
            height: 11px;
            border-radius: 999px;
            border: 2px solid var(--surface);
            background: var(--green);
        }

        .chat-list-copy,
        .chat-start-user span:last-child {
            min-width: 0;
            display: grid;
            gap: 2px;
            flex: 1;
        }

        .chat-list-copy strong,
        .chat-start-user strong,
        .chat-room-name {
            overflow: hidden;
            color: var(--text);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-list-copy em,
        .chat-start-user em {
            overflow: hidden;
            color: var(--muted);
            font-size: 11px;
            font-style: normal;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-list-side {
            display: grid;
            justify-items: end;
            gap: 5px;
            flex-shrink: 0;
        }

        .chat-list-side small {
            color: var(--muted2);
            font-size: 10px;
        }

        .chat-list-side b {
            min-width: 19px;
            height: 19px;
            padding: 0 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font-size: 10px;
        }

        .chat-main-panel {
            min-width: 0;
            min-height: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg);
        }

        .chat-welcome {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .chat-welcome-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            margin-bottom: 16px;
        }

        .chat-welcome h3 {
            margin: 0 0 6px;
            color: var(--text);
            font-size: 18px;
        }

        .chat-welcome p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .chat-room {
            min-height: 0;
            height: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-room-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            font-size: 12px;
        }

        .chat-room-meta {
            min-width: 0;
            flex: 1;
        }

        .chat-messages {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 18px 18px 28px;
            scroll-padding-bottom: 28px;
            background:
                radial-gradient(circle at 20% 0%, color-mix(in srgb, var(--accent) 8%, transparent), transparent 28%),
                linear-gradient(180deg, color-mix(in srgb, var(--surface2) 62%, transparent), var(--bg));
        }

        .chat-message-row {
            display: flex;
            align-items: flex-end;
            gap: 9px;
            margin-bottom: 13px;
        }

        .chat-message-row.mine {
            justify-content: flex-end;
        }

        .chat-message-row.mine .chat-message-avatar {
            display: none;
        }

        .chat-message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            font-size: 10px;
        }

        .chat-message-stack {
            max-width: min(76%, 520px);
            display: grid;
            gap: 4px;
        }

        .chat-message-author {
            padding: 0 4px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
        }

        .chat-message-row.mine .chat-message-author {
            text-align: right;
        }

        .chat-bubble {
            border: 1px solid var(--border);
            border-radius: 17px 17px 17px 6px;
            background: var(--surface);
            color: var(--text);
            padding: 10px 12px;
            box-shadow: var(--shadow-sm);
        }

        .chat-message-row.mine .chat-bubble {
            border-color: color-mix(in srgb, var(--accent) 65%, #000);
            border-radius: 17px 17px 6px 17px;
            background: linear-gradient(135deg, var(--accent), #0f766e);
            color: #fff;
        }

        .chat-bubble p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .chat-edited,
        .chat-deleted-message {
            display: inline-block;
            margin-top: 4px;
            font-size: 10px;
            color: currentColor;
            opacity: .7;
            font-style: normal;
        }

        .chat-reply-preview {
            margin-bottom: 7px;
            padding: 7px 9px;
            border-left: 3px solid currentColor;
            border-radius: 8px;
            background: rgba(255, 255, 255, .14);
            display: grid;
            gap: 2px;
        }

        .chat-reply-preview strong,
        .chat-reply-preview span {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-size: 11px;
        }

        .chat-message-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 3px;
            color: var(--muted);
            font-size: 10px;
        }

        .chat-message-row.mine .chat-message-meta {
            justify-content: flex-end;
        }

        .chat-message-actions {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .chat-message-meta button {
            border: 0;
            border-radius: 999px;
            background: color-mix(in srgb, var(--surface) 82%, transparent);
            color: var(--muted);
            cursor: pointer;
            padding: 4px 7px;
            font-size: 10px;
            font-weight: 700;
        }

        .chat-message-meta button:hover {
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 10%, var(--surface));
        }

        .chat-reaction-row {
            display: flex;
            gap: 3px;
        }

        .chat-message-row.mine .chat-reaction-row {
            justify-content: flex-end;
        }

        .chat-reaction-row button {
            min-width: 24px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            font-size: 13px;
        }

        .chat-reaction-row button.mine {
            border-color: color-mix(in srgb, var(--accent) 45%, transparent);
            background: color-mix(in srgb, var(--accent) 12%, var(--surface));
        }

        .chat-reaction-row small {
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
        }

        .chat-image-attachment {
            display: block;
            max-width: 260px;
            margin-top: 7px;
            overflow: hidden;
            border: 0;
            border-radius: 12px;
            background: transparent;
        }

        .chat-image-attachment img {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            display: block;
        }

        .chat-file-attachment {
            margin-top: 7px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: inherit;
            font-weight: 700;
            font-size: 12px;
        }

        .chat-typing {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 18px 9px;
            color: var(--muted);
            font-size: 11px;
        }

        .chat-typing span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--muted);
            animation: aiTyping 1s infinite ease-in-out;
        }

        .chat-typing span:nth-child(2) { animation-delay: .15s; }
        .chat-typing span:nth-child(3) { animation-delay: .3s; }

        .chat-reply-bar,
        .chat-attach-preview,
        .chat-emoji-panel {
            flex: 0 0 auto;
            border-top: 1px solid var(--border);
            background: var(--surface2);
        }

        .chat-reply-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 14px;
        }

        .chat-reply-bar div {
            min-width: 0;
            display: grid;
            gap: 2px;
        }

        .chat-reply-bar strong {
            color: var(--text);
            font-size: 11px;
        }

        .chat-reply-bar span {
            overflow: hidden;
            color: var(--muted);
            font-size: 11px;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-reply-bar button {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            font-size: 18px;
        }

        .chat-attach-preview {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 9px 14px;
        }

        .chat-attach-preview span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            max-width: 260px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--text2);
            padding: 6px 9px;
            font-size: 11px;
            white-space: nowrap;
        }

        .chat-attach-preview button {
            width: 18px;
            height: 18px;
            border: 0;
            border-radius: 999px;
            background: var(--surface3);
            color: var(--text);
        }

        .chat-emoji-panel {
            display: flex;
            flex-direction: column;
            max-height: 220px;
            overflow: hidden;
            padding: 0;
        }

        .chat-emoji-tabs {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 10px 14px 8px;
            border-bottom: 1px solid var(--border);
        }

        .chat-emoji-tabs button {
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            padding: 6px 9px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .chat-emoji-tabs button.active {
            border-color: color-mix(in srgb, var(--accent) 45%, transparent);
            background: color-mix(in srgb, var(--accent) 11%, var(--surface));
            color: var(--accent);
        }

        .chat-emoji-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 4px;
            overflow-y: auto;
            padding: 10px 14px;
        }

        .chat-emoji-grid button {
            min-width: 0;
            height: 32px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            font-size: 18px;
            cursor: pointer;
        }

        .chat-emoji-grid button:hover {
            background: var(--surface);
        }

        .chat-form {
            position: sticky;
            bottom: 0;
            z-index: 2;
            flex: 0 0 auto;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 12px 14px 14px;
            border-top: 1px solid var(--border);
            background: var(--surface);
        }

        .chat-form input[type="file"] {
            display: none;
        }

        .chat-tool-btn,
        .chat-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .chat-send-btn {
            border: 0;
            background: linear-gradient(135deg, var(--accent), #0f766e);
            color: #fff;
        }

        .chat-form textarea {
            flex: 1;
            min-width: 0;
            min-height: 40px;
            max-height: 110px;
            resize: none;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface2);
            color: var(--text);
            padding: 10px 12px;
            outline: none;
            font-size: 13px;
        }

        .chat-empty-state {
            padding: 24px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        .chat-empty-state.compact {
            padding: 14px 12px;
        }

        .chat-lightbox {
            position: fixed;
            inset: 0;
            z-index: 3200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(0, 0, 0, .78);
        }

        .chat-lightbox[hidden] {
            display: none;
        }

        .chat-lightbox img {
            max-width: min(920px, 100%);
            max-height: calc(100vh - 80px);
            border-radius: 14px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, .46);
        }

        .chat-lightbox button {
            position: absolute;
            top: 18px;
            right: 20px;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            font-size: 28px;
        }

        .chat-sheet-modal {
            position: fixed;
            inset: 0;
            z-index: 3300;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, .42);
            backdrop-filter: blur(6px);
        }

        .chat-sheet-modal.open {
            display: flex;
        }

        .chat-sheet {
            width: min(520px, 100%);
            max-height: min(680px, calc(100vh - 36px));
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--surface);
            box-shadow: 0 24px 80px rgba(0, 0, 0, .34);
            animation: chatPanelIn .2s ease-out;
        }

        .chat-sheet-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
        }

        .chat-sheet-head div {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .chat-sheet-head strong {
            color: var(--text);
            font-size: 14px;
            font-weight: 900;
        }

        .chat-sheet-head span {
            overflow: hidden;
            color: var(--muted);
            font-size: 12px;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-sheet-close {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .chat-reaction-tabs {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
        }

        .chat-reaction-tabs button {
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface2);
            color: var(--muted);
            cursor: pointer;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .chat-reaction-tabs button.active {
            border-color: color-mix(in srgb, var(--accent) 45%, transparent);
            background: color-mix(in srgb, var(--accent) 12%, var(--surface));
            color: var(--accent);
        }

        .chat-reaction-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 6px;
            overflow-y: auto;
            padding: 14px;
        }

        .chat-reaction-grid button {
            aspect-ratio: 1;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface2);
            cursor: pointer;
            font-size: 24px;
        }

        .chat-reaction-grid button:hover {
            border-color: color-mix(in srgb, var(--accent) 45%, transparent);
            background: color-mix(in srgb, var(--accent) 10%, var(--surface));
        }

        .chat-composer-sheet textarea {
            min-height: 118px;
            margin: 14px;
            resize: vertical;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface2);
            color: var(--text);
            padding: 12px;
            outline: 0;
            font-size: 13px;
            line-height: 1.45;
        }

        .chat-sheet-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 0 14px 14px;
        }

        .chat-sheet-secondary,
        .chat-sheet-primary {
            min-height: 38px;
            border-radius: 11px;
            cursor: pointer;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 900;
        }

        .chat-sheet-secondary {
            border: 1px solid var(--border);
            background: var(--surface2);
            color: var(--text2);
        }

        .chat-sheet-primary {
            border: 0;
            background: linear-gradient(135deg, var(--accent), #0f766e);
            color: #fff;
        }

        @media (max-width: 860px) {
            .chat-modal {
                padding: 0;
            }

            .chat-dialog {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
                grid-template-columns: 1fr;
            }

            .chat-main-panel {
                display: none;
            }

            .chat-dialog.chat-mobile-room .chat-sidebar-panel {
                display: none;
            }

            .chat-dialog.chat-mobile-room .chat-main-panel {
                display: flex;
            }

            .chat-back-btn {
                display: inline-flex;
            }

            .chat-message-stack {
                max-width: 82%;
            }

            .chat-emoji-grid {
                grid-template-columns: repeat(6, 1fr);
            }

            .chat-sheet-modal {
                align-items: flex-end;
                padding: 10px;
            }

            .chat-sheet {
                max-height: min(620px, calc(100vh - 20px));
                border-radius: 16px;
            }

            .chat-reaction-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        .ai-assistant-fab {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 900;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--accent) 30%, transparent);
            background: linear-gradient(135deg, var(--accent), #b8893a);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .28);
            cursor: pointer;
        }

        .ai-assistant-modal {
            position: fixed;
            inset: 0;
            z-index: 2500;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(8px);
        }

        .ai-assistant-modal.open {
            display: flex;
        }

        .ai-assistant-dialog {
            width: min(980px, 100%);
            height: min(760px, calc(100vh - 36px));
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: 0 28px 90px rgba(0, 0, 0, .35);
        }

        .ai-assistant-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
        }

        .ai-assistant-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2a1d05;
            background: linear-gradient(135deg, #c9a24a, #b8893a);
            flex-shrink: 0;
        }

        .ai-assistant-title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
        }

        .ai-assistant-subtitle {
            margin-top: 2px;
            font-family: var(--font-mono);
            font-size: 9px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .ai-assistant-close {
            margin-left: auto;
            display: flex;            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text2);
            cursor: pointer;
        }

        .ai-assistant-scope {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            padding: 12px 18px;
            border-bottom: 1px solid var(--border);
            background: color-mix(in srgb, var(--surface2) 80%, transparent);
        }

        .ai-assistant-scope input {
            min-width: 0;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            padding: 0 10px;
            font-size: 11px;
            outline: none;
        }

        .ai-assistant-messages {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            background: var(--bg);
        }

        .ai-message {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            align-items: flex-start;
        }

        .ai-message-user {
            justify-content: flex-end;
        }

        .ai-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--surface2);
            color: var(--text);
            border: 1px solid var(--border);
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 900;
        }

        .ai-message-bot .ai-avatar {
            background: color-mix(in srgb, #b8893a 16%, transparent);
            color: #b8893a;
        }

        .ai-bubble {
            width: min(760px, 100%);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            background: var(--surface);
            color: var(--text);
            font-size: 13px;
            line-height: 1.6;
        }

        .ai-message-user .ai-bubble {
            width: auto;
            max-width: min(680px, 80%);
            color: #fff;
            background: var(--accent);
            border-color: color-mix(in srgb, var(--accent) 70%, #000);
        }

        .ai-assistant-prompts {
            display: flex;
            gap: 8px;
            padding: 10px 18px;
            overflow-x: auto;
            border-top: 1px solid var(--border);
            background: var(--surface2);
        }

        .ai-assistant-prompts button {
            white-space: nowrap;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--text2);
            padding: 7px 11px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .ai-assistant-form {
            display: flex;
            gap: 10px;
            padding: 14px 18px 18px;
            border-top: 1px solid var(--border);
            background: var(--surface);
        }

        .ai-assistant-form textarea {
            flex: 1;
            min-height: 44px;
            max-height: 110px;
            resize: vertical;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--surface2);
            color: var(--text);
            padding: 12px;
            outline: none;
        }

        .ai-assistant-form button {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), #b8893a);
            color: #fff;
            cursor: pointer;
        }

        .ai-result-section {
            padding-top: 12px;
            margin-top: 12px;
            border-top: 1px solid var(--border);
        }

        .ai-direct-answer {
            padding: 12px;
            border: 1px solid color-mix(in srgb, #b8893a 28%, transparent);
            border-radius: 12px;
            background: color-mix(in srgb, #b8893a 9%, transparent);
        }

        .ai-result-section:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .ai-result-section h4 {
            margin: 0 0 8px;
            font-family: var(--font-mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--muted);
        }

        .ai-count-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .ai-count-grid span,
        .ai-mini-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface2);
            padding: 10px;
            font-size: 12px;
        }

        .ai-mini-table {
            display: grid;
            gap: 8px;
        }

        .ai-mini-row div {
            display: grid;
            gap: 2px;
        }

        .ai-mini-row span,
        .ai-mini-row em {
            font-size: 11px;
            color: var(--muted);
            font-style: normal;
        }

        .ai-risk-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            border-radius: 999px;
            padding: 0 11px;
            font-size: 11px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .ai-risk-high {
            background: rgba(239, 68, 68, .13);
            color: #ef4444;
        }

        .ai-risk-medium {
            background: rgba(245, 158, 11, .14);
            color: #f59e0b;
        }

        .ai-risk-low {
            background: rgba(16, 185, 129, .13);
            color: #10b981;
        }

        .ai-typing span {
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: 4px;
            border-radius: 50%;
            background: var(--muted);
            animation: aiTyping 1s infinite ease-in-out;
        }

        .ai-typing span:nth-child(2) {
            animation-delay: .15s;
        }

        .ai-typing span:nth-child(3) {
            animation-delay: .3s;
        }

        @keyframes aiTyping {
            0%, 80%, 100% { opacity: .25; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-3px); }
        }

        @media (max-width: 860px) {
            .ai-assistant-scope {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ai-count-grid {
                grid-template-columns: 1fr;
            }

            .ai-message-user .ai-bubble {
                max-width: 90%;
            }
        }

        @keyframes pulse-red {
            from {
                transform: scale(1);
                opacity: 1;
            }

            to {
                transform: scale(1.3);
                opacity: 0.8;
            }
        }

        @keyframes pulse-green {
            from {
                transform: scale(1);
                opacity: 1;
            }

            to {
                transform: scale(1.3);
                opacity: 0.8;
            }
        }

        @keyframes pulse-amber {
            from {
                transform: scale(1);
                opacity: 1;
            }

            to {
                transform: scale(1.3);
                opacity: 0.8;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
    @stack('scripts')
</body>

</html>
