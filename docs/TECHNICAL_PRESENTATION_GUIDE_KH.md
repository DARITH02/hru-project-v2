# មគ្គុទ្ទេសក៍បង្ហាញបច្ចេកទេស HRU-ATMS

## 1. អត្តសញ្ញាណគម្រោង

**ឈ្មោះគម្រោង:** HRU-ATMS, Human Resource University Academic Attendance Management System

**ប្រភេទគម្រោង:** ប្រព័ន្ធវេបសម្រាប់គ្រប់គ្រងវត្តមានសិក្សា វត្តមានគ្រូ កំណត់ត្រាសិស្ស ឯកសារ Chat របាយការណ៍ និងការងាររដ្ឋបាល។

**គោលបំណងសំខាន់:** ជំនួសការគ្រប់គ្រងវត្តមាន និងកំណត់ត្រាសិក្សាដោយដៃ ជាប្រព័ន្ធឌីជីថលមជ្ឈមណ្ឌល ដែលគាំទ្រ QR check-in ការចូលប្រើតាមតួនាទី ការតាមដានផ្ទាល់ របាយការណ៍ ការជូនដំណឹង និងប្រតិបត្តិការ backup/restore។

**អ្នកប្រើប្រាស់សំខាន់ៗ:**

- **Super admin:** គ្រប់គ្រងប្រព័ន្ធពេញលេញ សកម្មភាពលុប/កែប្រែសំខាន់ៗ អនុម័តគណនី រៀបចំទិន្នន័យសិក្សា របាយការណ៍ និងការកំណត់ប្រព័ន្ធ។
- **Admin:** គ្រប់គ្រងការងារសិក្សាប្រចាំថ្ងៃ ពិនិត្យវត្តមាន បង្កើតរបាយការណ៍ តាមដានវត្តមានគ្រូ គ្រប់គ្រងឯកសារ និងប្រតិបត្តិការ backup/restore។
- **Teacher:** មើលថ្នាក់ដែលបានចាត់តាំង បើកវគ្គវត្តមាន QR គ្រប់គ្រងពិន្ទុសិស្ស check-in/check-out វត្តមានគ្រូ ស្នើសុំកែតម្រូវ គ្រប់គ្រងឯកសារ និងប្រើ Chat។
- **Student:** Check-in វត្តមានតាម QR មើលប្រវត្តិវត្តមាន ទិន្នន័យថ្នាក់ និងចូលមើលឯកសារ។

## 2. ស្ថាបត្យកម្មប្រព័ន្ធកម្រិតខ្ពស់

ប្រព័ន្ធនេះត្រូវបានសាងសង់ជា Laravel backend application ដែលមាន web pages, protected REST APIs, realtime broadcasting, queue jobs, scheduled jobs និងសេវា integration ជាជម្រើស។

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

សេវាគាំទ្រ:

```text
Redis       -> cache, queue, session storage in Docker
Reverb      -> realtime WebSocket broadcasting
Queue       -> background jobs such as backup, restore, cleanup, notifications
Scheduler   -> recurring attendance, chat cleanup, and backup tasks
Nginx/PHP   -> web server and PHP runtime inside Docker
phpMyAdmin  -> local database administration
```

## 3. បច្ចេកវិទ្យាសំខាន់ៗដែលបានប្រើ

### Backend

| បច្ចេកវិទ្យា | កំណែ / Package | គោលបំណង |
| --- | --- | --- |
| PHP | ត្រូវការ 8.2+, Docker ប្រើ PHP 8.3 FPM | ភាសាសំខាន់សម្រាប់ backend |
| Laravel | 12.x | Web framework សម្រាប់ routing, controllers, models, validation, queues, scheduler |
| Laravel Sanctum | 4.x | API authentication និង personal access tokens |
| Laravel Reverb | 1.5+ | WebSocket server សម្រាប់ realtime updates |
| Eloquent ORM | Laravel built-in | Database models និង relationships |
| Laravel Queues | Database ឬ Redis | Background jobs |
| Laravel Scheduler | Laravel built-in | Commands ដំណើរការស្វ័យប្រវត្តិតាមកាលវិភាគ |
| Blade | Laravel built-in | Web pages ដែល render ពី server |
| Middleware | Custom + Laravel built-in | Role control, demo read-only mode, security headers, locale, maintenance |
| Policies | Laravel built-in | Authorization សម្រាប់ chat conversation និង message |

### Database និង Storage

| បច្ចេកវិទ្យា | គោលបំណង |
| --- | --- |
| MySQL 8.0 | Relational database សំខាន់ |
| Redis 7 | Cache, queue និង session storage ក្នុង Docker |
| Laravel migrations | គ្រប់គ្រង database schema តាម version |
| Local filesystem storage | រក្សាទុកឯកសារ upload, generated files, backups |
| Google Drive integration | ជម្រើសសម្រាប់រក្សាទុក cloud backup |
| Cloudinary Laravel | ជម្រើសសម្រាប់ media/file cloud integration |

### Frontend

គម្រោងនេះមាន backend Blade pages និងភ្ជាប់ជាមួយ frontend application ដាច់ដោយឡែកផងដែរ។

| បច្ចេកវិទ្យា | ទីតាំង | គោលបំណង |
| --- | --- | --- |
| Blade templates | `resources/views` | ទំព័រ Admin, teacher, student, auth, document, PDF និង report |
| Vite | Backend និង frontend | Asset build tool |
| Tailwind CSS | Backend និង frontend | Utility-first styling |
| JavaScript | `resources/js` | Browser behavior និង API calls |
| React | Backend widgets + frontend repo | Interactive dashboards, scanner pages, frontend SPA |
| React Router | Frontend repo | Client-side navigation |
| Axios | Backend និង frontend | HTTP API calls |
| Laravel Echo | Backend និង frontend | WebSocket client |
| Pusher JS | Backend និង frontend | WebSocket protocol client ដែល compatible ជាមួយ Reverb |
| QR libraries | `qrcode`, `qrcode.react`, scanner package | QR generation និង scanning |
| Recharts | Frontend repo | Charts និង dashboard visualization |
| Lucide React | Frontend repo | Icon set |

### របាយការណ៍ និងឯកសារ

| បច្ចេកវិទ្យា | គោលបំណង |
| --- | --- |
| barryvdh/laravel-dompdf | បង្កើត PDF reports |
| maatwebsite/excel | Excel import/export |
| Blade PDF views | HTML templates សម្រាប់ PDF output |
| Laravel file responses | Preview និង download documents |

### DevOps និង Runtime

| បច្ចេកវិទ្យា | គោលបំណង |
| --- | --- |
| Docker Compose | Local multi-service environment |
| Dockerfile | Build PHP, Nginx, Node, Composer, extensions និង app assets |
| Nginx | Serve Laravel តាម PHP-FPM |
| Composer | PHP dependency manager |
| npm | JavaScript dependency manager |
| PHPUnit | Automated backend tests |
| Laravel Pint | PHP code style formatter |
| Laravel Pail | Local log viewer |
| phpMyAdmin | Local MySQL management UI |

### Optional Integrations

| Integration | គោលបំណង |
| --- | --- |
| Telegram Bot API | ផ្ញើការជូនដំណឹងអំពីវត្តមាន និងរបាយការណ៍ |
| Gmail SMTP | Email OTP និង email messages |
| Google Drive API | Optional backup storage |
| Groq OpenAI-compatible API | Optional AI assistant provider |
| Google GenAI package in frontend | Optional AI/client-side feature support |
| Cloudinary | Optional cloud media storage |

## 4. រចនាសម្ព័ន្ធ Directory របស់គម្រោង

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

## 5. Modules សំខាន់ៗរបស់ Application

### 5.1 Authentication និង User Management

ប្រព័ន្ធគាំទ្រ login, logout, profile, registration, demo login និង role-based access។

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `AuthController` គ្រប់គ្រង API authentication។
- `WebAuthController` គ្រប់គ្រង web login និង registration pages។
- `LoginRequest`, `RegisterRequest` និង request classes ផ្សេងៗ validate inputs។
- Sanctum ការពារ API routes ដោយប្រើ `auth:sanctum`។
- `RoleMiddleware` កំណត់ routes ទៅតាម roles ដែលអនុញ្ញាត។
- `DemoReadOnlyMiddleware` ការពារការសរសេរ/កែប្រែក្នុង demo mode។
- User roles រួមមាន admin, super admin, teacher និងការចូលប្រើដែលពាក់ព័ន្ធនឹង student។

### 5.2 Admin Management

Admins គ្រប់គ្រងមូលដ្ឋានសិក្សារបស់ប្រព័ន្ធ:

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

Admin module ត្រូវបានផ្គត់ផ្គង់ជាចម្បងដោយ:

- `AdminController`
- `AdminTeacherAttendanceController`
- `AdminDocumentController`
- `BackupController`
- `RestoreController`
- Admin Blade views ក្នុង `resources/views/admin`

### 5.3 Teacher Portal

Teachers អាច:

- មើលថ្នាក់ដែលបានចាត់តាំង។
- មើលសិស្សក្នុងថ្នាក់ដែលបានចាត់តាំង។
- បង្កើត QR codes សម្រាប់ attendance sessions។
- តាមដាន live check-ins។
- Manual check-in សិស្សនៅពេលចាំបាច់។
- Update attendance session status។
- Export attendance។
- គ្រប់គ្រង semester scores។
- Upload និង view documents។
- ប្រើ teacher attendance check-in/check-out។
- ដាក់ correction និង class change requests។
- ប្រើ chat ជាមួយ admins/teachers។

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `TeacherController`
- `TeacherAttendanceController`
- `TeacherDocumentController`
- `AttendanceService`
- `TeacherAttendanceService`
- `SemesterAttendanceScoreService`
- Teacher Blade views ក្នុង `resources/views/teacher`

### 5.4 Student Attendance

Students អាច:

- Scan QR code សម្រាប់ active attendance session។
- Submit attendance verification។
- មើល active sessions។
- មើល class attendance history។
- ចូលមើល documents។

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `AttendanceController`
- `StudentDocumentController`
- Public scan route: `/student/scan/{sessionId}`
- Verify route: `/api/student/verify`
- Attendance models: `Attendance`, `AttendanceSession`, `Student`

### 5.5 Teacher Attendance Module

Module នេះតាមដានវត្តមានការងាររបស់គ្រូ ដាច់ដោយឡែកពីវត្តមានសិស្សក្នុងថ្នាក់។

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

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `AdminTeacherAttendanceController`
- `TeacherAttendanceController`
- `TeacherAttendanceService`
- `TeacherAttendanceNotificationService`
- `TeacherAttendanceUpdated` event
- Models ដូចជា `TeacherAttendanceSession`, `TeacherAttendanceLog`, `TeacherAttendanceQrToken`, `TeacherAttendanceCorrection` និង `TeacherClassChangeRequest`

### 5.6 Realtime Chat

Chat module គាំទ្រការទំនាក់ទំនងរវាង teacher/admin។

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

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `ChatController`
- `ChatService`
- `ChatRepository`
- Chat request validation classes
- Chat events: `MessageSent`, `MessageUpdated`, `MessageDeleted`, `UserTyping`, `UserPresenceChanged` និង receipt/reaction events
- Chat models នៅក្រោម `app/Models/Chat`
- Broadcast channels នៅក្រោម `routes/channels.php`

### 5.7 Reports, Exports, និង PDFs

ប្រព័ន្ធបង្កើតលទ្ធផលសិក្សា និងវត្តមានសម្រាប់ការបង្ហាញ ការត្រួតពិនិត្យ និងការងាររដ្ឋបាល។

ឧទាហរណ៍ report/export:

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

ការអនុវត្តបច្ចេកទេស:

- Excel files ប្រើ `maatwebsite/excel`។
- PDFs ប្រើ DomPDF ជាមួយ Blade templates។
- Export classes ត្រូវបានរក្សាទុកក្នុង `app/Exports`។
- PDF templates ត្រូវបានរក្សាទុកក្នុង `resources/views/pdf` និង `resources/views/admin/exports`។

### 5.8 Documents

ការគ្រប់គ្រងឯកសារមានសម្រាប់ admins, teachers និង students។

Features:

- Admin document management
- Teacher document upload/view
- Student document preview/download
- Role-based document access

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

- `Document` model
- `AdminDocumentController`
- `TeacherDocumentController`
- `StudentDocumentController`
- Blade views នៅក្រោម `resources/views/*/documents`

### 5.9 Backup and Restore

Backend មាន subsystem backup និង restore ដែលរឹងមាំ។

Features:

- Full backups
- Incremental backups
- Weekly and monthly backups
- Backup cleanup
- Backup verification
- Restore test jobs
- Optional Google Drive backup integration
- Backup/restore logs

ផ្នែកបច្ចេកទេសសំខាន់ៗ:

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

Scheduled backup tasks ត្រូវបានកំណត់ក្នុង `routes/console.php`។

## 6. សេចក្តីសង្ខេប Database Design

Database ត្រូវបានគ្រប់គ្រងតាម Laravel migrations។ ក្រុម entity សំខាន់ៗរួមមាន:

### User និង access control tables

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

### System និង integration tables

- `settings`
- `cache`
- `jobs`
- `failed_jobs`
- `telegram_bots`
- `documents`
- `backup_restore_logs`
- `user_locations`

## 7. API Design

API file សំខាន់គឺ `routes/api.php`។

### Public និង auth endpoints

- `GET /api/` ពិនិត្យថា API កំពុងដំណើរការ។
- `POST /api/login` login user។
- `GET /api/check-status` ពិនិត្យ system status។
- `GET /api/branding` ត្រឡប់ branding information។

### Protected shared endpoints

Protected routes ប្រើ:

```text
auth:sanctum
demo.readonly
```

Shared protected endpoints រួមមាន:

- `GET /api/profile`
- `POST /api/logout`
- Chat endpoints នៅក្រោម `/api/chat`

### Teacher endpoints

Teacher routes រួមមាន:

- `/api/teacher/summary`
- `/api/teacher/classes`
- `/api/teacher/session/{sessionId}/qr`
- `/api/teacher/session/{sessionId}/monitor`
- `/api/teacher/session/{sessionId}/checkin`
- `/api/teacher/semesters`
- `/api/teacher/attendance/*`
- `/api/teacher/documents`

### Admin endpoints

Admin routes រួមមាន:

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

Student routes រួមមាន:

- `/api/student/portal`
- `/api/student/active-session`
- `/api/student/classes`
- `/api/student/classes/{classId}/history`
- `/api/student/documents`
- `/api/student/scan/{sessionId}`
- `/api/student/verify`
- `/api/student/history`

## 8. Realtime Architecture

Realtime features ប្រើ Laravel Reverb, Laravel Echo និង Pusher JS។

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

## 9. Background Jobs និង Scheduled Tasks

ប្រព័ន្ធប្រើ queues សម្រាប់ការងារដំណើរការយូរ ឬ background work និងប្រើ scheduler សម្រាប់ recurring tasks។

Scheduled tasks ពី `routes/console.php`:

| កាលវិភាគ | ការងារ |
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

Docker Compose file ដំណើរការ containers ដាច់ដោយឡែកសម្រាប់:

- `queue`
- `scheduler`
- `reverb`

រចនាបែបនេះបំបែក background work ចេញពី normal web requests។

## 10. Security Design

Security controls រួមមាន:

- Laravel Sanctum token authentication។
- Role-based route protection។
- Destructive actions សម្រាប់ super admin ប៉ុណ្ណោះ។
- Demo read-only mode សម្រាប់ project review។
- Request validation តាម form request classes។
- Rate limiting លើ login, chat send, typing, activity និង location recording។
- Broadcast channel authorization។
- Security headers middleware។
- Maintenance mode middleware។
- Password hashing ដោយ bcrypt។
- Feature flags ផ្អែកលើ environment។
- Activity logs និង backup/restore logs។

ច្បាប់សំខាន់សម្រាប់ production:

កុំ commit secrets ពិតនៅក្នុង `.env`។ ត្រូវ rotate email app passwords, API keys, bot tokens, cloud credentials និង database passwords ដែលបានបង្ហាញ មុនពេល deploy ឬ share ជាសាធារណៈ។

## 11. Local Development Setup

### Backend ជាមួយ Docker

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

### Backend ដោយមិនប្រើ Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

### React frontend ដាច់ដោយឡែក

Sibling frontend project ដំណើរការលើ port 3000។

```bash
cd ../hru-project-v2-front
npm install
npm run dev
```

Expected frontend URL:

```text
http://localhost:3000
```

Backend `.env` ប្រើ `FRONTEND_URL` និង Sanctum stateful domains ដើម្បីឱ្យ browser-based authentication អាចដំណើរការរវាង backend និង frontend ports។

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

ក្រុម `.env` សំខាន់ៗ:

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

### Auth និង local features

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

## 14. Testing និង Quality

Commands ដែលអាចប្រើបាន:

```bash
composer test
php artisan test
./vendor/bin/pint
npm run build
```

ការត្រួតពិនិត្យដែលណែនាំមុនពេល presentation ឬ submission:

- Run database migrations ពី clean database។
- Run backend tests។
- Build frontend/backend assets។
- បញ្ជាក់ថា login ដំណើរការសម្រាប់ admin, teacher និង student flows។
- បញ្ជាក់ថា QR generation និង scanning ដំណើរការ។
- បញ្ជាក់ថា Reverb កំពុងដំណើរការសម្រាប់ realtime modules។
- បញ្ជាក់ថា report export ដំណើរការសម្រាប់ PDF និង Excel។
- បញ្ជាក់ថា backup commands មិន fail។

## 15. Deployment Notes

សម្រាប់ production:

- កំណត់ `APP_ENV=production`។
- កំណត់ `APP_DEBUG=false`។
- បិទ demo login លុះត្រាតែត្រូវការសម្រាប់ review។
- បិទ public registration ប្រសិនបើការបង្កើតគណនីត្រូវគ្រប់គ្រងដោយ admin។
- ប្រើ database passwords ដែលខ្លាំង។
- Rotate development credentials ទាំងអស់។
- ប្រើ HTTPS សម្រាប់ backend និង frontend។
- កំណត់ `APP_URL`, `FRONTEND_URL` និង `SANCTUM_STATEFUL_DOMAINS` ឱ្យត្រឹមត្រូវ។
- Run `php artisan config:cache`, `route:cache` និង `view:cache` បន្ទាប់ពី configuration។
- Run queue, scheduler និង Reverb ជា managed services។
- Configure backup storage និង verify restore tests។

## 16. ចំណុចនិយាយសម្រាប់ Presentation

### សេចក្តីសង្ខេបបច្ចេកទេសខ្លី

HRU-ATMS គឺជា academic attendance management platform ដែលសាងសង់លើ Laravel 12 និង React។ ប្រព័ន្ធប្រើ MySQL សម្រាប់ structured academic data, Redis សម្រាប់ caching និង queues, Sanctum សម្រាប់ secure API authentication, Reverb សម្រាប់ realtime WebSocket features និង Docker Compose សម្រាប់ local deployment។ ប្រព័ន្ធគាំទ្រ student QR attendance, teacher attendance, admin management, reports, chat, documents, notifications និង backup/restore workflows។

### ហេតុអ្វីបានជាជ្រើស Laravel

- ផ្តល់ routing, authentication, validation, ORM, queues, scheduler និង security features ក្នុង framework មួយដែល mature។
- គាំទ្រទាំង server-rendered Blade pages និង API-based frontend integration។
- ដំណើរការល្អជាមួយ MySQL, Redis, Docker និង realtime broadcasting។
- ជួយឱ្យការអភិវឌ្ឍ admin workflows និង reporting features លឿនឡើង។

### ហេតុអ្វីបានជាជ្រើស MySQL

- Attendance, users, classes, subjects និង reports គឺជា relational data។
- MySQL គាំទ្រ joins, indexes, transactions និង structured constraints ដោយទុកចិត្តបាន។
- ងាយពិនិត្យ locally ជាមួយ phpMyAdmin។

### ហេតុអ្វីបានជាជ្រើស Redis

- បង្កើន performance សម្រាប់ cache/session use។
- គាំទ្រ queue-backed background work។
- ជួយបំបែក heavy operations ចេញពី normal web requests។

### ហេតុអ្វីបានជាជ្រើស Reverb

- Native Laravel realtime WebSocket server។
- ដំណើរការជាមួយ Laravel Echo និង Pusher JS។
- មានប្រយោជន៍សម្រាប់ live teacher attendance, chat, read receipts, typing និង presence។

### ហេតុអ្វីបានជាជ្រើស Docker

- ធ្វើឱ្យ teammates run project បានងាយ ដោយប្រើ versions ដូចគ្នា។
- រួមបញ្ចូល app, database, Redis, Reverb, queue worker, scheduler និង phpMyAdmin ក្នុង stack តែមួយ។
- កាត់បន្ថយបញ្ហា setup រវាងកុំព្យូទ័រផ្សេងៗ។

## 17. ឧទាហរណ៍ការបែងចែកភារកិច្ចសមាជិកក្រុម

| ផ្នែក | អ្នកទទួលខុសត្រូវដែលណែនាំ |
| --- | --- |
| Backend API and database | Laravel controllers, services, models, migrations |
| Frontend UI | React/Blade pages, Tailwind, dashboards, scanner screens |
| Attendance workflow | QR generation, validation, sessions, logs |
| Teacher attendance | Schedules, check-in/out, corrections, realtime monitor |
| Reports | PDF, Excel, charts, academic summaries |
| Security and testing | Roles, middleware, validation, tests |
| Deployment | Docker, environment, backup, production settings |

## 18. គ្រោង Slide ដែលបានណែនាំ

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

## 19. គំនិតកែលម្អអនាគត

- Mobile app សម្រាប់ students និង teachers។
- GPS/geofence verification ដែលខ្លាំងជាងមុនសម្រាប់ attendance។
- Face verification ពេល check-in។
- Analytics dashboards បន្ថែម។
- Notification center សម្រាប់ users ទាំងអស់។
- Offline attendance mode ជាមួយ later sync។
- Fine-grained permission management។
- Audit dashboard សម្រាប់ security និង administrative actions។
- Production cloud deployment ជាមួយ automated CI/CD។

