# Deployment and Operations

## Requirements

- PHP 8.2 or newer (the project is developed on 8.4) with the extensions Laravel needs (`ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo` plus `pdo_mysql` or `pdo_sqlite`, `tokenizer`, `xml`) and `fileinfo` for upload MIME detection.
- Composer 2.
- MySQL (or MariaDB; the default connection in `.env.example` is `mysql`). SQLite also works for small installs.
- Node.js and npm to build assets. `package-lock.json` is committed; the Node version is not pinned in the repository, so use a current LTS that Vite 7 supports.
- A web server (nginx or Apache) whose document root is `public/`, with PHP-FPM, and cron.
- Outbound SMTP access for real email.

`public/build` (compiled assets), `vendor/`, `node_modules/` and `.env` are git-ignored, so each deployment must install dependencies and build assets.

## Install from scratch

```bash
git clone <repository-url> koralink && cd koralink          # 1. get the code
composer install --no-dev --optimize-autoloader             # 2. PHP dependencies (no dev packages)
cp .env.example .env                                        # 3. environment file, then edit it
php artisan key:generate                                    #    generate APP_KEY
npm ci                                                      # 4. JavaScript dependencies from the lock file
npm run build                                               #    production assets into public/build
php artisan migrate --force                                 # 5. database schema
php artisan db:seed --force                                 # 6. initial data (see Seeding; or use admin:ensure)
```

**Assets: always use `npm ci` followed by `npm run build`.** `npm ci` installs exactly the versions in `package-lock.json` and fails if `package.json` and the lock file disagree; `npm install` may rewrite the lock file and change versions. Do not use `npm install` on servers or in CI. Likewise install PHP packages from `composer.lock` (`composer install`, not `composer update`). The `composer run setup` script in `composer.json` runs `npm install`; on a server run the explicit commands above instead.

For development, `composer run dev` starts `php artisan serve`, `queue:listen`, `pail` and `npm run dev` together (requires dev dependencies and `npx concurrently`).

## Environment setup checklist

Edit `.env` (names and meanings are in `06-integrations-and-configuration.md`):

1. `APP_ENV=production`, `APP_DEBUG=false`.
2. `APP_URL` to the public URL, with `https://` when TLS is used (this also forces HTTPS URLs and secure cookies).
3. `APP_KEY` generated once and backed up.
4. `DB_*` for the production database.
5. `SESSION_DOMAIN` only to the real domain (or `null`); a wrong value on a host produces HTTP 419 errors.
6. Mail: `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME` (`smtp` or `smtps`), `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`. Verify with `php artisan config:clear && php artisan mail:test you@example.com`.
7. `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` set to real, strong values before seeding.
8. `APP_INITIAL_LOCALE` (`en` or `rw`) for first-time visitors.

## Migrations

```bash
php artisan migrate --force
php artisan migrate:status
```

Migrations create all tables, insert the `super-admin` role and the SFH partner organisation, and add default Kinyarwanda strings for the seeded categories and the seeded sample course description. `admin:ensure` re-runs the roles migration if the `roles` table or `users.is_active` column is missing (it deletes that migration's ledger row and calls `migrate` for that file), which is a recovery path for servers where migrations were skipped.

## Seeding

`php artisan db:seed` runs `DatabaseSeeder`, which:

1. creates a demo learner account (`test@example.com`) with a fixed, weak password if it does not exist;
2. ensures the `super-admin` role with all permissions;
3. creates or updates the admin user from `ADMIN_EMAIL`, `ADMIN_NAME` and `ADMIN_PASSWORD` (note: `updateOrCreate` **resets the password** to the configured value each time the seeder runs);
4. runs `CategoryCourseSeeder`, which creates four categories and sample courses with random prices, durations and `students_count` values.

On a production system do not run the full seeder as-is: it creates the demo learner and sample courses. Instead run:

```bash
php artisan admin:ensure --email=admin@your-domain.example --name="Platform Admin"
```

`admin:ensure` creates or updates the Super Admin (password from `--password` or `ADMIN_PASSWORD`), marks the email verified and **prints the password in the console output**; clear your terminal history afterwards. If you did seed the demo learner, delete it from Admin > Users.

## File permissions and storage

The web-server user must be able to write to `storage/` and `bootstrap/cache/`:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

Uploads land in `storage/app/public` and are served by the application itself (`course-image/...`, `lesson-file/...`), so `php artisan storage:link` is optional. Lesson videos can reach 100 MB and PDFs 50 MB: raise `upload_max_filesize` and `post_max_size` in PHP (and `client_max_body_size` in nginx) above those limits, and set `max_execution_time` / `memory_limit` appropriately.

## Web server

Point the document root at `public/`. Behind a reverse proxy, the app trusts all proxies (`trustProxies(at: '*')`); make sure only your proxy can reach the PHP upstream, forward `X-Forwarded-Proto` and `X-Forwarded-Host`, and keep `APP_URL` on `https://`. A health endpoint is available at `GET /up`.

## Scheduler (cron) and queue worker

Required: the scheduler, every minute, as the web-server user or the app owner:

```text
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

It runs `quizzes:close-expired` each minute. Without it, abandoned timed quiz attempts stay open until the learner returns.

Not required today: a queue worker. No jobs are dispatched and email is sent synchronously. If you add queued jobs, run `php artisan queue:work` under a process manager (Supervisor or systemd) and restart it on each deployment with `php artisan queue:restart`.

## Caching commands

Run after each deployment, once `.env` is final:

```bash
php artisan config:cache
php artisan view:cache
php artisan event:cache
```

Do **not** run `php artisan route:cache` with the current code: `routes/web.php` contains a closure route (`/admin` redirect inside the admin group), which Laravel cannot serialise, so the command fails. `env()` is only used inside `config/*.php`, so `config:cache` is safe. To reset: `php artisan optimize:clear` (or `config:clear`, `cache:clear`, `view:clear`). Run `config:clear` before running tests (the `composer test` script does this).

## Backups

Suggested, not automated by the project:

- **Database**: nightly `mysqldump --single-transaction` (or your provider's snapshots), kept off-server, with a restore test.
- **Uploads**: `storage/app/public` (images, PDFs, videos). Pending CSV imports in `storage/app/private` are temporary and need no backup.
- **Secrets**: a copy of `.env`, especially `APP_KEY`, stored in a password manager or vault. Without the key, encrypted `users.national_id` values cannot be decrypted after a restore.
- **Documentation**: `docs/` is in git.

## Upgrading

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan view:cache
php artisan up
```

Read new migrations before running them on production, take a database backup first, and run `php artisan test` on a staging copy. For Laravel framework upgrades follow the official upgrade guide and update `composer.lock` deliberately in a branch, not on the server.

## Logs

Application logs go to `storage/logs/laravel.log` (`LOG_STACK=single`); there is no rotation by default (set `LOG_STACK=daily` to rotate, `LOG_DAILY_DAYS` controls retention). Password reset requests are logged with the mailer and host (never credentials). In development `php artisan pail` tails the log.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Page shows an error about a missing Vite manifest or unstyled pages | `public/build` missing or stale | `npm ci && npm run build` |
| HTTP 419 "Page expired" on forms | `SESSION_DOMAIN` set to a domain that does not match the host, wrong `APP_URL`, cookies blocked, or session table unreachable | Set `SESSION_DOMAIN=null` locally or the correct domain; verify `APP_URL`; check the `sessions` table |
| Endless redirect between http and https | TLS terminated at a proxy but `APP_URL` is `http://` or proxy headers not forwarded | Use `https://` in `APP_URL`; forward `X-Forwarded-Proto` |
| Emails never arrive | `MAIL_MAILER=log` (writes to the log only), wrong SMTP values, or provider rejection | `php artisan config:clear && php artisan mail:test <address>`; read `storage/logs/laravel.log`; check the provider's logs and spam folder |
| `MAIL_SCHEME` error | Value `tls` used | Use `smtp` (STARTTLS, 587) or `smtps` (SSL, 465) |
| Quiz attempts not closing after the time limit | Cron entry missing | Add the `schedule:run` cron line; test with `php artisan quizzes:close-expired` |
| Uploaded images or PDFs return 404 | File missing from `storage/app/public`, wrong path prefix, or files not restored | Check the file exists; the app only serves `courses/`, `bundles/`, `site/`, `partners/` (images) and `lessons/` (lesson files) |
| Upload fails with a size error | PHP/nginx limits below the app limits (2 MB images, 50 MB PDF, 100 MB video, 5 MB CSV) | Raise `upload_max_filesize`, `post_max_size`, `client_max_body_size` |
| `The stream or file ... could not be opened` / permission denied | Wrong ownership of `storage` or `bootstrap/cache` | Fix ownership and permissions (above) |
| `No application encryption key has been specified` | Missing `APP_KEY` | `php artisan key:generate` (new installs only) |
| Admin user cannot log in | Account inactive, wrong password, or not created | `php artisan admin:ensure ...` |
| Staff member sees 403 in the admin | Missing module permission | Grant it under Admin > Roles & permissions or on the user |
| Changed `.env` has no effect | Config cached | `php artisan config:clear` then `config:cache` |
| `php artisan route:cache` fails | Closure route in `routes/web.php` | Skip route caching (see above) |
| Login says the account was deactivated | `users.is_active = false` | Reactivate in Admin > Users |
| "Too many login attempts" | Five failures for the same email and IP | Wait for the time shown; the limiter is stored in the cache |
