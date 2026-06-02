# Backup Strategy

## Database Backups

Run a daily logical backup:

```bash
mysqldump --single-transaction --routines --triggers -u backup_user -p branding_pm > storage/backups/branding_pm_$(date +%F).sql
```

Keep:

- 7 daily backups
- 4 weekly backups
- 6 monthly backups

Encrypt backups before storing off-server.

## File Backups

Back up:

- `storage/uploads`
- `.env`
- deployment release metadata

Do not back up:

- `storage/sessions`
- temporary cache files

## Restore Drill

At least monthly:

1. Restore the latest database backup to a staging database.
2. Restore uploads.
3. Boot the app with staging `.env`.
4. Verify login, file download, client portal, and reporting screens.
