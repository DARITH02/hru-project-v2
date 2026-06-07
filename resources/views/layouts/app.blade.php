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
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

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
            <div class="nav-section-label">Main</div>

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
                <span class="nav-text">Dashboard</span>
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
                <span class="nav-text">Result & Grading</span>
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
                <span class="nav-text">Attendance Issues</span>
            </a>


            @if (Auth::user()->isAdmin())
                <div class="nav-section-label">Teachers</div>
                <a href="{{ route('admin.instructors') }}" data-tooltip="Instructors"
                    class="nav-link {{ request()->is('admin/instructors') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <circle cx="6" cy="5" r="2.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M1 13c0-2.76 2.24-5 5-5s5 2.24 5 5" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">Instructors</span></a>

                <a href="{{ route('admin.teacher-attendance') }}" data-tooltip="Teacher Attendance"
                    class="nav-link {{ request()->routeIs('admin.teacher-attendance') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 2.5h10v11H3z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5.5 6h5M5.5 9h3" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                            <path d="M11.5 1.5v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span><span class="nav-text">Teacher Attendance</span></a>
                <a href="{{ route('admin.teacher-attendance.scan-qr') }}" data-tooltip="Teacher QR Scan"
                    class="nav-link {{ request()->is('admin/teacher-attendance/scan-qr') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 2h4v4H2V2ZM10 2h4v4h-4V2ZM2 10h4v4H2v-4Z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M10 10h1.5v1.5H10V10ZM12.5 10H14v4h-4v-1.5h2.5V10Z" stroke="currentColor"
                                stroke-width="1.3" stroke-linecap="round" />
                        </svg></span><span class="nav-text">Teacher QR Scan</span></a>
                <a href="{{ route('admin.teacher-attendance.scan-monitor') }}" data-tooltip="Teacher Scan Monitor"
                    class="nav-link {{ request()->is('admin/teacher-attendance/scan-monitor*') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2.5 3h11v8h-11V3Z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 13h6M8 11v2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M5 7.5l1.5 1.5L11 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg></span><span class="nav-text">Scan Monitor</span></a>

                <div class="nav-section-label">Students</div>
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
                    <span class="nav-text">Overview</span>
                </a>
                <a href="{{ route('admin.students') }}" data-tooltip="Students"
                    class="nav-link {{ request()->is('admin/students') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="6" r="2.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M2 13c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">Students</span></a>
                <a href="{{ route('admin.permissions') }}" data-tooltip="Permissions"
                    class="nav-link {{ request()->is('admin/permissions') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 13v-8a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1Z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 8h6M5 11h4" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">Permissions</span></a>
                <a href="{{ route('admin.classes') }}" data-tooltip="Groups"
                    class="nav-link {{ request()->is('admin/classes') ? 'active' : '' }}"><span class="nav-icon"><svg
                            width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 13c0-2.5 1-4 4-4s4 1.5 4 4M2 5a3 3 0 0 1 6 0 3 3 0 0 1-6 0z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M10 5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1-5 0z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M12 13c0-2 1-3 3-3s3 1 3 3" stroke="currentColor" stroke-width="1.3" />
                        </svg></span><span class="nav-text">Student Groups</span></a>

                <div class="nav-section-label">Academic Setup</div>
                <a href="{{ route('admin.departments') }}" data-tooltip="Departments"
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


                    </span><span class="nav-text">Departments</span></a>
                <a href="{{ route('admin.subjects') }}"
                    class="nav-link {{ request()->is('admin/subjects') ? 'active' : '' }}" data-tooltip="Subjects">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-library-big-icon lucide-library-big">
                        <rect width="8" height="18" x="3" y="3" rx="1" />
                        <path d="M7 3v18" />
                        <path
                            d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z" />
                    </svg>
                    </span><span class="nav-text">Subjects</span></a>
                <a href="{{ route('admin.courses') }}" data-tooltip="Classes"
                    class="nav-link {{ request()->is('admin/courses') ? 'active' : '' }}"><span class="nav-icon"><svg
                            width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 3h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"
                                stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 3V2M11 3V2M1 6h14" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg></span><span class="nav-text">Academic Classes</span></a>


                <div class="nav-section-label">System</div>
                <a href="{{ route('admin.teacher-accounts') }}" data-tooltip="Accounts"
                    class="nav-link {{ request()->is('admin/teacher-accounts') ? 'active' : '' }}"><span
                        class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M1 14.5V13a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <circle cx="8" cy="5" r="3.5" stroke="currentColor"
                                stroke-width="1.3" />
                        </svg></span><span class="nav-text">Accounts</span></a>
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.settings') }}" data-tooltip="Settings"
                        class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}"><span
                            class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                                <path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor"
                                    stroke-width="1.3" />
                                <path
                                    d="M8 1v1M8 14v1M1 8h1M14 8h1M3.05 3.05l.7.7M12.25 12.25l.7.7M3.05 12.25l.7-.7M12.25 3.05l.7-.7"
                                    stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            </svg></span><span class="nav-text">Settings</span></a>
                @endif

            @endif

            @if (Auth::user()->role === 'teacher')
                <div class="nav-section-label">Academic</div>
                <a href="{{ route('teacher.attendance') }}"
                    class="nav-link {{ request()->is('teacher/attendance') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 2.5h10v11H3z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M11.5 1.5v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">Attendance</span>
                </a>
                <a href="{{ route('teacher.attendance.scan') }}"
                    class="nav-link {{ request()->is('teacher/attendance/scan') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 2h4v4H2V2ZM10 2h4v4h-4V2ZM2 10h4v4H2v-4Z" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M10 10h1.5v1.5H10V10ZM12.5 10H14v4h-4v-1.5h2.5V10Z" stroke="currentColor"
                                stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">QR Check-In</span>
                </a>
                <a href="{{ route('teacher.attendance.checkout') }}"
                    class="nav-link {{ request()->is('teacher/attendance/checkout') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h7M7 4l4 4-4 4" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13 2.5v11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">Checkout</span>
                </a>
                <a href="/teacher/reports" class="nav-link {{ request()->is('teacher/reports') ? 'active' : '' }}">
                    <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 3h12v10H2V3Z" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg></span>
                    <span class="nav-text">Reports</span>
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
                    style="border:none; background:transparent; padding:0; cursor:pointer;" title="Logout">
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
            <button id="sidebar-toggle" class="topbar-btn" title="Toggle Sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="sys-badge mobile-hidden">SYSTEM OPTIMAL</div>
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
                <div id="theme-toggle"
                    style="width:34px; height:34px; border-radius:10px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--muted2); cursor:pointer;"
                    title="Change Mode">
                    <svg id="theme-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"
                        stroke="currentColor" stroke-width="1.3">
                        <path
                            d="M8 3v1m0 8v1M3 8h1m11 0h1M4.5 4.5l.7.7m7.1 7.1.7.7M4.5 11.5l.7-.7m7.1-7.1.7-.7M8 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z" />
                    </svg>
                </div>
                <div id="notif-wrapper" style="position:relative;">
                    <div id="notif-bell"
                        style="width:34px; height:34px; border-radius:10px; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--muted2); cursor:pointer; position:relative;"
                        title="Notifications">
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
                                <span>LATEST ACTIVITY</span>
                                <span
                                    style="background:var(--accent); color:white; padding:2px 6px; border-radius:4px; font-size:8px;">LIVE</span>
                            </div>
                        </div>
                        <div id="notif-list" style="max-height:300px; overflow-y:auto; padding:5px 0;">
                            <div
                                style="padding:25px; text-align:center; color:var(--muted); font-size:10px; font-family:var(--font-mono);">
                                NO RECENT EVENTS
                            </div>
                        </div>
                        <div
                            style="padding:10px; border-top:1px solid var(--border); text-align:center; background:var(--surface3);">
                            <a href="#"
                                style="font-family:var(--font-mono); font-size:8px; color:var(--accent); font-weight:700; text-decoration:none; letter-spacing:.05em;">VIEW
                                FULL LOGS</a>
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

    <div class="toast" id="toast">Copied!</div>

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
            t.innerText = msg;
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
            item.onmouseover = () => item.style.background = 'var(--accent)08';
            item.onmouseout = () => item.style.background = 'transparent';

            const initials = act.name ? act.name.trim().split(/\s+/).map(n => n[0]).join('').substring(0, 2).toUpperCase() :
                'SYS';

            let avatarBg = 'var(--accent)18';
            let avatarBorder = 'var(--accent)30';
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
    </script>
    <style>
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
