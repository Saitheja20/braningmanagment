# Production Folder Structure

Recommended deployment layout:

```text
/var/www/branding-platform/
├── current/
│   ├── public/              # only web-accessible document root
│   ├── src/
│   ├── database/
│   ├── docs/
│   ├── storage/
│   └── .env
├── releases/
├── shared/
│   ├── storage/uploads/
│   ├── storage/logs/
│   ├── storage/backups/
│   └── .env
└── backups/
```

Apache/Nginx document root must point to:

```text
/var/www/branding-platform/current/public
```

Never expose:

- `.env`
- `src/`
- `database/`
- `storage/`
- `docs/`
- backup archives
