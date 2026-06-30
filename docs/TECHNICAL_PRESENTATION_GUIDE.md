# HRU-ATMS Technical Presentation Guide

For a slide-by-slide presentation outline, see `TECHNICAL_PRESENTATION_SLIDE_STRUCTURE.md`.

## 1. Project Identity

**Project name:** HRU-ATMS, Human Resource University Academic Attendance Management System

**Project type:** Web-based academic attendance, teacher attendance, student records, document, chat, reporting, and administration platform.

**Main purpose:** Replace manual attendance and academic record workflows with a centralized digital system that supports QR check-in, role-based access, live monitoring, reports, notifications, and backup/restore operations.

**Main users:**

- **Super admin:** Full system control, destructive actions, account approval, academic setup, reports, and system settings.
- **Admin:** Daily academic management, attendance review, reports, teacher attendance monitoring, documents, and backup/restore operations.
- **Teacher:** Assigned classes, QR attendance sessions, student scores, teacher attendance check-in/check-out, correction requests, documents, and chat.
- **Student:** QR attendance check-in, attendance history, class data, and document access.

## 2. High-Level System Architecture

The system is built as a Laravel backend application with web pages, protected REST APIs, realtime broadcasting, queue jobs, scheduled jobs, and optional integration services.

```text
React Frontend / Browser / Blade Pages
              |
              v
Laravel Routes: web.php + api.php
              |
              v
Controllers + Request Validation + Middleware
              |
              v
Services + Repositories + Policies
              |
              v
Eloquent Models
              |
              v
MySQL Database
```

Supporting services:

```text
Redis       -> cache, queue, session storage in Docker
Reverb      -> realtime WebSocket broadcasting
Queue       -> background jobs such as backup, restore, cleanup, notifications
Scheduler   -> recurring attendance, chat cleanup, and backup tasks
Nginx/PHP   -> web server and PHP runtime inside Docker
phpMyAdmin  -> local database administration
```

## 3. Main Technologies Used

### Backend

| Technology | Version / Package | Purpose |
| --- | --- | --- |
| PHP | 8.2+ required, Docker uses PHP 8.3 FPM | Main backend language |
| Laravel | 12.x | Web framework, routing, controllers, models, validation, queues, scheduler |
| Laravel Sanctum | 4.x | API authentication and personal access tokens |
| Laravel Reverb | 1.5+ | WebSocket server for realtime updates |
| Eloquent ORM | Laravel built-in | Database models and relationships |
| Laravel Queues | Database or Redis | Background jobs |
| Laravel Scheduler | Laravel built-in | Recurring automated commands |
| Blade | Laravel built-in | Server-rendered web pages |
| Middleware | Custom + Laravel built-in | Role control, demo read-only mode, security headers, locale, maintenance |
| Policies | Laravel built-in | Chat conversation and message authorization |

### Database and Storage

| Technology | Purpose |
| --- | --- |
| MySQL 8.0 | Main relational database |
| Redis 7 | Cache, queue, and session storage in Docker |
| Laravel migrations | Versioned database schema |
| Local filesystem storage | Uploaded documents, generated files, backups |
| Google Drive integration | Optional cloud backup destination |
| Cloudinary Laravel | Optional media/file cloud integration |

### Frontend

The project contains backend Blade pages and also connects to a separate frontend application.

| Technology | Location | Purpose |
| --- | --- | --- |
| Blade templates | `resources/views` | Admin, teacher, student, auth, document, PDF, and report pages |
| Vite | Backend and frontend | Asset build tool |
| Tailwind CSS | Backend and frontend | Utility-first styling |
| JavaScript | `resources/js` | Browser behavior and API calls |
| React | Backend widgets + frontend repo | Interactive dashboards, scanner pages, frontend SPA |
| React Router | Frontend repo | Client-side navigation |
| Axios | Backend and frontend | HTTP API calls |
| Laravel Echo | Backend and frontend | WebSocket client |
| Pusher JS | Backend and frontend | Reverb-compatible WebSocket protocol client |
| QR libraries | `qrcode`, `qrcode.react`, scanner package | QR generation and scanning |
| Recharts | Frontend repo | Charts and dashboard visualization |
| Lucide React | Frontend repo | Icon set |

### Reporting and Documents

| Technology | Purpose |
| --- | --- |
| barryvdh/laravel-dompdf | Generate PDF reports |
| maatwebsite/excel | Excel import/export |
| Blade PDF views | HTML templates for PDF output |
| Laravel file responses | Preview and download documents |

### DevOps and Runtime

| Technology | Purpose |
| --- | --- |
| Docker Compose | Local multi-service environment |
| Dockerfile | Builds PHP, Nginx, Node, Composer, extensions, and app assets |
| Nginx | Serves Laravel through PHP-FPM |
| Composer | PHP dependency manager |
| npm | JavaScript dependency manager |
| PHPUnit | Automated backend tests |
| Laravel Pint | PHP code style formatter |
| Laravel Pail | Local log viewer |
| phpMyAdmin | Local MySQL management UI |

### Optional Integrations

| Integration | Purpose |
| --- | --- |
| Telegram Bot API | Send attendance and report notifications |
| Gmail SMTP | Email OTP and email messages |
| Google Drive API | Optional backup storage |
| Groq OpenAI-compatible API | Optional AI assistant provider |
| Google GenAI package in frontend | Optional AI/client-side feature support |
| Cloudinary | Optional cloud media storage |

## 4. Project Directory Structure

```text
hru-project-v2/
  app/
    Console/Commands/       Custom artisan commands
    Events/                 Realtime broadcast events
    Exports/                Excel export classes
    Http/Controllers/       Web and API controllers
    Http/Middleware/        Role, demo, security, locale, maintenance middleware
    Http/Requests/          Form request validation
    Jobs/                   Queue jobs for backup, restore, cleanup
    Models/                 Eloquent database models
    Policies/               Authorization rules
    Repositories/           Data access helpers
    Services/               Business logic layer
    Support/                Shared helpers such as API response formatting
  config/                   Laravel configuration
  database/
    migrations/             Database schema history
    seeders/                Seed data
  docs/                     Project documentation
  public/                   Public web root
  resources/
    css/                    Tailwind/CSS assets
    js/                     Vite JavaScript and React widgets
    lang/                   English and Khmer translations
    views/                  Blade templates
  routes/
    api.php                 REST API routes
    web.php                 Web routes
    channels.php            Realtime broadcast authorization
    console.php             Scheduled command definitions
  storage/                  Logs, generated files, uploads, backups
  tests/                    Automated tests
  docker-compose.yml        Local service orchestration
  Dockerfile                Application container image
```

## 5. Core Application Modules

### 5.1 Authentication and User Management

The system supports login, logout, profile, registration, demo login, and role-based access.

Important technical parts:

- `AuthController` handles API authentication.
- `WebAuthController` handles web login and registration pages.
- `LoginRequest`, `RegisterRequest`, and other request classes validate inputs.
- Sanctum protects API routes using `auth:sanctum`.
- `RoleMiddleware` limits routes to allowed roles.
- `DemoReadOnlyMiddleware` prevents write operations in demo mode.
- User roles include admin, super admin, teacher, and student-related access.

### 5.2 Admin Management

Admins manage the academic foundation of the system:

- Students
- Teachers/instructors
- Subjects
- Classes
- Departments
- Majors
- Class groups
- Semester assignments
- Attendance sessions
- Student permissions
- System settings
- Documents
- Telegram bots
- Reports
- Backup and restore

The admin module is mainly served by:

- `AdminController`
- `AdminTeacherAttendanceController`
- `AdminDocumentController`
- `BackupController`
- `RestoreController`
- Admin Blade views in `resources/views/admin`

### 5.3 Teacher Portal

Teachers can:

- View assigned classes.
- View students in assigned classes.
- Generate QR codes for attendance sessions.
- Monitor live check-ins.
- Manually check in students when needed.
- Update attendance session status.
- Export attendance.
- Manage semester scores.
- Upload and view documents.
- Use teacher attendance check-in/check-out.
- Submit correction and class change requests.
- Use chat with admins/teachers.

Important technical parts:

- `TeacherController`
- `TeacherAttendanceController`
- `TeacherDocumentController`
- `AttendanceService`
- `TeacherAttendanceService`
- `SemesterAttendanceScoreService`
- Teacher Blade views in `resources/views/teacher`

### 5.4 Student Attendance

Students can:

- Scan a QR code for an active attendance session.
- Submit attendance verification.
- View active sessions.
- View class attendance history.
- Access documents.

Important technical parts:

- `AttendanceController`
- `StudentDocumentController`
- Public scan route: `/student/scan/{sessionId}`
- Verify route: `/api/student/verify`
- Attendance models: `Attendance`, `AttendanceSession`, `Student`

### 5.5 Teacher Attendance Module

This module tracks teacher work attendance separately from student class attendance.

Features:

- Teacher schedules
- Teacher attendance sessions
- QR token generation
- QR check-in
- Manual check-in/check-out
- Required checkout tracking
- Correction request workflow
- Class change request workflow
- Admin approval/rejection
- Live teacher attendance dashboard

Important technical parts:

- `AdminTeacherAttendanceController`
- `TeacherAttendanceController`
- `TeacherAttendanceService`
- `TeacherAttendanceNotificationService`
- `TeacherAttendanceUpdated` event
- Models such as `TeacherAttendanceSession`, `TeacherAttendanceLog`, `TeacherAttendanceQrToken`, `TeacherAttendanceCorrection`, and `TeacherClassChangeRequest`

### 5.6 Realtime Chat

The chat module supports teacher/admin communication.

Features:

- User search
- Conversations
- Messages
- Attachments
- Delivery/read receipts
- Reactions
- Typing indicators
- Presence
- Message edit and delete history
- Notifications
- Automatic old history cleanup

Important technical parts:

- `ChatController`
- `ChatService`
- `ChatRepository`
- Chat request validation classes
- Chat events: `MessageSent`, `MessageUpdated`, `MessageDeleted`, `UserTyping`, `UserPresenceChanged`, and receipt/reaction events
- Chat models under `app/Models/Chat`
- Broadcast channels under `routes/channels.php`

### 5.7 Reports, Exports, and PDFs

The system generates academic and attendance outputs for presentation, auditing, and administration.

Report/export examples:

- Attendance export
- Student export/import
- Course export
- Department export
- Group export
- Instructor export
- Subject export
- Subject scores
- Semester results
- Institutional transcript
- System summary
- Teacher attendance reports

Technical implementation:

- Excel files use `maatwebsite/excel`.
- PDFs use DomPDF with Blade templates.
- Export classes are stored in `app/Exports`.
- PDF templates are stored in `resources/views/pdf` and `resources/views/admin/exports`.

### 5.8 Documents

Document handling is available for admins, teachers, and students.

Features:

- Admin document management
- Teacher document upload/view
- Student document preview/download
- Role-based document access

Important technical parts:

- `Document` model
- `AdminDocumentController`
- `TeacherDocumentController`
- `StudentDocumentController`
- Blade views under `resources/views/*/documents`

### 5.9 Backup and Restore

The backend includes a strong backup and restore subsystem.

Features:

- Full backups
- Incremental backups
- Weekly and monthly backups
- Backup cleanup
- Backup verification
- Restore test jobs
- Optional Google Drive backup integration
- Backup/restore logs

Important technical parts:

- `BackupService`
- `RestoreService`
- `GoogleDriveService`
- `BackupJob`
- `ScheduledBackupJob`
- `BackupCleanupJob`
- `BackupVerificationJob`
- `RestoreBackupJob`
- `RestoreCloudBackupJob`
- `RestoreTestJob`
- `BackupRestoreLog` model

Scheduled backup tasks are defined in `routes/console.php`.

## 6. Database Design Summary

The database is managed through Laravel migrations. Main entity groups include:

### User and access control tables

- `users`
- `personal_access_tokens`
- `activity_logs`
- `student_permissions`

### Academic structure tables

- `departments`
- `majors`
- `class_groups`
- `subjects`
- `teachers`
- `students`
- `classes`
- `class_class_group`
- `semester_assignments`
- Semester score tables

### Student attendance tables

- `attendance_sessions`
- `attendance`
- `student_restore_histories`

### Teacher attendance tables

- `teacher_schedules`
- `teacher_attendance_sessions`
- `teacher_attendance_logs`
- `teacher_attendance_qr_tokens`
- `teacher_attendance_corrections`
- `teacher_attendance_reports`
- `teacher_class_change_requests`
- `teacher_registration_requests`

### Chat tables

- `conversations`
- `conversation_participants`
- `messages`
- `attachments`
- `message_receipts`
- `message_reactions`
- `message_deletions`
- `message_edit_histories`

### System and integration tables

- `settings`
- `cache`
- `jobs`
- `failed_jobs`
- `telegram_bots`
- `documents`
- `backup_restore_logs`
- `user_locations`

## 7. API Design

The main API file is `routes/api.php`.

### Public and auth endpoints

- `GET /api/` checks whether the API is running.
- `POST /api/login` logs in a user.
- `GET /api/check-status` checks system status.
- `GET /api/branding` returns branding information.

### Protected shared endpoints

Protected routes use:

```text
auth:sanctum
demo.readonly
```

Shared protected endpoints include:

- `GET /api/profile`
- `POST /api/logout`
- Chat endpoints under `/api/chat`

### Teacher endpoints

Teacher routes include:

- `/api/teacher/summary`
- `/api/teacher/classes`
- `/api/teacher/session/{sessionId}/qr`
- `/api/teacher/session/{sessionId}/monitor`
- `/api/teacher/session/{sessionId}/checkin`
- `/api/teacher/semesters`
- `/api/teacher/attendance/*`
- `/api/teacher/documents`

### Admin endpoints

Admin routes include:

- `/api/admin/stats`
- `/api/admin/users`
- `/api/admin/classes`
- `/api/admin/students`
- `/api/admin/subjects`
- `/api/admin/departments`
- `/api/admin/majors`
- `/api/admin/class-groups`
- `/api/admin/teacher-attendance/*`
- `/api/admin/semesters/*`
- `/api/admin/attendance-assistant/analyze`

### Student endpoints

Student routes include:

- `/api/student/portal`
- `/api/student/active-session`
- `/api/student/classes`
- `/api/student/classes/{classId}/history`
- `/api/student/documents`
- `/api/student/scan/{sessionId}`
- `/api/student/verify`
- `/api/student/history`

## 8. Realtime Architecture

Realtime features use Laravel Reverb, Laravel Echo, and Pusher JS.

Main realtime channels:

- `teacher-attendance`
- `teacher-attendance.{date}`
- `teacher-attendance.teacher.{teacherId}`
- `chat.conversation.{conversationId}`
- `chat.user.{userId}`
- `chat.presence`

Use cases:

- Live teacher attendance dashboard
- Chat messages
- Message read/delivery receipts
- Typing indicators
- Online presence
- Attendance updates

## 9. Background Jobs and Scheduled Tasks

The system uses queues for long-running or background work and the scheduler for recurring tasks.

Scheduled tasks from `routes/console.php`:

| Schedule | Task |
| --- | --- |
| Daily 07:15 | Notify teachers |
| Every 15 minutes | Process teacher attendance sync |
| Daily 01:30 | Clean old chat history |
| Daily 02:00 | Full backup |
| Daily 08:00, 12:00, 16:00, 20:00 | Incremental backups |
| Weekly Sunday 03:00 | Weekly backup |
| Monthly day 1 at 04:00 | Monthly backup |
| Daily 05:00 | Backup cleanup |
| Weekly Sunday 04:00 | Backup verification |
| First Sunday 06:00 | Restore test |

The Docker Compose file runs dedicated containers for:

- `queue`
- `scheduler`
- `reverb`

This separates background work from normal web requests.

## 10. Security Design

Security controls include:

- Laravel Sanctum token authentication.
- Role-based route protection.
- Super admin-only destructive actions.
- Demo read-only mode for project review.
- Request validation through form request classes.
- Rate limiting on login, chat send, typing, activity, and location recording.
- Broadcast channel authorization.
- Security headers middleware.
- Maintenance mode middleware.
- Password hashing with bcrypt.
- Environment-based feature flags.
- Activity logs and backup/restore logs.

Important production rule:

Do not commit real secrets in `.env`. Rotate any exposed email app passwords, API keys, bot tokens, cloud credentials, and database passwords before deployment or public sharing.

## 11. Local Development Setup

### Backend with Docker

```bash
cd hru-project-v2
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

Useful URLs:

```text
Backend app: http://localhost:8080
phpMyAdmin:  http://localhost:8082
Reverb:      ws://localhost:8091
```

### Backend without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

### Separate React frontend

The sibling frontend project runs on port 3000.

```bash
cd ../hru-project-v2-front
npm install
npm run dev
```

Expected frontend URL:

```text
http://localhost:3000
```

The backend `.env` uses `FRONTEND_URL` and Sanctum stateful domains so browser-based authentication can work across backend and frontend ports.

## 12. Docker Services

| Service | Container role | Local port |
| --- | --- | --- |
| `app` | Laravel app, Nginx, PHP-FPM | 8080 |
| `reverb` | WebSocket server | 8091 |
| `queue` | Queue worker | internal |
| `scheduler` | Scheduler worker | internal |
| `mysql` | MySQL database | 3308 -> 3306 |
| `redis` | Cache, queue, sessions | internal |
| `phpmyadmin` | Database UI | 8082 |

## 13. Environment Configuration

Important `.env` groups:

### Application

```env
APP_NAME=HRU-ATMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:3000
```

### Database

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=attendance_db_two
DB_USERNAME=...
DB_PASSWORD=...
```

### Auth and local features

```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8080,...
ALLOW_STUDENT_CODE_LOGIN=true
PUBLIC_REGISTRATION_ENABLED=true
DEMO_LOGIN_ENABLED=true
SUPER_ADMIN_KEY=...
```

### Realtime

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=reverb
REVERB_PORT=8091
```

### Optional integrations

```env
MAIL_MAILER=smtp
TELEGRAM_BOT_TOKEN=
AI_ASSISTANT_ENABLED=false
GROQ_API_KEY=
GOOGLE_DRIVE_...
CLOUDINARY_...
```

## 14. Testing and Quality

Available commands:

```bash
composer test
php artisan test
./vendor/bin/pint
npm run build
```

Recommended checks before presentation or submission:

- Run database migrations from a clean database.
- Run backend tests.
- Build frontend/backend assets.
- Confirm login works for admin, teacher, and student flows.
- Confirm QR generation and scanning work.
- Confirm Reverb is running for realtime modules.
- Confirm report export works for PDF and Excel.
- Confirm backup commands do not fail.

## 15. Deployment Notes

For production:

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Disable demo login unless required for review.
- Disable public registration if account creation should be admin-controlled.
- Use strong database passwords.
- Rotate all development credentials.
- Use HTTPS for backend and frontend.
- Set correct `APP_URL`, `FRONTEND_URL`, and `SANCTUM_STATEFUL_DOMAINS`.
- Run `php artisan config:cache`, `route:cache`, and `view:cache` after configuration.
- Run queue, scheduler, and Reverb as managed services.
- Configure backup storage and verify restore tests.

## 16. Presentation Talking Points

### Short technical summary

HRU-ATMS is a Laravel 12 and React-based academic attendance management platform. It uses MySQL for structured academic data, Redis for caching and queues, Sanctum for secure API authentication, Reverb for realtime WebSocket features, and Docker Compose for local deployment. The system supports student QR attendance, teacher attendance, admin management, reports, chat, documents, notifications, and backup/restore workflows.

### Why Laravel was selected

- Provides routing, authentication, validation, ORM, queues, scheduler, and security features in one mature framework.
- Supports both server-rendered Blade pages and API-based frontend integration.
- Works well with MySQL, Redis, Docker, and realtime broadcasting.
- Speeds up development of admin workflows and reporting features.

### Why MySQL was selected

- Attendance, users, classes, subjects, and reports are relational data.
- MySQL supports reliable joins, indexes, transactions, and structured constraints.
- Easy to inspect locally with phpMyAdmin.

### Why Redis was selected

- Improves performance for cache/session use.
- Supports queue-backed background work.
- Helps separate heavy operations from normal web requests.

### Why Reverb was selected

- Native Laravel realtime WebSocket server.
- Works with Laravel Echo and Pusher JS.
- Useful for live teacher attendance, chat, read receipts, typing, and presence.

### Why Docker was selected

- Makes the project easier for teammates to run with the same versions.
- Includes app, database, Redis, Reverb, queue worker, scheduler, and phpMyAdmin in one stack.
- Reduces setup problems between different computers.

## 17. Team Member Responsibilities Example

| Area | Suggested owner |
| --- | --- |
| Backend API and database | Laravel controllers, services, models, migrations |
| Frontend UI | React/Blade pages, Tailwind, dashboards, scanner screens |
| Attendance workflow | QR generation, validation, sessions, logs |
| Teacher attendance | Schedules, check-in/out, corrections, realtime monitor |
| Reports | PDF, Excel, charts, academic summaries |
| Security and testing | Roles, middleware, validation, tests |
| Deployment | Docker, environment, backup, production settings |

## 18. Suggested Slide Outline

1. Project title and problem statement
2. Objectives
3. User roles
4. System architecture
5. Technology stack
6. Core modules
7. QR attendance workflow
8. Teacher attendance workflow
9. Realtime chat and live monitoring
10. Database design
11. Security design
12. Reports and exports
13. Docker deployment
14. Testing and quality
15. Future improvements

## 19. Future Improvement Ideas

- Mobile app for students and teachers.
- Stronger GPS/geofence verification for attendance.
- Face verification during check-in.
- More analytics dashboards.
- Notification center for all users.
- Offline attendance mode with later sync.
- Fine-grained permission management.
- Audit dashboard for security and administrative actions.
- Production cloud deployment with automated CI/CD.
