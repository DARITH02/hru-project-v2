# Academic Attendance Management System - Project Guide

## Purpose

This document is the practical project guide for developers, admins, reviewers, and future maintainers. It explains how the system is organized, how to run it, how the main modules work, and how to extend the code safely without breaking existing behavior.

For thesis-style project explanation, diagrams, and bilingual project description, see [docs/README.md](README.md).

## Project Summary

The Academic Attendance Management System is a Laravel-based web application for university attendance and academic administration. It supports student attendance, teacher attendance, QR verification, academic setup, semester scoring, issue monitoring, exports, Telegram notifications, and role-based access control.

Main users:

- `super_admin`: full administrative control and restricted destructive actions.
- `admin`: manages academic data, attendance, reports, and teacher attendance operations.
- `teacher`: manages assigned attendance sessions, QR check-in/check-out, reports, and corrections.
- `student`: uses QR-based attendance and student-specific attendance APIs.

## Technology Stack

- Laravel 12
- PHP 8.2+
- MySQL 8
- Redis
- Nginx
- Docker Compose
- Laravel Sanctum
- Blade templates
- Vite
- Tailwind CSS
- Small React components under `resources/js/react`
- Maatwebsite Excel
- DomPDF
- Cloudinary
- Telegram Bot API

## Local URLs

When running with the provided Docker Compose setup:

| Service | URL / Port |
| --- | --- |
| Laravel app | `http://localhost:8080` |
| Alternate app mapping | `http://localhost` |
| phpMyAdmin | `http://localhost:8082` |
| MySQL host port | `3308` |
| Redis container port | `6379` |

The app redirects unauthenticated users to `/login`.

## Quick Start

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
npm install
npm run build
```

Open:

```text
http://localhost:8080
```

Useful container commands:

```bash
docker compose ps
docker compose logs -f app
docker compose exec app sh
docker compose exec app php artisan route:list
```

## Environment Notes

Important `.env` values:

```text
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
CACHE_STORE=redis
QUEUE_CONNECTION=sync
ALLOW_STUDENT_CODE_LOGIN=true
```

For phone location checks, browser geolocation usually requires HTTPS on real devices. Local network HTTP URLs such as `http://192.168.x.x:8080` may not prompt for location in some mobile browsers. For testing, use HTTPS, a tunnel, or disable the location requirement in system settings.

## Current Folder Structure

```text
app/
  Http/
    Controllers/
      Admin/
      Api/
      Auth/
    Middleware/
    Requests/
      Api/
      Auth/
  Models/
  Repositories/
  Services/
    Auth/
  Support/
    Http/
  Exports/
  Console/
  Events/
  Providers/

routes/
  web.php
  web/
    auth.php
    student.php
    teacher.php
    admin.php
  api.php
  channels.php
  console.php

resources/
  css/
  js/
    react/
      services/
  views/
    admin/
    auth/
    teacher/
    vendor/

database/
  migrations/
  seeders/
  factories/

docs/
tests/
```

## Architecture

The backend is moving toward a layered Laravel structure:

```text
Route -> Controller -> Form Request -> Service -> Repository -> Model/Database
```

Responsibilities:

- Routes define URLs, middleware, and route names.
- Controllers should stay thin and delegate business logic.
- Form Requests own validation and authorization rules.
- Services own business workflows and decisions.
- Repositories isolate reusable database lookup and persistence logic where useful.
- Models define relationships, casts, fillable fields, and local model behavior.
- `ApiResponse` centralizes JSON success/error shape for newer APIs.

Existing large controllers still contain older logic. Refactor them gradually by module, with tests and route checks after each step.

## Main Modules

### Authentication

Files:

- `app/Http/Controllers/Auth/WebAuthController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Services/Auth/AuthService.php`
- `app/Repositories/UserRepository.php`
- `app/Http/Requests/Auth/WebLoginRequest.php`
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Api/LoginRequest.php`

Features:

- Web login and registration.
- API login using Laravel Sanctum tokens.
- Email, phone, and student-code login lookup.
- Optional student-code password login controlled by config.
- Admin approval check for non-student users.
- Demo login for reviewers.

### Student Overview And Student Attendance

Files:

- `app/Http/Controllers/DashboardController.php`
- `resources/views/admin/student_overview.blade.php`
- `resources/views/student_scan.blade.php`
- `routes/web/student.php`

Important routes:

| Method | URL | Route name |
| --- | --- | --- |
| GET | `/` | redirects to `admin.students.overview` |
| GET | `/admin/students/overview` | `admin.students.overview` |
| GET | `/scan/{session_id}` | `student.scan` |
| POST | `/verify-attendance` | `student.verify` |

Purpose:

- Shows student attendance dashboard.
- Tracks active session status.
- Displays high absence students and class risk.
- Handles student QR scan verification.

### Admin Management

Files:

- `app/Http/Controllers/Admin/AdminController.php`
- `routes/web/admin.php`
- `resources/views/admin/*.blade.php`

Main pages:

- Students
- Permissions
- Student Groups
- Instructors
- Teacher Accounts
- Attendance Issues
- Results and grading
- Courses and academic classes
- Subjects
- Departments
- Settings
- Telegram bots
- Exports

Important route examples:

| Method | URL | Route name |
| --- | --- | --- |
| GET | `/admin/students` | `admin.students` |
| GET | `/admin/permissions` | `admin.permissions` |
| GET | `/admin/classes` | `admin.classes` |
| GET | `/admin/instructors` | `admin.instructors` |
| GET | `/admin/attendance-issues` | `admin.attendance-issues` |
| GET | `/admin/settings` | `admin.settings` |

### Teacher Attendance

Files:

- `app/Http/Controllers/TeacherAttendanceController.php`
- `app/Http/Controllers/Admin/TeacherAttendanceController.php`
- `app/Http/Controllers/Api/TeacherAttendanceController.php`
- `app/Http/Controllers/Api/AdminTeacherAttendanceController.php`
- `app/Services/TeacherAttendanceService.php`
- `app/Services/TeacherAttendanceNotificationService.php`
- `routes/web/teacher.php`
- `routes/web/admin.php`
- `docs/teacher-qr-attendance-workflow.md`

Important routes:

| Method | URL | Purpose |
| --- | --- | --- |
| GET | `/teacher/attendance/qr-scan` | Public teacher QR scan page |
| POST | `/teacher/attendance/qr-scan` | Submit teacher code and status |
| GET | `/admin/teacher-attendance` | Admin teacher attendance dashboard |
| GET | `/admin/teacher-attendance/scan-qr` | Admin QR scan selector |
| GET | `/admin/teacher-attendance/sessions/{session}/qr-token` | Generate/view QR token |
| GET | `/admin/teacher-attendance/scan-monitor` | Scan monitor |

Workflow summary:

1. Admin syncs teacher schedules.
2. Admin opens a session and generates QR.
3. Teacher scans QR and enters teacher code.
4. System verifies token, teacher code, date, session, and location rules.
5. Check-in can auto check in later same-subject sessions.
6. Check-out is required for the correct session.
7. Admin monitors live activity and reports.

### Academic Setup

Models:

- `Department`
- `Major`
- `ClassGroup`
- `Subject`
- `ClassRoom`
- `Teacher`
- `Student`

Purpose:

- Departments contain majors and subjects.
- Majors contain class groups and students.
- Class groups contain students.
- Classes connect teachers, subjects, schedules, and groups.
- Attendance sessions are generated or managed from class schedules.

### Reports And Exports

Files:

- `app/Exports/*.php`
- `resources/views/admin/exports/*.blade.php`
- `resources/views/pdf/semester_report.blade.php`

Supported exports include:

- Students
- Instructors
- Courses
- Subjects
- Departments
- Classes/groups
- Results
- Attendance issues
- Teacher attendance reports
- System summaries

### Telegram Integration

Files:

- `app/Http/Controllers/Admin/TelegramBotController.php`
- `app/Services/TelegramService.php`
- `app/Models/TelegramBot.php`

Purpose:

- Configure Telegram bot records.
- Set active bot.
- Sync and test bots.
- Send reports or attendance notifications.

## API Overview

Authentication:

| Method | URL | Purpose |
| --- | --- | --- |
| POST | `/api/login` | Create Sanctum token |
| GET | `/api/profile` | Current user profile |
| POST | `/api/logout` | Revoke current token |
| GET | `/api/branding` | Public app branding/settings |

Teacher attendance:

| Method | URL |
| --- | --- |
| GET | `/api/teacher/attendance/today` |
| POST | `/api/teacher/attendance/qr/check-in` |
| GET | `/api/teacher/attendance/required-checkouts` |
| POST | `/api/teacher/attendance/sessions/{session}/check-in` |
| POST | `/api/teacher/attendance/sessions/{session}/check-out` |

Admin teacher attendance:

| Method | URL |
| --- | --- |
| GET | `/api/admin/teacher-attendance/dashboard` |
| GET | `/api/admin/teacher-attendance/sessions` |
| POST | `/api/admin/teacher-attendance/sessions/{session}/qr-token` |
| PUT | `/api/admin/teacher-attendance/sessions/{session}` |

Location:

| Method | URL | Purpose |
| --- | --- | --- |
| POST | `/api/location/record` | Save device coordinates |

## Frontend Structure

The project is mostly Blade-based, with a small React area.

```text
resources/js/
  app.js
  bootstrap.js
  react/
    AdminLiveTeacherAttendanceDashboard.jsx
    TeacherCheckoutPage.jsx
    TeacherScanPage.jsx
    services/
      teacherAttendanceApi.js
```

Rules for future React work:

- Keep API calls in `resources/js/react/services`.
- Keep page components focused on rendering and state.
- Extract repeated UI into shared components when component files grow.
- Do not hard-code privileged business rules in React.
- Laravel must remain the source of truth for permissions and validation.

## Security Notes

- Protected web routes use `auth`, `demo.readonly`, and role middleware.
- Protected API routes use `auth:sanctum`.
- Login is rate-limited.
- Teacher QR public submission is rate-limited by IP and teacher code.
- Do not log passwords, tokens, QR payloads, or exact sensitive location data unnecessarily.
- Do not expose raw exception messages to API clients.
- Keep destructive actions restricted to `super_admin`.
- Demo account should remain read-only through `demo.readonly`.
- Use HTTPS for production and for mobile geolocation reliability.

## Common Commands

Laravel:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan route:list
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan view:cache
docker compose exec app php artisan test
```

Frontend:

```bash
npm install
npm run dev
npm run build
```

Docker:

```bash
docker compose up -d
docker compose up -d --build
docker compose down
docker compose logs -f app
docker compose exec app sh
```

## Testing Checklist

Run automated checks:

```bash
docker compose exec app php artisan test
npm run build
```

Manual smoke tests:

- Login page loads.
- Demo login opens an admin workspace.
- `/` redirects to student overview after authentication.
- `/admin/students/overview` loads for admin.
- Sidebar links open Students, Permissions, and Student Groups.
- Student QR scan page loads.
- Teacher public QR scan page loads.
- Teacher code check-in validates duplicate check-in rules.
- Teacher check-out validates required session and location rules.
- Admin teacher attendance QR page generates QR token.
- Admin QR refresh updates QR without full page reload.
- Reports export successfully.
- Telegram bot test sends a message when configured.
- Location prompt appears on supported HTTPS/mobile browser.

## Maintenance Conventions

Use these naming and structure conventions for new work:

- Controllers: `ModuleController`, action names as verbs, thin methods.
- Form Requests: `StoreXRequest`, `UpdateXRequest`, `LoginRequest`.
- Services: `XService` for workflows and business decisions.
- Repositories: `XRepository` only when query/persistence logic is reused or complex.
- Blade views: module folders under `resources/views/admin`, `resources/views/teacher`, etc.
- Routes: add new web routes in the correct `routes/web/*.php` module file.
- API endpoints: group by role and module in `routes/api.php`.
- React API calls: place in `resources/js/react/services`.
- Comments: add only when they explain non-obvious business rules.

## Safe Refactor Strategy

For future cleanup, use small steps:

1. Pick one module.
2. Add or update tests for current behavior.
3. Move validation into Form Requests.
4. Move business rules into Services.
5. Move repeated queries into Repositories.
6. Keep existing route URLs and names unless a migration plan exists.
7. Run route list, tests, and build.
8. Verify manually in browser.

High-risk files that should be refactored gradually:

- `app/Http/Controllers/Api/AdminController.php`
- `app/Http/Controllers/Admin/AdminController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Api/TeacherController.php`

## Troubleshooting

### Port already allocated

If MySQL or phpMyAdmin fails to start because a port is already allocated, change the host-side port in `docker-compose.yml`.

Current project ports:

- MySQL host port: `3308`
- phpMyAdmin host port: `8082`
- App host port: `8080`

### Location permission does not ask on phone

Most mobile browsers require HTTPS for geolocation. Use HTTPS, a local tunnel, or temporarily disable `require_location` in system settings for local testing.

### 429 Too Many Requests

The teacher QR public endpoint is rate-limited. Wait for the retry window or reduce repeated submissions while testing.

### Blade changes not visible

Clear and rebuild view cache:

```bash
docker compose exec app php artisan view:clear
docker compose exec app php artisan view:cache
```

### Tailwind classes not visible

Rebuild frontend assets:

```bash
npm run build
```

## Deployment Checklist

- Set production `.env`.
- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set correct `APP_URL`.
- Configure database credentials.
- Configure Redis/cache/queue.
- Configure mail if needed.
- Configure Telegram bot if used.
- Run migrations.
- Build assets.
- Cache config/routes/views.
- Use HTTPS.
- Verify file permissions for `storage` and `bootstrap/cache`.

Production commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Current Baseline Verification

At the time this guide was written, the following checks passed:

```bash
docker compose exec app php artisan test
npm run build
docker compose exec app php artisan route:list
```

Automated tests:

- Login page loads.
- Student overview requires authentication.
- Unit test suite is configured.
