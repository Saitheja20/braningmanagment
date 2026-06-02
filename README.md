# Branding PM Authentication System

Phase 3 implements a secure PHP 8 + MySQL authentication module for the Branding Agency Project Management Platform.

## Features

- Login
- Registration
- Forgot password and reset password tokens
- Session authentication
- Remember me tokens with selector/validator rotation
- Role-based access control through permissions
- Secure logout
- Bootstrap 5 UI
- PDO prepared statements
- CSRF protection
- Server-side validation
- Basic error handling
- Premium admin dashboard UI
- Responsive sidebar and top navbar
- Dashboard cards, analytics widgets, charts, notifications, activity timeline, and project statistics

## Structure

```text
public/
  index.php
  .htaccess
  assets/css/app.css
src/
  Controllers/
  Core/
  Helpers/
  Middleware/
  Models/
  Services/
  Views/
database/
  migrations/001_auth_schema.sql
```

## Setup

1. Create the database.

```sql
CREATE DATABASE branding_pm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the migration.

```bash
mysql -u root -p branding_pm < database/migrations/001_auth_schema.sql
```

3. Copy the environment file.

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

4. Update `.env` with your MySQL credentials.

5. Start the local PHP server from the project root.

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

6. Open:

```text
http://127.0.0.1:8000/register
```

## Security Notes

- Passwords are hashed with `password_hash()`.
- SQL injection is prevented with PDO prepared statements.
- CSRF tokens are required for every POST auth action.
- Session cookies are `HttpOnly` and `SameSite=Lax`.
- Remember-me cookies are `HttpOnly`, stored as opaque tokens, and only token hashes are saved in MySQL.
- Password reset tokens are stored hashed and expire after 60 minutes.
- Server-side sessions are tracked in `user_sessions` and expire after 8 hours of inactivity.

## Production Notes

- Enable HTTPS so cookies are sent with the `Secure` flag.
- Replace the development reset-link display with email delivery through PHPMailer or a transactional mail provider.
- Add login rate limiting per IP and email before production launch.
- Add MFA for admin roles.
- Store logs outside the public web root.
- Run periodic cleanup for expired `remember_tokens`, `password_resets`, and `user_sessions`.

## Phase 4 Dashboard UI

The protected `/dashboard` route now uses:

- Bootstrap 5
- Bootstrap Icons
- Chart.js
- AOS animations
- Responsive desktop sidebar and mobile offcanvas navigation
- Notifications dropdown
- Glass-style metric cards and analytics panels
- Project velocity and project status charts
- Priority project statistics
- Activity timeline

Dashboard data is currently static UI-ready sample data. Replace the values in `src/Views/dashboard/index.php` with controller-provided metrics once the Projects, Tasks, Clients, Files, and Reports modules are connected.

## Phase 5 Project Management Module

The protected Project Management module is available after login:

- `/projects`
- `/projects/kanban`

Implemented:

- Create, edit, soft-delete projects
- Project status tracking with AJAX updates
- Assign employees to projects through `project_members`
- Kanban board with drag-and-drop task status updates
- Task creation and status updates
- Milestone creation and upcoming milestone list
- Deadline calendar snapshot
- Responsive Bootstrap 5 UI
- CSRF-protected forms and AJAX calls
- PDO prepared statements for all database writes

Import the Phase 5 migration after the auth migration:

```bash
mysql -u root -p branding_pm < database/migrations/002_project_management_schema.sql
```

Main files:

- `src/Controllers/ProjectController.php`
- `src/Models/Project.php`
- `src/Views/projects/index.php`
- `src/Views/projects/kanban.php`
- `public/assets/js/project-management.js`
- `database/migrations/002_project_management_schema.sql`

Core MySQL operations are implemented in `src/Models/Project.php`, including project CRUD queries, member sync queries, task status updates, milestone inserts, deadline lists, and dashboard statistics.

## Phase 6 Task Management

The protected Task Management module is available after login:

- `/tasks`
- `/tasks/detail?id={task_id}`
- `/projects/kanban`

Implemented:

- Task assignment
- Priority and status updates through AJAX
- Due dates and estimated hours
- Labels through `task_labels` and `task_label_map`
- File attachments stored under `storage/uploads/tasks/{task_id}`
- Comments through AJAX
- Checklist creation and completion toggles through AJAX
- Checklist-based progress tracking
- Assignment/comment/attachment notification inserts
- Responsive Bootstrap workbench and detail UI

Import the Phase 6 migration after Phase 5:

```bash
mysql -u root -p branding_pm < database/migrations/003_task_management_schema.sql
```

Main files:

- `src/Controllers/TaskController.php`
- `src/Models/Task.php`
- `src/Views/tasks/index.php`
- `src/Views/tasks/detail.php`
- `src/Views/tasks/task-modal.php`
- `public/assets/js/project-management.js`
- `database/migrations/003_task_management_schema.sql`

Core database operations are implemented in `src/Models/Task.php`, including task queries, label sync, comment inserts, checklist progress recalculation, attachment metadata inserts, and notification creation.

## Phase 8 Employee Management

Routes:

- `/employees`
- `/employees/detail?id={employee_id}`

Implemented:

- Employee profiles
- Attendance tracking
- Assigned task overview
- Work logs
- Productivity analytics widgets
- Employee directory UI

## Phase 9 Real-Time Features

Routes:

- `/realtime`
- `/api/realtime/snapshot`
- `/api/realtime/message`
- `/api/notifications/read`

Implemented:

- AJAX notification polling
- Internal chat
- Live task update stream
- Activity feed
- Notification read endpoint
- Real-time UI refresh every 10 seconds

## Phase 10 Client Portal

Routes:

- `/portal`
- `/portal/approval`
- `/portal/feedback`
- `/portal/file`

Implemented:

- Secure role-protected portal access
- Client-scoped project progress
- Design approval/rejection workflow
- Client feedback messaging
- Client file download access checks
- Invoice visibility

## Phase 11 Production Optimization

Added:

- Hardened `public/.htaccess`
- Root `.htaccess` denial if the wrong web root is used
- `storage/.htaccess` denial
- Security headers middleware
- Central error logging to `storage/logs/app.log`
- Upload extension, size, and MIME validation
- File cache utility in `src/Core/Cache.php`
- Backup strategy document
- Deployment checklist
- Production folder structure guide

Import the Phase 8-11 migration after Phase 6:

```bash
mysql -u root -p branding_pm < database/migrations/004_employee_realtime_client_production.sql
```

Production docs:

- `docs/deployment-checklist.md`
- `docs/backup-strategy.md`
- `docs/production-folder-structure.md`
