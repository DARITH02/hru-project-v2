# Teacher Attendance Security and Scalability Architecture

## Scope

This document defines the secure and scalable architecture for a Laravel + React Teacher Attendance Management System. It is designed for university lecturer attendance based on teaching schedules, check-in/check-out, correction approvals, class cancellation, rescheduling, reporting, Telegram notifications, and future HR/payroll integration.

## Secure Project Architecture

### Backend Layers

Use a layered Laravel backend:

```text
app/
  Http/
    Controllers/
      Api/
        AuthController.php
        TeacherAttendanceController.php
        AdminTeacherAttendanceController.php
        TeacherAttendanceCorrectionController.php
        TeacherClassChangeRequestController.php
        TeacherAttendanceReportController.php
    Middleware/
      RoleMiddleware.php
      PermissionMiddleware.php
    Requests/
      Auth/
      TeacherAttendance/
      TeacherAttendanceCorrection/
      TeacherClassChange/
    Resources/
      TeacherAttendanceSessionResource.php
      TeacherScheduleResource.php
      TeacherAttendanceCorrectionResource.php
      TeacherClassChangeRequestResource.php
  Models/
  Policies/
  Repositories/
  Services/
  Jobs/
  Notifications/
```

### Frontend Layers

Use React as a separate frontend client:

```text
src/
  api/
    client.ts
    auth.ts
    teacherAttendance.ts
    adminTeacherAttendance.ts
  components/
  pages/
    auth/
    teacher/
    admin/
  hooks/
  guards/
  stores/
  types/
```

React should only communicate through protected JSON APIs. Do not expose database logic, credentials, or privileged decisions to the frontend.

## Authentication

Use Laravel Sanctum for API authentication.

Recommended flow:

1. React calls `POST /api/login`.
2. Laravel validates credentials and approval status.
3. Laravel creates a Sanctum token or uses Sanctum SPA cookie auth.
4. React stores only the minimum required token/session state.
5. Every protected API route uses `auth:sanctum`.
6. Logout revokes the current token.

Password hashing:

- Use Laravel default password hashing.
- Use bcrypt or argon2id.
- Never store plain text passwords.
- Never log passwords.

Recommended `.env`:

```text
HASH_DRIVER=bcrypt
```

or:

```text
HASH_DRIVER=argon2id
```

## Role-Based Access Control

Roles:

- `super_admin`
- `admin`
- `teacher`

### Super Admin

- Full system access.
- Manage admins.
- Manage global settings.
- Delete or archive critical records.
- View audit logs.
- Configure permissions.
- Generate all reports.

### Admin

- Manage teacher attendance.
- Manage schedules.
- Approve/reject correction requests.
- Approve/reject class change requests.
- Generate operational reports.
- Cannot delete super admin records.
- Cannot change global security settings unless permitted.

### Teacher

- View own teaching schedules.
- Check in/check out for own sessions only.
- View own attendance history.
- Submit corrections.
- Submit cancellation/reschedule/replacement requests.
- Cannot approve own requests.
- Cannot view another teacher’s attendance unless granted department-level permission.

## Permission List

Use permissions in addition to roles. A user can have a role and module-specific permissions.

Suggested permissions:

```text
teacher_attendance.view_any
teacher_attendance.view_own
teacher_attendance.create_schedule
teacher_attendance.update_schedule
teacher_attendance.cancel_schedule
teacher_attendance.reschedule
teacher_attendance.check_in
teacher_attendance.check_out
teacher_attendance.manual_check_in
teacher_attendance.manual_check_out
teacher_attendance.override_status
teacher_attendance.mark_permission
teacher_attendance.view_reports
teacher_attendance.export_reports
teacher_attendance.generate_reports

teacher_attendance_correction.create
teacher_attendance_correction.view_own
teacher_attendance_correction.view_any
teacher_attendance_correction.approve
teacher_attendance_correction.reject

teacher_class_change.create
teacher_class_change.view_own
teacher_class_change.view_any
teacher_class_change.approve
teacher_class_change.reject

teacher_attendance_audit.view
teacher_attendance_audit.export

system_settings.manage
telegram_settings.manage
users.manage
roles.manage
permissions.manage
```

Recommended tables:

```text
permissions
role_permissions
user_permissions
```

Keep permission checks server-side through middleware and policies.

## Middleware List

Recommended middleware:

```text
auth:sanctum
role:super_admin,admin,teacher
permission:teacher_attendance.check_in
throttle:login
throttle:check-in
throttle:check-out
throttle:qr
throttle:api
verified.optional
demo.readonly
force.json
audit.request
```

### Route Protection

All API routes must be protected except public authentication and limited public scan information.

Example:

```php
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::middleware(['role:teacher', 'permission:teacher_attendance.check_in'])->post(
        '/teacher/attendance/sessions/{session}/check-in',
        [TeacherAttendanceController::class, 'checkIn']
    );
});
```

## Rate Limiting

Define named rate limiters in `RouteServiceProvider` or application bootstrapping.

Recommended limits:

```text
login: 5 attempts per minute per IP/email
check-in: 10 attempts per minute per teacher
check-out: 10 attempts per minute per teacher
qr: 30 attempts per minute per IP/device
api: 120 requests per minute per user
reports: 10 requests per minute per admin
```

Protect:

- Login
- Check-in
- Check-out
- QR verification
- Correction submission
- Class-change submission
- Report generation

## Form Request Validation

Do not validate complex requests inside controllers. Use Form Request classes.

Recommended classes:

```text
app/Http/Requests/TeacherAttendance/CheckInRequest.php
app/Http/Requests/TeacherAttendance/CheckOutRequest.php
app/Http/Requests/TeacherAttendance/AdminUpdateSessionRequest.php
app/Http/Requests/TeacherAttendanceCorrection/StoreCorrectionRequest.php
app/Http/Requests/TeacherAttendanceCorrection/ReviewCorrectionRequest.php
app/Http/Requests/TeacherClassChange/StoreClassChangeRequest.php
app/Http/Requests/TeacherClassChange/ReviewClassChangeRequest.php
app/Http/Requests/TeacherAttendanceReport/GenerateReportRequest.php
```

Validation rules should enforce:

- User owns teacher session when role is teacher.
- Check-in session belongs to authenticated teacher.
- Check-out session belongs to authenticated teacher.
- Requested schedule belongs to teacher.
- Reschedule end time is after start time.
- Status values are valid enums.
- Reason fields have max length.
- Uploaded proof files are size/type restricted.

## Prevent Duplicate Check-In and Check-Out

Rules:

- One attendance session per teaching schedule.
- `teacher_attendance_sessions.schedule_id` must be unique.
- Check-in is rejected if `check_in_time` already exists unless admin override permission is present.
- Check-out is rejected if `check_out_time` already exists unless admin override permission is present.
- Admin override must create audit log.

Recommended service logic:

```text
if check_in_time exists and user is teacher:
    reject duplicate check-in

if check_out_time exists and user is teacher:
    reject duplicate check-out
```

## Scalable Database Schema

Core tables:

```text
teachers
teacher_schedules
teacher_attendance_sessions
teacher_attendance_logs
teacher_attendance_corrections
teacher_class_change_requests
teacher_attendance_reports
permissions
role_permissions
user_permissions
```

### Important Indexes

Add or keep indexes:

```text
teacher_schedules:
  teacher_id, schedule_date
  subject_id
  class_id
  class_group_id
  semester, academic_year
  status

teacher_attendance_sessions:
  teacher_id, attendance_date
  schedule_id unique
  attendance_status
  subject_id
  class_id

teacher_attendance_logs:
  teacher_id, created_at
  teacher_attendance_session_id
  action
  actor_id

teacher_attendance_corrections:
  teacher_id, status
  attendance_session_id
  reviewed_by

teacher_class_change_requests:
  teacher_id, status
  schedule_id
  request_type
```

### Soft Deletes

Use soft deletes for important records:

```text
teacher_schedules
teacher_attendance_sessions
teacher_attendance_corrections
teacher_class_change_requests
teacher_attendance_reports
```

Do not physically delete attendance records in normal admin workflows. Use status changes and audit logs.

## API Structure

### Teacher APIs

```text
GET    /api/teacher/attendance/schedules
GET    /api/teacher/attendance/sessions
GET    /api/teacher/attendance/sessions/{session}
POST   /api/teacher/attendance/sessions/{session}/check-in
POST   /api/teacher/attendance/sessions/{session}/check-out
GET    /api/teacher/attendance/history
POST   /api/teacher/attendance/corrections
GET    /api/teacher/attendance/corrections
POST   /api/teacher/attendance/class-change-requests
GET    /api/teacher/attendance/class-change-requests
```

### Admin APIs

```text
GET    /api/admin/teacher-attendance/dashboard
GET    /api/admin/teacher-attendance/live
GET    /api/admin/teacher-attendance/schedules
POST   /api/admin/teacher-attendance/schedules
PUT    /api/admin/teacher-attendance/schedules/{schedule}
GET    /api/admin/teacher-attendance/sessions
GET    /api/admin/teacher-attendance/sessions/{session}
PUT    /api/admin/teacher-attendance/sessions/{session}
POST   /api/admin/teacher-attendance/sessions/{session}/manual-check-in
POST   /api/admin/teacher-attendance/sessions/{session}/manual-check-out
POST   /api/admin/teacher-attendance/sessions/{session}/mark-permission
GET    /api/admin/teacher-attendance/corrections
POST   /api/admin/teacher-attendance/corrections/{correction}/approve
POST   /api/admin/teacher-attendance/corrections/{correction}/reject
GET    /api/admin/teacher-attendance/class-change-requests
POST   /api/admin/teacher-attendance/class-change-requests/{request}/approve
POST   /api/admin/teacher-attendance/class-change-requests/{request}/reject
GET    /api/admin/teacher-attendance/reports
POST   /api/admin/teacher-attendance/reports
GET    /api/admin/teacher-attendance/audit-logs
```

## API Resources

Use Laravel API Resources for consistent JSON output.

Recommended resources:

```text
TeacherScheduleResource
TeacherAttendanceSessionResource
TeacherAttendanceLogResource
TeacherAttendanceCorrectionResource
TeacherClassChangeRequestResource
TeacherAttendanceReportResource
TeacherSummaryResource
```

Benefits:

- Prevent leaking internal fields.
- Keep API response format stable.
- Format dates consistently.
- Add computed fields like `can_check_in`, `can_check_out`, and `attendance_label`.

## Repository Layer

Use repositories when query complexity grows.

Recommended repositories:

```text
TeacherScheduleRepository
TeacherAttendanceSessionRepository
TeacherAttendanceReportRepository
TeacherAttendanceAuditRepository
```

Example responsibilities:

- Build filters.
- Apply date range conditions.
- Apply department/teacher/subject scopes.
- Return paginated results.
- Keep controllers thin.

## Queue Job Design

Use Redis queues for slow tasks.

Recommended jobs:

```text
SendTeacherAttendanceTelegramNotification
SendTeacherAttendanceEmailNotification
GenerateTeacherAttendanceReport
ExportTeacherAttendanceReport
MarkAbsentTeacherSessions
MarkMissingTeacherCheckouts
SyncTeacherSchedulesFromAcademicClasses
BackupMysqlDatabase
```

Queue priorities:

```text
high: authentication/security notifications
default: attendance notifications
reports: report generation/export
maintenance: backup/sync jobs
```

Recommended `.env`:

```text
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

## Audit Log Design

Audit logs must be append-only.

Recommended table:

```text
teacher_attendance_logs
  id
  teacher_attendance_session_id nullable
  teacher_id
  actor_id nullable
  action
  old_values json nullable
  new_values json nullable
  ip_address nullable
  user_agent nullable
  remarks nullable
  created_at
```

Required audit events:

```text
login_success
login_failed
logout
checked_in
checked_out
duplicate_check_in_rejected
duplicate_check_out_rejected
late_check_in
missing_check_in_marked
missing_check_out_marked
admin_override
status_changed
correction_submitted
correction_approved
correction_rejected
class_cancellation_requested
class_reschedule_requested
class_change_approved
class_change_rejected
report_generated
report_exported
telegram_notification_sent
telegram_notification_failed
```

For global actions, use a general `audit_logs` table later:

```text
id
actor_id
module
action
auditable_type
auditable_id
old_values json
new_values json
ip_address
user_agent
created_at
```

## Device, IP, and Location Validation

Optional controls:

- Store teacher trusted devices.
- Record device fingerprint.
- Record IP address.
- Record check-in/check-out latitude and longitude.
- Validate teacher is within campus geofence.
- Flag unusual IP/device/location changes.

Recommended tables:

```text
teacher_devices
teacher_attendance_location_rules
teacher_attendance_security_flags
```

Do not block teachers too aggressively at first. Prefer warning/flagging plus admin review unless university policy requires strict location enforcement.

## CORS for React

Configure `config/cors.php` for the React domain only.

Development:

```text
http://localhost:3000
http://localhost:5173
```

Production:

```text
https://attendance.university.edu
```

Do not use wildcard origins in production.

Recommended `.env`:

```text
FRONTEND_URL=https://attendance.university.edu
SANCTUM_STATEFUL_DOMAINS=attendance.university.edu
SESSION_DOMAIN=.university.edu
```

## XSS and Input Sanitization

Rules:

- Escape all output in Blade using `{{ }}`.
- React escapes text by default; avoid `dangerouslySetInnerHTML`.
- Sanitize rich text if rich text is ever introduced.
- Validate string lengths.
- Strip unwanted HTML from notes/reasons unless HTML is explicitly supported.
- Use Content Security Policy in production.

## SQL Injection Prevention

Rules:

- Use Eloquent or Query Builder.
- Do not concatenate raw SQL with user input.
- Use bound parameters for raw expressions if raw SQL is unavoidable.
- Keep filters whitelisted.
- Validate sortable columns before using them.

## Sensitive Data and .env

Keep these in `.env` only:

```text
APP_KEY
DB_HOST
DB_DATABASE
DB_USERNAME
DB_PASSWORD
TELEGRAM_BOT_TOKEN
CLOUDINARY_URL
MAIL_USERNAME
MAIL_PASSWORD
REDIS_PASSWORD
```

Never commit real secrets.

Never expose:

```text
.env
vendor/
storage/
database backups
private keys
composer auth files
```

## Telegram Token Security

Rules:

- Store bot token in `.env` or encrypted database storage.
- Do not display full token in admin UI.
- Mask token in settings pages.
- Rotate token if leaked.
- Queue Telegram sending.
- Log success/failure without logging token.

Recommended:

```text
TELEGRAM_BOT_TOKEN=
TELEGRAM_DEFAULT_CHAT_ID=
```

## Redis Usage

Use Redis for:

- Cache
- Queue
- Session
- Rate limiting

Recommended `.env`:

```text
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
```

## Report Generation

Reports can become heavy. Use background jobs.

Flow:

1. Admin requests report.
2. Laravel validates filters.
3. Create `teacher_attendance_reports` record with `processing` status.
4. Dispatch `GenerateTeacherAttendanceReport`.
5. Job generates file.
6. Store file path.
7. Notify admin when ready.

Report types:

- Daily teacher attendance
- Monthly teacher attendance
- Semester teaching report
- Teaching hours report
- Late attendance report
- Absent teacher report

## Scheduler

Use Laravel Scheduler for automation:

```text
Every 5 minutes:
  mark active teaching state

Every 15 minutes:
  mark absent teachers
  mark missing check-outs
  sync generated schedules

Daily:
  generate daily summary
  send admin attendance digest

Weekly:
  generate department summary

Monthly:
  generate monthly teacher attendance report
```

Example:

```php
Schedule::command('teacher-attendance:process --sync')->everyFifteenMinutes();
```

## Docker Deployment

Recommended production services:

```text
nginx
app
mysql
redis
queue-worker
scheduler
backup
```

Use separate containers:

- `nginx`: public HTTP/HTTPS reverse proxy
- `app`: PHP-FPM Laravel app
- `mysql`: database
- `redis`: cache/session/queue
- `queue-worker`: `php artisan queue:work`
- `scheduler`: `php artisan schedule:work`

Do not run everything in one process in production.

## MySQL Backup and Restore Strategy

### Backup

Use scheduled backups:

```text
Daily full database backup
Hourly binlog or incremental backup if high activity
Weekly offsite backup
Monthly archive backup
```

Store backups:

- Encrypted local volume
- Offsite storage
- Restricted access

Backup command:

```bash
mysqldump --single-transaction --routines --triggers database_name > backup.sql
```

### Restore

Test restore regularly:

```bash
mysql database_name < backup.sql
```

Keep a written restore runbook:

1. Stop app writes.
2. Restore backup to staging.
3. Validate data.
4. Restore production if approved.
5. Run migrations if needed.
6. Verify login, attendance, reports.

## Logging and Monitoring

Log:

- Authentication failures
- Admin overrides
- Attendance status changes
- Correction approvals/rejections
- Report generation failures
- Telegram failures
- Queue failures
- Backup failures

Monitor:

- App uptime
- Queue length
- Failed jobs
- MySQL disk usage
- Redis memory
- API error rate
- Slow queries
- Login failures
- Attendance check-in spikes

Recommended tools:

- Laravel logs
- Laravel Horizon if using Redis queues
- Supervisor for workers
- MySQL slow query log
- Docker health checks
- External uptime monitoring

## Deployment Recommendations

Production requirements:

- Use HTTPS.
- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set secure `APP_KEY`.
- Run `php artisan config:cache`.
- Run `php artisan route:cache`.
- Run `php artisan view:cache`.
- Run migrations during controlled deploy.
- Use non-root containers where possible.
- Restrict MySQL and Redis to private Docker network.
- Do not expose phpMyAdmin publicly.
- Use a real process manager for queue workers.
- Use rolling or maintenance deploys for schema changes.

## Production Security Checklist

### Laravel

- `APP_DEBUG=false`
- `APP_ENV=production`
- Strong `APP_KEY`
- Sanctum configured
- CORS restricted
- Rate limiting enabled
- Form Requests used
- Policies/middleware enforced
- Sensitive data in `.env`
- No secrets committed
- Error pages do not leak stack traces

### API

- All protected routes use `auth:sanctum`
- Role middleware applied
- Permission middleware applied
- Pagination used
- API Resources used
- Duplicate check-in/check-out blocked
- Request validation enforced
- Audit logs written

### Database

- MySQL not public
- Strong DB password
- Required indexes exist
- Soft deletes enabled for important records
- Backups scheduled
- Restore tested
- Slow queries monitored

### Docker

- Nginx exposes only HTTP/HTTPS
- App, MySQL, Redis are private
- Queue worker container enabled
- Scheduler container enabled
- Volumes backed up
- Production images rebuilt cleanly

### Telegram

- Token stored securely
- Token not shown in UI
- Messages sent through queue
- Failures logged without token

### React

- API URL from environment
- No secrets in frontend bundle
- No unsafe HTML rendering
- Auth state cleared on logout
- Role guards in UI
- Server still enforces all permissions

## Future Scalability

Design for:

- Payroll module using approved teaching hours.
- HR leave module linked to permission status.
- Student attendance integration.
- Mobile teacher check-in app.
- Department head review workflow.
- Multi-campus geofencing.
- Biometric/device verification.
- Offline check-in with later sync.
- Data warehouse/reporting database.
- Multi-tenant university branches.

