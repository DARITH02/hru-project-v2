# HRU-ATMS Technical Presentation Slide Structure

This file is a slide-ready structure based on `TECHNICAL_PRESENTATION_GUIDE.md`. Use it to create slides in PowerPoint, Canva, Google Slides, or any presentation tool.

## Recommended Presentation Format

**Audience:** Team members, classmates, thesis advisor, or project reviewers

**Duration:** 10-15 minutes

**Recommended slide count:** 15 slides

**Style:** Technical but easy to understand. Keep each slide short and explain details verbally.

## Slide 1: Project Title

**Title:** HRU-ATMS Technical Overview

**Subtitle:** Academic Attendance Management System

**Key points:**

- Web-based academic attendance and administration platform
- Supports students, teachers, admins, and super admins
- Built with Laravel, React, MySQL, Redis, Reverb, and Docker

**Suggested visual:**

- HRU logo or system dashboard screenshot
- Simple project name and technology badges

**Speaker note:**

Introduce the project as a centralized digital platform for managing attendance, academic records, reports, teacher attendance, documents, chat, and backup workflows.

## Slide 2: Problem Statement

**Title:** Problem We Are Solving

**Key points:**

- Manual attendance is slow and difficult to verify
- Paper records are hard to search, summarize, and audit
- Admins need reliable reports for academic decisions
- Teachers need faster class attendance and score workflows
- Students need transparent attendance history

**Suggested visual:**

- Before/after diagram: manual paperwork -> digital system

**Speaker note:**

Explain that the system reduces manual work, improves accuracy, and gives every role a clearer workflow.

## Slide 3: Project Objectives

**Title:** Project Objectives

**Key points:**

- Replace manual attendance with QR-based check-in
- Centralize academic data management
- Provide role-based dashboards
- Generate PDF and Excel reports
- Support realtime monitoring and chat
- Add backup/restore for data protection

**Suggested visual:**

- Six objective icons in a grid

**Speaker note:**

Keep this slide focused on what the system must achieve, not the implementation details yet.

## Slide 4: User Roles

**Title:** Main User Roles

**Key points:**

- **Super Admin:** Full control, approvals, destructive actions, system settings
- **Admin:** Academic management, reports, documents, attendance review
- **Teacher:** Assigned classes, QR sessions, scores, teacher attendance, chat
- **Student:** QR check-in, attendance history, document access

**Suggested visual:**

- Four-column role card layout

**Speaker note:**

Mention that role-based access is important because each user should only see and perform actions related to their responsibilities.

## Slide 5: System Architecture

**Title:** High-Level Architecture

**Key points:**

- Frontend and browser clients call Laravel routes
- Controllers validate requests and apply middleware
- Services handle business logic
- Eloquent models communicate with MySQL
- Redis, Reverb, Queue, and Scheduler support performance and automation

**Suggested visual:**

```text
React / Browser / Blade
        |
Laravel Routes
        |
Controllers + Middleware
        |
Services + Repositories
        |
Eloquent Models
        |
MySQL
```

**Speaker note:**

Explain the layered design from UI to database. This helps the audience understand where each part of the code belongs.

## Slide 6: Technology Stack

**Title:** Technology Stack

**Key points:**

- **Backend:** PHP 8.2+, Laravel 12, Sanctum, Reverb
- **Frontend:** React, Blade, Vite, Tailwind CSS, Axios
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis
- **Reports:** DomPDF, Maatwebsite Excel
- **Runtime:** Docker Compose, Nginx, PHP-FPM

**Suggested visual:**

- Technology stack grouped by layer

**Speaker note:**

Do not list every package. Focus on the major technologies and why they are used.

## Slide 7: Backend Structure

**Title:** Laravel Backend Structure

**Key points:**

- `routes/api.php` defines REST API endpoints
- `routes/web.php` defines web pages
- `app/Http/Controllers` handles requests
- `app/Services` contains business logic
- `app/Models` maps database tables
- `app/Jobs` and `app/Console/Commands` handle background work

**Suggested visual:**

- Project folder tree with highlighted backend folders

**Speaker note:**

Show that the backend is organized by responsibility, which makes it easier for team members to maintain.

## Slide 8: Core Modules

**Title:** Core Application Modules

**Key points:**

- Authentication and user management
- Admin management
- Teacher portal
- Student attendance
- Teacher attendance
- Realtime chat
- Reports and exports
- Documents
- Backup and restore

**Suggested visual:**

- Module map around the HRU-ATMS system name

**Speaker note:**

This is the feature overview slide. Each module can be explained briefly, then deeper slides cover the most important workflows.

## Slide 9: QR Student Attendance Workflow

**Title:** Student QR Attendance Workflow

**Key points:**

1. Teacher opens an attendance session
2. System generates QR code
3. Student scans QR code
4. System validates session, time, enrollment, and status
5. Attendance record is saved
6. Teacher/admin can monitor results

**Suggested visual:**

```text
Teacher Session -> QR Code -> Student Scan -> Validation -> Attendance Record -> Report
```

**Speaker note:**

Explain that QR check-in improves speed and reduces manual errors, while backend validation protects the workflow.

## Slide 10: Teacher Attendance Workflow

**Title:** Teacher Attendance Workflow

**Key points:**

- Admin creates or syncs teacher schedules
- Teacher checks in using QR or session action
- Teacher checks out after required class/work time
- Correction requests handle special cases
- Admin approves or rejects corrections and class changes
- Realtime dashboard shows current attendance status

**Suggested visual:**

- Timeline: Schedule -> Check-in -> Check-out -> Correction -> Admin review

**Speaker note:**

Clarify that teacher attendance is separate from student attendance and has its own sessions, logs, QR tokens, and approval workflows.

## Slide 11: API and Security

**Title:** API Design and Security

**Key points:**

- APIs are defined in `routes/api.php`
- Authentication uses Laravel Sanctum
- Protected routes use `auth:sanctum`
- Role checks use `RoleMiddleware`
- Demo mode uses `DemoReadOnlyMiddleware`
- Validation uses Laravel Form Request classes
- Rate limits protect login, chat, typing, activity, and location endpoints

**Suggested visual:**

- Lock icon beside API route groups: public, protected, role-based

**Speaker note:**

Mention that security is built into the route layer, middleware layer, validation layer, and authorization layer.

## Slide 12: Realtime Features

**Title:** Realtime Communication

**Key points:**

- Laravel Reverb provides WebSocket server
- Laravel Echo and Pusher JS connect the browser to realtime channels
- Used for live teacher attendance dashboard
- Used for chat messages, typing, receipts, reactions, and presence

**Suggested visual:**

```text
Laravel Event -> Reverb -> Echo Client -> Browser UI Update
```

**Speaker note:**

Realtime features make the system feel active because users can see updates without refreshing the page.

## Slide 13: Database Design

**Title:** Database Design Summary

**Key points:**

- User and access control tables
- Academic structure tables
- Student attendance tables
- Teacher attendance tables
- Chat tables
- System and integration tables

**Suggested visual:**

- Simplified ER diagram:

```text
Users -> Students / Teachers
Teachers -> Classes -> Attendance Sessions -> Attendance
Departments -> Majors -> Class Groups -> Students
Conversations -> Messages -> Receipts / Reactions
```

**Speaker note:**

Explain that MySQL is a good fit because most data is relational and requires joins, indexes, and consistency.

## Slide 14: Reports, Backup, and Automation

**Title:** Reports and Automation

**Key points:**

- PDF reports use DomPDF
- Excel import/export uses Maatwebsite Excel
- Queue jobs handle backup, restore, cleanup, and verification
- Scheduler runs teacher notifications, attendance sync, chat cleanup, and backups
- Optional Google Drive integration supports cloud backup

**Suggested visual:**

- Three columns: Reports, Jobs, Scheduler

**Speaker note:**

Explain that long-running or recurring tasks are separated from normal user requests to keep the application responsive.

## Slide 15: Deployment and Local Runtime

**Title:** Docker Runtime

**Key points:**

- Docker Compose runs all local services
- `app`: Laravel, Nginx, PHP-FPM
- `mysql`: database
- `redis`: cache, queues, sessions
- `reverb`: WebSocket server
- `queue`: background worker
- `scheduler`: recurring tasks
- `phpmyadmin`: database UI

**Suggested visual:**

```text
Docker Compose
  app
  mysql
  redis
  reverb
  queue
  scheduler
  phpmyadmin
```

**Speaker note:**

Mention that Docker helps all team members run the same environment with fewer setup problems.

## Slide 16: Testing and Quality

**Title:** Testing and Quality Checks

**Key points:**

- `php artisan test` for backend tests
- `composer test` for Laravel test script
- `./vendor/bin/pint` for PHP formatting
- `npm run build` for frontend/backend assets
- Manual checks for login, QR scan, realtime, reports, and backup commands

**Suggested visual:**

- Checklist layout

**Speaker note:**

This slide shows that the project is not only feature-focused but also maintainable and testable.

## Slide 17: Team Responsibilities

**Title:** Suggested Team Responsibilities

**Key points:**

- Backend API and database
- Frontend UI
- Attendance workflow
- Teacher attendance workflow
- Reports and exports
- Security and testing
- Deployment and backup

**Suggested visual:**

- Responsibility table

**Speaker note:**

Use this slide if presenting as a group. Each team member can explain their technical area.

## Slide 18: Future Improvements

**Title:** Future Improvements

**Key points:**

- Mobile app for students and teachers
- GPS/geofence attendance verification
- Face verification during check-in
- More analytics dashboards
- Offline attendance with later sync
- Fine-grained permission management
- Production cloud deployment with CI/CD

**Suggested visual:**

- Roadmap with short-term, mid-term, long-term improvements

**Speaker note:**

End by showing that the current system is functional but has clear paths for future development.

## Slide 19: Closing

**Title:** Summary

**Key points:**

- HRU-ATMS centralizes academic attendance workflows
- QR attendance improves speed and accuracy
- Role-based access separates responsibilities
- Realtime features improve monitoring and communication
- Reports, backup, and automation support administration
- Laravel, React, MySQL, Redis, Reverb, and Docker form the technical foundation

**Suggested visual:**

- Final architecture or dashboard screenshot

**Speaker note:**

Close with the main technical value: the project combines academic workflow management, secure APIs, realtime features, and deployable infrastructure in one system.

## Short 10-Slide Version

Use this version if presentation time is limited.

1. Project Title
2. Problem and Objectives
3. User Roles
4. System Architecture
5. Technology Stack
6. Core Modules
7. QR Attendance Workflow
8. Security, API, and Realtime
9. Reports, Backup, and Docker
10. Summary and Future Improvements

## Suggested Speaking Order for Team Members

| Speaker | Slides | Topic |
| --- | --- | --- |
| Member 1 | 1-3 | Introduction, problem, objectives |
| Member 2 | 4-6 | Roles, architecture, technology stack |
| Member 3 | 7-10 | Backend structure, modules, attendance workflows |
| Member 4 | 11-14 | API, security, realtime, database, reports |
| Member 5 | 15-19 | Docker, testing, responsibilities, future work, closing |

## Design Tips

- Keep each slide to 3-6 bullets.
- Use diagrams for architecture and workflow slides.
- Use screenshots for dashboard, QR scanning, reports, and login pages.
- Avoid copying long paragraphs from the technical guide into slides.
- Put detailed explanations in speaker notes instead of slide body text.
- Use the same colors, fonts, and icons across all slides.

