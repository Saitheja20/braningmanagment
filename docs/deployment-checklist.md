# Production Deployment Checklist

## Server

- Use PHP 8.2+ with OPcache enabled.
- Use MySQL 8+ with InnoDB and `utf8mb4_unicode_ci`.
- Point the web root to `public/`, never the repository root.
- Keep `storage/`, `database/`, `src/`, and `.env` outside public access.
- Enable HTTPS and HSTS.
- Set `APP_ENV=production` and `APP_DEBUG=false`.

## Security

- Use strong database credentials with least privilege.
- Rotate `.env` secrets before launch.
- Enforce MFA for admin users.
- Limit upload size with PHP, Apache/Nginx, and application validation.
- Schedule cleanup for expired sessions, reset tokens, and remember tokens.
- Review `storage/logs/app.log` and configure log rotation.

## Performance

- Enable OPcache.
- Add Redis or Memcached for sessions/cache when traffic grows.
- Add indexes after observing slow queries with `EXPLAIN`.
- Serve `/assets` through CDN or long-cache headers.
- Keep uploaded files in private storage or object storage.

## Database

- Run migrations in order:
  1. `001_auth_schema.sql`
  2. `002_project_management_schema.sql`
  3. `003_task_management_schema.sql`
  4. `004_employee_realtime_client_production.sql`
- Run backups before each deployment.
- Test restore from backup, not only backup creation.

## Release

- Deploy code.
- Install/update dependencies if Composer is introduced.
- Apply migrations.
- Warm caches.
- Smoke test login, dashboard, projects, tasks, employees, realtime, and portal.
- Monitor errors for the first hour after release.
