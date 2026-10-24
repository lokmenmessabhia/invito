# Invito

Invito is a self-hosted PHP + MySQL application for creating and sharing digital invitations for weddings, baccalaureate celebrations, and circumcisions. Couples get their own dashboard to customise an invitation, while administrators manage users, templates, songs, and RSVPs.

The interface is in French.

## Features

- Animated, video-backed invitation templates (MP4/WebM) with optional background music
- Couple, baccalaureate, and circumcision invitation types
- User dashboard for editing invitation content, photos, colours, and music
- Public invitation page with countdown, map, photo gallery, and RSVP form
- Admin dashboard for users, templates, songs, uploads, and statistics
- Token-based authentication (Bearer tokens) with expiring sessions
- Password hashing with bcrypt and login rate limiting
- Automatic database backups with retention

## Tech stack

- PHP 8.1+ (no framework, no Composer dependencies)
- MySQL 8 / MariaDB 10.4+
- Apache with `mod_rewrite` and `mod_headers`
- Vanilla HTML, CSS, and JavaScript

Required PHP extensions: `pdo_mysql`, `fileinfo`, `mbstring`, `zlib`.

## Installation

### 1. Clone the project

```bash
git clone https://github.com/<your-username>/invito.git
```

For XAMPP, place it in `C:\xampp\htdocs\invito`.

### 2. Create the database

Import the schema with phpMyAdmin, or from the command line:

```bash
mysql -u root -p < sql/schema.sql
```

The script creates the `invito` database and seeds a default admin account plus a demo invitation.

### 3. Configure the database

Defaults match a standard XAMPP install (`127.0.0.1:3306`, user `root`, empty password). To use different credentials, set environment variables instead of editing the source:

| Variable     | Default     |
| ------------ | ----------- |
| `DB_HOST`    | `127.0.0.1` |
| `DB_PORT`    | `3306`      |
| `DB_NAME`    | `invito`    |
| `DB_USER`    | `root`      |
| `DB_PASS`    | *(empty)*   |
| `DB_CHARSET` | `utf8mb4`   |

The app also auto-detects its base URL, so it works from a subfolder such as `http://localhost/invito/`.

### 4. Set permissions

Apache must be able to write to:

- `uploads/` (profile photos)
- `backup/` (database dumps)
- `songs/` and `templates/` (uploads from the admin dashboard)

### 5. Open the app

- Landing page: `http://localhost/invito/`
- Admin login: `http://localhost/invito/admin-login.php`
- Demo invitation: `http://localhost/invito/invitation.php?slug=demo-wedding`

Default admin credentials:

```
Username: admin
Password: Admin@123456
```

**Change this password immediately after the first login.** User accounts are created from the admin dashboard.

## Project structure

```
api/                 JSON endpoints (auth, admin, user)
assets/              Shared CSS, JS, and images
backup/              Database backups (ignored by git)
config/              Database and base URL configuration
dashboard/admin/     Admin pages
dashboard/user/      Couple/user pages
includes/            Shared helpers and token validation
songs/               Background music library
sql/                 Database schema and migration scripts
templates/           Invitation templates (videos, music, thumbnails)
uploads/             Uploaded user photos (ignored by git)
admin-login.php      Admin login page
index.php            Landing page and user login
invitation.php       Public invitation page
_backup.php          Backup CLI script
```

## Database backups

`_backup.php` creates compressed MySQL dumps in `backup/` (30-day retention). It requires `mysqldump` and a PHP CLI binary.

```bash
php _backup.php
```

On Windows you can run `schedule_backup.bat` or register it with Task Scheduler. On Linux, use cron:

```cron
0 * * * * /usr/bin/php /path/to/invito/_backup.php
```

Backups and `uploads/` are intentionally excluded from version control.

## Roadmap / notes

- Update the allowed CORS origins in `includes/functions.php` (`setCorsHeaders`) for your own domain. The current list includes a production domain and local development hosts.
- Templates and songs are committed to the repository, so the clone is roughly 80 MB. Consider Git LFS if you plan to add more large media.
- The `_migrate_*.php` files are one-off schema migrations kept for reference; a fresh install only needs `sql/schema.sql`.

## Security

- Change the seeded admin password right away.
- Use HTTPS in production and set database credentials through environment variables.
- Requests to `config/`, `includes/`, `sql/`, `backup/`, and internal `_*.php` scripts are denied by `.htaccess`; keep the same protections if you deploy to another web server.
- Never commit real database dumps, `.env` files, or user uploads (already covered by `.gitignore`).
