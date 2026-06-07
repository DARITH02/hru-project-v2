# Teacher Attendance Management Module

## Purpose

This module tracks lecturer attendance based on assigned teaching schedules and class sessions. It is designed for full-time and part-time university lecturers, where attendance depends on teaching obligations rather than fixed office hours.

## Core Concept

Teacher attendance is schedule-based:

- A teacher has scheduled teaching sessions.
- Each schedule creates or maps to a teacher attendance session.
- Teachers check in during a configurable window before class.
- Teachers check out after teaching ends.
- The system calculates late time, early leave time, actual teaching duration, and attendance percentage.
- Teachers can request corrections, cancellations, reschedules, and replacement sessions.
- Admins approve, reject, audit, and report on all activity.

## User Roles

### Super Admin

- Full access to all teacher attendance data.
- Configure attendance rules.
- Approve or reject correction and schedule-change requests.
- Delete or override records when necessary.
- Export reports.
- View audit logs.

### Admin

- Monitor teacher attendance.
- Review and approve requests.
- Create manual attendance records.
- Generate reports.
- Manage schedules and replacement sessions.

### Teacher

- View own teaching schedule.
- Check in and check out.
- View own attendance history.
- Submit correction requests.
- Request class cancellation or rescheduling.
- View request approval status.

### Department Head

Optional future role:

- View department-level teacher attendance.
- Review reports.
- Recommend approval before admin approval.

## Business Workflow

### 1. Schedule Creation

Admin creates or imports teaching schedules with:

- Teacher
- Subject
- Class group
- Room
- Date
- Start time
- End time
- Semester
- Academic year

Each schedule can generate one `teacher_attendance_session`.

### 2. Check-In

Teachers check in before class starts.

Example:

- Class: 5:30 PM to 8:00 PM
- Check-in window: 5:00 PM to 5:45 PM

Recommended status rules:

- Before 5:30 PM: `on_time`
- 5:31 PM to 5:45 PM: `late`
- After 5:45 PM: `very_late`
- No check-in after cutoff: `absent`

### 3. Teaching State

After successful check-in, the session can move to:

- `on_time`
- `late`
- `very_late`
- `teaching`

Use `teaching` when the class is actively in progress.

### 4. Check-Out

Teachers check out after teaching ends.

Recommended status rules:

- Check-out at or after scheduled end: `completed`
- Check-out before scheduled end: `early_leave`
- No check-out by configured cutoff: `missing_check_out`

### 5. Automatic Calculation

The system calculates:

- Late minutes
- Early leave minutes
- Teaching duration minutes
- Actual teaching hours
- Attendance percentage

### 6. Correction Requests

Teachers can submit requests for:

- Missing check-in
- Missing check-out
- Wrong attendance status
- Internet problems
- Schedule changes

Workflow:

1. Teacher submits request.
2. Admin reviews request.
3. Admin approves or rejects.
4. System records audit trail.
5. If approved, attendance record is updated.
6. Teacher receives notification.

### 7. Cancellation and Rescheduling

Teachers can request:

- Class cancellation
- Class rescheduling
- Replacement session

Workflow:

1. Teacher submits change request.
2. Admin reviews.
3. Admin approves or rejects.
4. Approved cancellation updates attendance session to `cancelled`.
5. Approved reschedule creates a new schedule/session and links it to the original.
6. Replacement sessions are marked as replacement records.

## Attendance Statuses

Use consistent lowercase enum values in the database:

- `scheduled`
- `on_time`
- `late`
- `very_late`
- `teaching`
- `completed`
- `early_leave`
- `absent`
- `permission`
- `cancelled`
- `rescheduled`
- `missing_check_out`

## Database Schema

### teachers

Already exists. Keep it as the teacher profile table.

Recommended additional fields if not already present:

```text
department_id nullable
specialization nullable
status active/inactive
telegram_id nullable
```

### teacher_schedules

Stores planned teaching schedules.

```text
id
teacher_id foreign key
subject_id foreign key
class_id foreign key nullable
class_group_id foreign key nullable
room_id foreign key nullable
room_name nullable
schedule_date date
scheduled_start_time datetime
scheduled_end_time datetime
check_in_opens_at datetime
check_in_closes_at datetime
check_out_opens_at datetime nullable
check_out_closes_at datetime nullable
semester string
academic_year string
status scheduled/cancelled/rescheduled/completed
source manual/import/generated
original_schedule_id nullable
created_by foreign key users nullable
approved_by foreign key users nullable
remarks nullable
timestamps
```

Indexes:

```text
teacher_id + schedule_date
subject_id
class_id
class_group_id
semester + academic_year
status
```

### teacher_attendance_sessions

Stores attendance state for a schedule.

```text
id
teacher_id foreign key
schedule_id foreign key
subject_id foreign key
class_id foreign key nullable
class_group_id foreign key nullable
room_id foreign key nullable
attendance_date date
scheduled_start_time datetime
scheduled_end_time datetime
check_in_time datetime nullable
check_out_time datetime nullable
attendance_status enum
check_in_method qr/manual/location/system
check_out_method qr/manual/location/system
late_minutes integer default 0
early_leave_minutes integer default 0
teaching_duration_minutes integer default 0
actual_teaching_hours decimal(6,2) default 0
attendance_percentage decimal(5,2) default 0
check_in_latitude decimal nullable
check_in_longitude decimal nullable
check_out_latitude decimal nullable
check_out_longitude decimal nullable
remarks nullable
approved_by foreign key users nullable
created_by foreign key users nullable
timestamps
```

Unique rule:

```text
unique(schedule_id)
```

Indexes:

```text
teacher_id + attendance_date
attendance_status
schedule_id
semester/reporting fields through schedule relation
```

### teacher_attendance_logs

Audit trail for every action.

```text
id
teacher_attendance_session_id foreign key nullable
teacher_id foreign key
actor_id foreign key users nullable
action string
old_values json nullable
new_values json nullable
ip_address nullable
user_agent nullable
remarks nullable
created_at
```

Example actions:

- `checked_in`
- `checked_out`
- `status_changed`
- `admin_override`
- `correction_submitted`
- `correction_approved`
- `correction_rejected`
- `schedule_cancelled`
- `schedule_rescheduled`

### teacher_attendance_corrections

Stores correction requests.

```text
id
teacher_id foreign key
attendance_session_id foreign key nullable
schedule_id foreign key nullable
request_type missing_check_in/missing_check_out/wrong_status/internet_problem/schedule_change/other
requested_check_in_time datetime nullable
requested_check_out_time datetime nullable
requested_status nullable
reason text
attachment_path nullable
status pending/approved/rejected
reviewed_by foreign key users nullable
reviewed_at datetime nullable
review_note nullable
timestamps
```

### teacher_class_change_requests

Stores cancellation, reschedule, and replacement requests.

```text
id
teacher_id foreign key
schedule_id foreign key
request_type cancellation/reschedule/replacement
requested_date date nullable
requested_start_time datetime nullable
requested_end_time datetime nullable
requested_room_id foreign key nullable
requested_room_name nullable
reason text
status pending/approved/rejected
replacement_schedule_id foreign key nullable
reviewed_by foreign key users nullable
reviewed_at datetime nullable
review_note nullable
timestamps
```

### teacher_attendance_reports

Optional cached report table for heavy reporting.

```text
id
report_type daily/monthly/semester/academic_year/teaching_hours/late/absent
teacher_id foreign key nullable
department_id foreign key nullable
semester nullable
academic_year nullable
date_from date
date_to date
filters json nullable
summary json
file_path nullable
generated_by foreign key users
generated_at datetime
timestamps
```

## Laravel Backend Architecture

### Models

```text
app/Models/TeacherSchedule.php
app/Models/TeacherAttendanceSession.php
app/Models/TeacherAttendanceLog.php
app/Models/TeacherAttendanceCorrection.php
app/Models/TeacherClassChangeRequest.php
app/Models/TeacherAttendanceReport.php
```

### Controllers

API controllers:

```text
app/Http/Controllers/Api/TeacherAttendanceController.php
app/Http/Controllers/Api/AdminTeacherAttendanceController.php
app/Http/Controllers/Api/TeacherAttendanceCorrectionController.php
app/Http/Controllers/Api/TeacherClassChangeRequestController.php
app/Http/Controllers/Api/TeacherAttendanceReportController.php
```

Web/Blade admin controllers if keeping the current UI style:

```text
app/Http/Controllers/Admin/TeacherAttendancePageController.php
```

### Services

```text
app/Services/TeacherAttendanceService.php
app/Services/TeacherAttendanceReportService.php
app/Services/TeacherScheduleService.php
app/Services/TeacherAttendanceNotificationService.php
```

### Policies

```text
app/Policies/TeacherAttendanceSessionPolicy.php
app/Policies/TeacherAttendanceCorrectionPolicy.php
app/Policies/TeacherClassChangeRequestPolicy.php
```

### Jobs

```text
app/Jobs/MarkMissingTeacherCheckouts.php
app/Jobs/MarkAbsentTeacherSessions.php
app/Jobs/SendTeacherAttendanceNotification.php
app/Jobs/GenerateTeacherAttendanceReport.php
```

### Console Commands

```text
php artisan teacher-attendance:mark-absent
php artisan teacher-attendance:mark-missing-checkout
php artisan teacher-attendance:generate-sessions
```

## API Endpoints

### Teacher Endpoints

```text
GET    /api/teacher/attendance/schedules
GET    /api/teacher/attendance/sessions
GET    /api/teacher/attendance/sessions/{id}
POST   /api/teacher/attendance/sessions/{id}/check-in
POST   /api/teacher/attendance/sessions/{id}/check-out
GET    /api/teacher/attendance/history
POST   /api/teacher/attendance/corrections
GET    /api/teacher/attendance/corrections
POST   /api/teacher/class-change-requests
GET    /api/teacher/class-change-requests
```

### Admin Endpoints

```text
GET    /api/admin/teacher-attendance/dashboard
GET    /api/admin/teacher-attendance/live
GET    /api/admin/teacher-attendance/schedules
POST   /api/admin/teacher-attendance/schedules
PUT    /api/admin/teacher-attendance/schedules/{id}
GET    /api/admin/teacher-attendance/sessions
GET    /api/admin/teacher-attendance/sessions/{id}
PUT    /api/admin/teacher-attendance/sessions/{id}
POST   /api/admin/teacher-attendance/sessions/{id}/manual-check-in
POST   /api/admin/teacher-attendance/sessions/{id}/manual-check-out
POST   /api/admin/teacher-attendance/sessions/{id}/mark-permission
POST   /api/admin/teacher-attendance/sessions/{id}/mark-cancelled
GET    /api/admin/teacher-attendance/corrections
POST   /api/admin/teacher-attendance/corrections/{id}/approve
POST   /api/admin/teacher-attendance/corrections/{id}/reject
GET    /api/admin/teacher-attendance/class-change-requests
POST   /api/admin/teacher-attendance/class-change-requests/{id}/approve
POST   /api/admin/teacher-attendance/class-change-requests/{id}/reject
GET    /api/admin/teacher-attendance/reports/daily
GET    /api/admin/teacher-attendance/reports/monthly
GET    /api/admin/teacher-attendance/reports/semester
GET    /api/admin/teacher-attendance/reports/teaching-hours
GET    /api/admin/teacher-attendance/reports/late
GET    /api/admin/teacher-attendance/reports/absent
```

## Attendance Calculation Rules

### Late Minutes

```text
late_minutes = max(0, check_in_time - scheduled_start_time)
```

### Very Late

Recommended rule:

```text
check_in_time > check_in_closes_at
```

Or configurable:

```text
late_minutes > 15
```

### Early Leave Minutes

```text
early_leave_minutes = max(0, scheduled_end_time - check_out_time)
```

### Teaching Duration

```text
teaching_duration_minutes = check_out_time - check_in_time
```

### Actual Teaching Hours

```text
actual_teaching_hours = teaching_duration_minutes / 60
```

### Attendance Percentage

For a date range:

```text
attendance_percentage = completed_or_valid_sessions / total_required_sessions * 100
```

Valid sessions can include:

- `on_time`
- `late`
- `very_late`
- `teaching`
- `completed`
- `early_leave`
- `permission`

Excluded from required sessions:

- `cancelled`
- approved `rescheduled` original sessions

## React Frontend Pages

If a React frontend is added later, use these pages.

### Teacher Pages

```text
/teacher/attendance/today
/teacher/attendance/schedule
/teacher/attendance/history
/teacher/attendance/corrections
/teacher/attendance/class-change-requests
```

Teacher UI components:

- Today’s teaching cards
- Check-in button
- Check-out button
- Attendance status badge
- Late minutes indicator
- Correction request modal
- Reschedule/cancellation request form
- History table with filters

### Admin Pages

```text
/admin/teacher-attendance/dashboard
/admin/teacher-attendance/live
/admin/teacher-attendance/schedules
/admin/teacher-attendance/sessions
/admin/teacher-attendance/corrections
/admin/teacher-attendance/class-change-requests
/admin/teacher-attendance/reports
```

Admin UI components:

- Live monitoring table
- Today schedule timeline
- Present/late/absent counters
- Currently teaching list
- Completed sessions table
- Correction approval queue
- Class-change approval queue
- Report filter panel
- Export buttons

## Dashboard Widgets

Admin dashboard:

- Today’s teaching schedules
- Present teachers
- Late teachers
- Very late teachers
- Absent teachers
- Currently teaching
- Completed sessions
- Missing check-outs
- Pending correction requests
- Pending cancellation/reschedule requests
- Attendance percentage by department
- Teaching hours by teacher

Teacher dashboard:

- Next class
- Current attendance status
- Check-in/check-out action
- This week teaching hours
- This month attendance percentage
- Pending requests

## Reports and Analytics

### Daily Teacher Attendance Report

Columns:

- Teacher
- Department
- Subject
- Class group
- Room
- Scheduled time
- Check-in time
- Check-out time
- Status
- Late minutes
- Early leave minutes

### Monthly Teacher Attendance Report

Metrics:

- Total scheduled sessions
- Completed sessions
- Late sessions
- Very late sessions
- Absent sessions
- Permission sessions
- Total teaching hours
- Attendance percentage

### Semester Teaching Report

Metrics:

- Total assigned classes
- Total required sessions
- Total completed sessions
- Replacement sessions
- Cancelled sessions
- Rescheduled sessions
- Total teaching hours

### Late Attendance Report

Filters:

- Date range
- Department
- Teacher
- Subject
- Late threshold

### Absent Teacher Report

Show:

- Missed class
- Teacher
- Subject
- Class group
- Room
- Date/time
- Reason if correction or permission exists

## Notifications

Use existing Telegram infrastructure through `TelegramService`, then add a wrapper service:

```text
TeacherAttendanceNotificationService
```

Notification triggers:

- Teacher checked in late.
- Teacher missed check-in.
- Teacher missed check-out.
- Teacher requested cancellation.
- Teacher requested reschedule.
- Correction approved.
- Correction rejected.
- Replacement session approved.

Channels:

- In-system notification table
- Email
- Telegram

Recommended messages:

```text
Late Attendance: {teacher} checked in {late_minutes} minutes late for {subject}.
Missing Check-In: {teacher} has not checked in for {subject} scheduled at {time}.
Correction Approved: Your attendance correction for {date} was approved.
Class Rescheduled: {subject} moved from {old_time} to {new_time}.
```

## Security and Audit Logging

Rules:

- Teachers can only view and update their own attendance sessions.
- Teachers cannot approve their own corrections.
- Admin and super admin can review all records.
- Every manual override must create an audit log.
- Every approval/rejection must store reviewer, timestamp, and note.
- Location data must be optional or controlled by university policy.
- Do not allow destructive deletes for attendance records; use status changes and logs.

Audit log required for:

- Check-in
- Check-out
- Manual admin edit
- Status change
- Correction submission
- Correction approval/rejection
- Cancellation request
- Reschedule request
- Replacement session creation

## Integration With Existing Project

Current project already has:

- `Teacher`
- `Subject`
- `ClassRoom`
- `ClassGroup`
- `AttendanceSession`
- `TelegramService`
- admin and teacher roles
- Laravel Sanctum API
- Blade admin UI

Recommended implementation approach:

1. Add teacher attendance tables.
2. Add models and relationships.
3. Add `TeacherAttendanceService`.
4. Add teacher check-in/check-out API.
5. Add admin monitoring API.
6. Add correction and class-change request APIs.
7. Add Blade pages first to match the current app.
8. Add React later only if the whole frontend is moved to React.

## Suggested Implementation Phases

### Phase 1: Database and Core API

- Create migrations.
- Create models.
- Create schedule/session generation service.
- Add check-in/check-out logic.
- Add admin list and detail APIs.

### Phase 2: Admin UI

- Live monitoring page.
- Today schedule page.
- Manual override actions.
- Filters by date, teacher, subject, class, semester, status.

### Phase 3: Teacher UI

- Today’s classes.
- Check-in/check-out.
- Attendance history.
- Correction request form.

### Phase 4: Requests

- Correction request workflow.
- Cancellation request workflow.
- Reschedule request workflow.
- Replacement session creation.

### Phase 5: Reports and Notifications

- Daily report.
- Monthly report.
- Semester report.
- Teaching hours report.
- Telegram/email/system notifications.

### Phase 6: Analytics and Optimization

- Dashboard widgets.
- Department analytics.
- Index tuning.
- Cached report generation.
- Queued notifications.

## Future Scalability Recommendations

- Keep student attendance and teacher attendance separate.
- Use services for business rules, not large controllers.
- Use queued jobs for report generation and notifications.
- Add database indexes before large imports.
- Store audit logs append-only.
- Make attendance thresholds configurable per semester or department.
- Add import support for teaching schedules from Excel.
- Add room conflict validation.
- Add academic calendar integration.
- Add replacement-class linkage for accurate semester teaching-hour totals.
- Add policies before exposing APIs to mobile apps.
- Add tests for check-in/check-out edge cases.

## Important Edge Cases

- Teacher has two classes on the same day.
- Teacher has overlapping schedules.
- Teacher checks in but forgets check-out.
- Teacher checks in after cutoff.
- Admin cancels class after teacher already checked in.
- Internet problem during check-in.
- Class is rescheduled to another room.
- Replacement session belongs to a previous cancelled session.
- Teacher teaches multiple class groups in one session.
- Teacher has permission approved before the class date.

