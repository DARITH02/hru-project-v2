# Project Proposal

## Project Title

**HRU Academic Attendance Management System (HRU-ATMS)**

## 1. Executive Summary

The HRU Academic Attendance Management System is a web-based platform designed to modernize attendance tracking and academic administration for a university environment. The system replaces paper-based attendance, manual record checking, scattered reports, and delayed administrative follow-up with a centralized digital platform.

The proposed system supports role-based access for super administrators, administrators, teachers, and students. It includes QR code attendance, academic class management, teacher attendance, student attendance monitoring, absence risk detection, blacklisting support, semester scoring, PDF and Excel report generation, Telegram notification integration, real-time communication, and secure deployment using Docker.

The main goal of the project is to improve accuracy, speed, transparency, and accountability in academic attendance management. By digitizing attendance workflows, the institution can reduce human error, save administrative time, identify at-risk students earlier, and generate reliable reports for academic decision-making.

## 2. Background

Many academic institutions still depend on manual attendance sheets, spreadsheet-based summaries, or disconnected systems. These methods create several problems:

- Attendance records can be lost, duplicated, or entered incorrectly.
- Teachers spend extra time checking attendance manually during class.
- Administrators must collect and verify attendance data from many sources.
- Students with repeated absences may be identified too late.
- Report generation is slow and often requires manual calculation.
- Academic departments lack real-time visibility into attendance issues.

HRU-ATMS addresses these issues by providing one integrated system for attendance, academic data, users, reports, alerts, and administrative control.

## 3. Problem Statement

The current manual or semi-manual attendance process is inefficient and difficult to monitor at scale. It can delay academic decisions, reduce data accuracy, and make it harder for administrators to identify students or teachers who need attention.

The key problem is the lack of a centralized, secure, and automated attendance management system that can:

- Verify attendance efficiently.
- Store attendance data reliably.
- Separate user permissions by role.
- Generate useful academic reports.
- Notify administrators about important issues.
- Support both student and teacher attendance workflows.

## 4. Project Objectives

### General Objective

To develop a secure, centralized, and user-friendly academic attendance management system for HRU that improves attendance tracking, reporting, and administrative decision-making.

### Specific Objectives

- To replace manual attendance checking with QR-based digital attendance.
- To allow teachers to manage attendance sessions and monitor student check-ins.
- To allow administrators to manage students, teachers, classes, subjects, departments, majors, groups, and semester assignments.
- To support teacher attendance check-in and check-out based on teaching schedules.
- To detect students who are at risk or blacklisted due to excessive absences.
- To generate PDF and Excel reports for students, subjects, departments, attendance issues, teacher attendance, and semester results.
- To provide secure authentication and role-based access control.
- To integrate Telegram notifications for important reports and attendance alerts.
- To support real-time communication between admins and teachers.
- To provide a Docker-based deployment environment for easier installation and maintenance.

## 5. Scope of the Project

### Included Scope

The project includes the following modules:

- Authentication and account approval
- Super admin, admin, teacher, and student roles
- Student management
- Teacher and instructor management
- Department, major, class group, subject, and class management
- Student attendance using QR code verification
- Teacher attendance based on teaching schedules
- Attendance session monitoring
- Student absence analysis and blacklist tracking
- Semester score and academic result management
- Report export as PDF and Excel
- Telegram bot integration
- Real-time chat API for admin and teacher communication
- System settings and branding
- Demo account support for reviewers
- Docker Compose deployment with MySQL, Redis, Nginx, queue worker, scheduler, Reverb, and phpMyAdmin

### Excluded Scope

The following items are not part of the current project scope but can be added in future versions:

- Native Android and iOS mobile applications
- Biometric attendance hardware integration
- Full payment or tuition management
- Advanced LMS features such as assignments and online exams
- AI-based face recognition
- Multi-campus enterprise administration with separate tenant databases

## 6. Target Users

### Super Administrator

The super administrator has the highest system permission. This user can manage all major data and perform restricted destructive actions such as deleting important records.

### Administrator

The administrator manages academic data, students, teachers, classes, attendance records, reports, and system settings.

### Teacher

The teacher can view assigned classes, generate or use QR attendance workflows, monitor attendance, update scores, and manage teacher attendance requests.

### Student

The student can check in through QR code attendance and access attendance-related information where enabled.

## 7. Proposed System Features

### 7.1 Authentication and Security

The system uses secure login, Laravel Sanctum authentication, role-based middleware, account approval, and protected routes.

Main features:

- Login and registration
- Super admin key registration option
- Admin account approval workflow
- Demo login mode for presentation and review
- Protected web and API routes
- Role-based access control
- Rate limiting for sensitive routes
- Secure session and API authentication

### 7.2 Admin Management Module

The admin module is the main control center of the system.

Main features:

- Manage students
- Manage instructors and teacher accounts
- Manage departments
- Manage majors
- Manage class groups
- Manage subjects
- Manage academic classes
- Assign semesters to classes
- Manage attendance sessions
- Manage student permissions
- Configure system settings
- Export academic data

### 7.3 Student Attendance Module

The student attendance module allows students to check in through QR code verification.

Main workflow:

1. Teacher or admin opens an attendance session.
2. The system generates a QR code.
3. Student scans the QR code.
4. The system validates session, time, enrollment, and optional location rules.
5. Attendance is recorded.
6. Teacher and admin can monitor attendance results.

Benefits:

- Faster check-in
- Less manual work for teachers
- Reduced attendance fraud
- More accurate attendance records
- Real-time monitoring

### 7.4 Teacher Attendance Module

The teacher attendance module tracks instructor attendance based on teaching schedules rather than fixed office hours.

Main features:

- Schedule-based teacher attendance
- QR check-in and check-out
- Late and early leave calculation
- Missing check-out tracking
- Teacher correction requests
- Class cancellation and reschedule requests
- Admin approval and rejection workflow
- Teacher attendance reports

### 7.5 Attendance Issues and Blacklist Module

The system helps administrators identify students with attendance problems.

Main features:

- Count absences by semester and academic year
- Identify at-risk students
- Identify blacklisted students
- Track restore history
- Limit repeated restores
- Generate attendance issue reports
- Send attendance issue reports through Telegram

### 7.6 Reports and Export Module

The system supports academic reporting in PDF and Excel formats.

Supported reports:

- Student list
- Instructor list
- Subject list
- Department list
- Class and course list
- Attendance records
- Attendance issues
- Semester results
- Subject scores
- Teacher attendance
- System summary reports

### 7.7 Telegram Notification Module

Telegram integration allows the system to send important reports and alerts to configured Telegram bots.

Main features:

- Configure Telegram bot accounts
- Set active bot
- Test bot connection
- Send reports to Telegram
- Send attendance alerts
- Notify about important academic issues

### 7.8 Real-Time Chat Module

The project includes backend support for a Messenger-style communication module between admins, super admins, and teachers.

Main features:

- User search
- Conversation creation
- Message sending
- Attachments
- Message editing and deletion
- Reactions
- Read and delivered receipts
- Typing indicators
- Presence tracking
- Real-time broadcasting through Laravel Reverb

### 7.9 Dashboard and Analytics

The dashboard provides summary information for decision-making.

Dashboard examples:

- Total students
- Total teachers
- Total subjects
- Attendance activity
- At-risk student count
- Blacklisted student count
- Recent attendance records
- Teacher attendance status
- Semester and subject performance

## 8. System Workflow

### Admin Setup Workflow

1. Admin creates departments.
2. Admin creates majors under departments.
3. Admin creates class groups.
4. Admin creates subjects.
5. Admin creates teacher and student accounts.
6. Admin creates academic classes.
7. Admin assigns subjects, teachers, groups, and schedules.

### Student Attendance Workflow

1. Teacher opens an attendance session.
2. QR code is generated.
3. Student scans QR code.
4. System checks student and session validity.
5. Attendance record is saved.
6. Teacher monitors attendance in real time.
7. Admin reviews reports and issues.

### Teacher Attendance Workflow

1. Admin syncs or creates teacher schedules.
2. Teacher checks in for scheduled teaching session.
3. System calculates status such as on time, late, or very late.
4. Teacher checks out after class.
5. System calculates teaching duration and early leave.
6. Admin reviews exceptions and correction requests.
7. Reports are generated.

### Reporting Workflow

1. Admin selects report type and filters.
2. System collects and processes data.
3. Report is generated as PDF or Excel.
4. Admin downloads the report or sends it through Telegram.

## 9. Technology Stack

### Backend

- Laravel 12
- PHP 8.3
- Laravel Sanctum
- Laravel Reverb
- Eloquent ORM

### Frontend

- Blade templates
- JavaScript
- Vite
- Tailwind CSS
- Small React components for selected interactive views

### Database and Services

- MySQL 8
- Redis
- Laravel queue worker
- Laravel scheduler

### Reporting and Files

- Maatwebsite Excel
- DomPDF
- Cloudinary support

### Deployment

- Docker
- Docker Compose
- Nginx
- phpMyAdmin

## 10. System Architecture

The system follows a Laravel-based architecture:

```text
User Interface
    -> Web Routes / API Routes
    -> Controllers
    -> Form Requests
    -> Services
    -> Models and Repositories
    -> MySQL Database
```

Additional integrations:

```text
Laravel Application
    -> Redis for cache/queue support
    -> Telegram Bot API for alerts
    -> Reverb for real-time chat/events
    -> DomPDF and Excel for reports
    -> Docker Compose for deployment
```

## 11. Database Overview

Important database entities include:

- Users
- Students
- Teachers
- Departments
- Majors
- Class groups
- Subjects
- Classes
- Attendance sessions
- Attendance records
- Teacher schedules
- Teacher attendance sessions
- Teacher attendance corrections
- Student permissions
- Student restore histories
- Settings
- Telegram bots
- Chat conversations and messages

These entities allow the system to connect academic structure, users, attendance records, scoring, reports, and notifications.

## 12. Security Considerations

Security is a key part of the system design.

Security controls include:

- Role-based access control
- Protected API routes
- Sanctum authentication
- CSRF protection for web requests
- Rate limiting for login and attendance actions
- Super admin restrictions for destructive actions
- Demo mode read-only protection
- Configuration-based registration control
- Secure environment variables for keys and credentials
- Maintenance mode support

Recommended production settings:

```env
APP_ENV=production
APP_DEBUG=false
PUBLIC_REGISTRATION_ENABLED=false
DEMO_LOGIN_ENABLED=false
```

## 13. Development Methodology

The project follows an iterative development approach.

### Phase 1: Requirement Analysis

- Identify user roles
- Define attendance workflows
- Define academic management requirements
- Study reporting needs

### Phase 2: System Design

- Design database structure
- Design user interface flow
- Define API routes
- Define security and role permissions

### Phase 3: Implementation

- Build authentication
- Build admin modules
- Build teacher and student attendance
- Build reports
- Build Telegram integration
- Build deployment environment

### Phase 4: Testing

- Test login and roles
- Test attendance workflows
- Test report exports
- Test API routes
- Test Docker deployment
- Test security restrictions

### Phase 5: Deployment and Presentation

- Prepare production environment
- Run migrations and seeders
- Build frontend assets
- Configure environment variables
- Demonstrate system modules

## 14. Project Timeline

| Phase | Activities | Estimated Duration |
| --- | --- | --- |
| Phase 1 | Requirement analysis and planning | 1 week |
| Phase 2 | Database and architecture design | 1 week |
| Phase 3 | Authentication and role setup | 1 week |
| Phase 4 | Admin academic management modules | 2 weeks |
| Phase 5 | Student attendance and QR workflow | 2 weeks |
| Phase 6 | Teacher attendance workflow | 2 weeks |
| Phase 7 | Reports, exports, and Telegram integration | 1 week |
| Phase 8 | Testing, bug fixing, and deployment | 1 week |
| Phase 9 | Documentation and presentation preparation | 1 week |

Total estimated duration: **12 weeks**

## 15. Required Resources

### Hardware

- Development computer
- Server or VPS for production deployment
- Mobile phone for QR scanning tests
- Stable network connection

### Software

- PHP
- Composer
- Node.js and npm
- Docker Desktop or Docker Engine
- MySQL
- Git
- Web browser

### Human Resources

- Project developer
- Academic supervisor
- Test users such as admin, teacher, and student
- Reviewer or evaluator

## 16. Estimated Project Cost

The estimated cost below is prepared for proposal and presentation purposes. The final price may change depending on hosting choice, number of users, additional customization, maintenance period, and whether mobile applications or biometric hardware are added later.

### 16.1 Development Cost Estimate

| Item | Description | Estimated Cost |
| --- | --- | ---: |
| Requirement analysis and system design | User role analysis, workflow planning, database design, and technical planning | USD 300 |
| Authentication and role management | Login, registration, super admin key, account approval, role permissions, and protected routes | USD 500 |
| Admin management modules | Student, teacher, subject, department, major, class group, class, semester, and settings management | USD 1,200 |
| Student QR attendance module | QR attendance sessions, scan validation, attendance records, and monitoring | USD 900 |
| Teacher attendance module | Schedule-based check-in/check-out, late calculation, correction requests, and admin review | USD 1,000 |
| Reports and exports | PDF and Excel exports for attendance, students, subjects, departments, results, and summaries | USD 700 |
| Telegram notification integration | Bot configuration, test message, report sending, and alert support | USD 300 |
| Real-time chat backend | Conversation, messages, attachments, read receipts, typing, and Reverb broadcasting | USD 600 |
| Dashboard and analytics | Summary cards, attendance issue monitoring, and academic overview | USD 500 |
| Docker deployment setup | Docker Compose, Nginx, MySQL, Redis, queue worker, scheduler, and phpMyAdmin setup | USD 400 |
| Testing and bug fixing | Functional testing, role testing, attendance testing, and deployment verification | USD 500 |
| Documentation and presentation support | Proposal, project guide, setup guide, and presentation preparation | USD 300 |

**Estimated total development cost: USD 7,200**

### 16.2 Optional Hosting and Operation Cost

| Item | Description | Estimated Cost |
| --- | --- | ---: |
| VPS or cloud server | Production server for running the system | USD 10-40 per month |
| Domain name | Custom website domain | USD 10-20 per year |
| SSL certificate | HTTPS security certificate | Free with Let's Encrypt, or paid option if required |
| Backup storage | External backup storage for database and files | USD 5-20 per month |
| SMS or notification service | Optional future notification channel | Based on provider usage |

### 16.3 Maintenance Cost

| Package | Description | Estimated Cost |
| --- | --- | ---: |
| Basic maintenance | Bug fixes, minor support, and monthly system check | USD 100 per month |
| Standard maintenance | Basic support plus small improvements and backup monitoring | USD 200 per month |
| Advanced maintenance | Priority support, feature updates, security review, and reporting support | USD 350 per month |

### 16.4 Suggested Payment Schedule

| Milestone | Payment Percentage |
| --- | ---: |
| Project approval and requirement confirmation | 30% |
| Core modules completed | 30% |
| Reports, integrations, and testing completed | 25% |
| Final deployment, documentation, and presentation delivery | 15% |

## 17. Expected Outcomes

After completion, the system is expected to:

- Reduce manual attendance work.
- Improve attendance data accuracy.
- Help teachers manage attendance faster.
- Help administrators identify attendance problems earlier.
- Provide reliable academic reports.
- Improve transparency in attendance and scoring.
- Support secure access by user role.
- Provide a deployable system suitable for academic use.

## 18. Benefits of the Project

### For Students

- Faster attendance check-in
- Clear attendance record tracking
- Better fairness and transparency

### For Teachers

- Reduced manual attendance checking
- Easy QR session management
- Attendance export support
- Teacher attendance and correction workflow

### For Administrators

- Centralized academic data
- Fast report generation
- Better attendance monitoring
- Clear role-based control
- Alerts and Telegram reporting

### For the Institution

- More reliable academic records
- Reduced paperwork
- Better decision-making
- Stronger accountability
- Scalable digital foundation

## 19. Risk Analysis

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Internet connection problems | QR attendance may be delayed | Allow manual admin correction and teacher requests |
| Wrong user role assignment | Unauthorized access risk | Use role middleware and account approval |
| Server downtime | System unavailable | Use Docker deployment and backup strategy |
| Incorrect attendance data | Academic reporting error | Add validation, audit logs, and admin review |
| Forgotten checkout for teacher attendance | Incomplete teacher attendance records | Mark missing check-out and allow correction workflow |
| Public registration abuse | Unauthorized accounts | Disable public registration in production |
| Demo account misuse | Data changes by reviewers | Use read-only demo mode |

## 20. Future Enhancements

Possible future improvements include:

- Mobile application for students and teachers
- Push notifications
- Face recognition attendance
- Biometric device integration
- Advanced AI analytics
- Multi-campus support
- Department head approval workflow
- Automatic class schedule import from Excel
- More detailed audit dashboard
- Advanced data visualization

## 21. Presentation Outline

Use this outline for slides:

1. Project title and team information
2. Background of the problem
3. Problem statement
4. Project objectives
5. Scope of the project
6. Target users
7. Main system features
8. System workflow
9. Technology stack
10. System architecture
11. Security and role permissions
12. Estimated project cost
13. Demonstration screenshots or live demo
14. Expected outcomes
15. Benefits
16. Future enhancements
17. Conclusion

## 22. Conclusion

The HRU Academic Attendance Management System provides a complete digital solution for academic attendance and administration. It improves the traditional attendance process by using QR verification, role-based access, centralized data management, report generation, teacher attendance tracking, Telegram alerts, and secure deployment.

This project is practical, scalable, and suitable for real academic use. It helps students, teachers, administrators, and the institution work with more accurate data and faster workflows. With future enhancements such as mobile apps and advanced analytics, the system can continue to grow into a broader academic management platform.
