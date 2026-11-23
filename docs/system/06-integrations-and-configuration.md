# Integrations and Configuration

Configuration comes from `.env` (never committed) read through `config/*.php`. This page lists variable **names** and meanings only. Copy `.env.example` to `.env` and fill in real values on the server.

## Environment variables in `.env.example`

Defaults shown are the values written in `.env.example` (placeholders) and, where different, the fallback in `config/*.php`.

### Application

| Variable | Meaning | `.env.example` / config default |
|---|---|---|
| `APP_NAME` | Site name; used in emails, the session cookie name, cache prefix and the documentation download file name | `Koralink` |
| `APP_ENV` | Environment name (`local`, `production`, `testing`) | `local` (config: `production`) |
| `APP_KEY` | Encryption key (cookies, sessions, encrypted `national_id`). Generate with `php artisan key:generate`. Must be kept secret and stable: changing it makes encrypted data unreadable | empty |
| `APP_DEBUG` | Show detailed errors. Must be `false` in production | `true` (config: `false`) |
| `APP_URL` | Public URL. Used for generated links, the `public` disk URL, mail EHLO domain and to force HTTPS when it starts with `https://` | `http://localhost` |
| `APP_INITIAL_LOCALE` | Language a first-time visitor sees (`en` or `rw`); an invalid value falls back to `en` | `en` |
| `APP_LOCALE` | Framework locale before `SetLocale` runs | `en` |
| `APP_FALLBACK_LOCALE` | Laravel fallback locale | `en` |
| `APP_FAKER_LOCALE` | Locale for factories | `en_US` |
| `APP_MAINTENANCE_DRIVER` | Maintenance mode store (`file` or `cache`) | `file` |
| `APP_MAINTENANCE_STORE` | Cache store when the driver is `cache` (commented out) | `database` in config |
| `PHP_CLI_SERVER_WORKERS` | Workers for `php artisan serve` (commented out) | unset |
| `BCRYPT_ROUNDS` | Password hash cost | `12` |

### Logging

| Variable | Meaning | Default |
|---|---|---|
| `LOG_CHANNEL` | Default log channel | `stack` |
| `LOG_STACK` | Channels in the stack | `single` (one file `storage/logs/laravel.log`) |
| `LOG_DEPRECATIONS_CHANNEL` | Channel for deprecation notices | `null` |
| `LOG_LEVEL` | Minimum level | `debug` |

### Database

| Variable | Meaning | Default |
|---|---|---|
| `DB_CONNECTION` | Driver: `mysql`, `mariadb`, `pgsql`, `sqlsrv`, `sqlite` | `mysql` in `.env.example`; config default `sqlite` |
| `DB_HOST`, `DB_PORT` | Server address (commented out) | `127.0.0.1`, `3306` |
| `DB_DATABASE` | Database name (SQLite: file path, default `database/database.sqlite`) | `laravel` |
| `DB_USERNAME`, `DB_PASSWORD` | Credentials (secret) | `root`, empty |

The config files read further optional names (`DB_URL`, `DB_SOCKET`, `DB_CHARSET`, `DB_COLLATION`, `DB_FOREIGN_KEYS`, `MYSQL_ATTR_SSL_CA`, `DB_SSLMODE`).

### Session

| Variable | Meaning | Default |
|---|---|---|
| `SESSION_DRIVER` | `database` stores sessions in the `sessions` table | `database` |
| `SESSION_LIFETIME` | Idle lifetime in minutes | `120` |
| `SESSION_ENCRYPT` | Encrypt session payload | `false` |
| `SESSION_PATH` | Cookie path | `/` |
| `SESSION_DOMAIN` | Cookie domain. Leave `null` locally; a wrong domain causes HTTP 419 errors. Set only to the real production domain | `null` |
| `SESSION_SECURE_COOKIE` | HTTPS-only cookie. Automatically true when `APP_URL` starts with `https://` (commented out) | derived |

Other optional names in `config/session.php`: `SESSION_EXPIRE_ON_CLOSE`, `SESSION_CONNECTION`, `SESSION_TABLE`, `SESSION_STORE`, `SESSION_COOKIE`, `SESSION_HTTP_ONLY` (true), `SESSION_SAME_SITE` (`lax`), `SESSION_PARTITIONED_COOKIE`.

### Cache, queue, broadcasting, filesystem

| Variable | Meaning | Default |
|---|---|---|
| `CACHE_STORE` | Cache backend | `database` (`cache` table) |
| `CACHE_PREFIX` | Key prefix (commented out) | slug of `APP_NAME` + `-cache-` |
| `QUEUE_CONNECTION` | Queue backend | `database` (`jobs` table) |
| `BROADCAST_CONNECTION` | Broadcasting driver (not used by the app) | `log` |
| `FILESYSTEM_DISK` | Default disk for `Storage::` calls without a disk | `local` |
| `MEMCACHED_HOST` | Memcached host (only if that store is selected) | `127.0.0.1` |
| `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` | Redis connection (only if Redis is used) | `phpredis`, `127.0.0.1`, `null`, `6379` |

### Mail

| Variable | Meaning | Default |
|---|---|---|
| `MAIL_MAILER` | `log` writes emails to `storage/logs/laravel.log` and delivers nothing; `smtp` sends for real | `log` |
| `MAIL_SCHEME` | `smtp` (STARTTLS, port 587) or `smtps` (SSL, port 465). The value `tls` is invalid | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp.gmail.com` in `.env.example` (config: `127.0.0.1`) |
| `MAIL_PORT` | SMTP port | `587` (config: `2525`) |
| `MAIL_USERNAME`, `MAIL_PASSWORD` | SMTP credentials (secret) | `null` |
| `MAIL_FROM_ADDRESS` | Sender address; also the recipient of contact-form notifications | `hello@example.com` |
| `MAIL_FROM_NAME` | Sender name | `${APP_NAME}` |

Other optional names in `config/mail.php`: `MAIL_URL`, `MAIL_EHLO_DOMAIN`, `MAIL_SENDMAIL_PATH`, `MAIL_LOG_CHANNEL`. `config/services.php` reads `POSTMARK_API_KEY`, `RESEND_API_KEY`, `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL`; none are used by application code.

### AWS (unused by default)

`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` (`us-east-1`), `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT` (`false`). They only matter if the `s3` disk, SES mail or SQS were configured. Note that `StorageController` and the upload code are hard-wired to the `public` disk.

### Front-end

| Variable | Meaning |
|---|---|
| `VITE_APP_NAME` | Exposed to Vite (`${APP_NAME}`); not referenced by the current JS |

### Admin seeder

| Variable | Meaning | Fallback in `config/admin.php` |
|---|---|---|
| `ADMIN_NAME` | Display name of the seeded Super Admin | `Super Admin` |
| `ADMIN_EMAIL` | Login email of the Super Admin | `admin@example.com` |
| `ADMIN_PASSWORD` | Initial password. The placeholder in `.env.example` and the built-in fallback are weak: set a strong value before seeding in any shared environment | weak built-in fallback |

They are used by `DatabaseSeeder` and by `php artisan admin:ensure` (which also accepts `--email`, `--password`, `--name`).

## Configuration files

| File | Notes |
|---|---|
| `config/app.php` | Name, env, debug, URL, locale, cipher, maintenance driver. |
| `config/admin.php` | Super Admin name, email and password for seeding. |
| `config/admin-modules.php` | The permission and navigation registry: `actions`, `groups` (sidebar sections: root, Catalog, Learners, Assessments, Analytics, Site, Help), `modules` (label, icon, actions, nav entries), `route_modules` (route-name prefix to module) and `route_actions` (route-name to action overrides). Its labels are translated by `TranslationCoverageTest`. |
| `config/localization.php` | `default` (`en`), `initial` (`APP_INITIAL_LOCALE`), `locales` (`en`, `rw` with names and short codes), `session_key` (`locale_choice`), `cookie` (`koralink_lang`), `cookie_minutes` (one year). |
| `config/auth.php` | `web` guard, `users` provider (`App\Models\User`), reset token table `password_reset_tokens`, reset expiry 60 minutes, throttle 60 seconds, `password_timeout` 10800. |
| `config/session.php`, `cache.php`, `queue.php`, `database.php`, `logging.php`, `mail.php`, `services.php` | Framework defaults driven by the variables above. |
| `config/filesystems.php` | See below. |

## Filesystem disks

| Disk | Root | Used for |
|---|---|---|
| `local` (default) | `storage/app/private` | CSV imports (`imports/progress-csv`, deleted after processing) |
| `public` | `storage/app/public`, URL `APP_URL/storage` | Course banners (`courses/`), bundle thumbnails (`bundles/`), site logo (`site/`), partner logos (`partners/`), lesson PDFs and videos (`lessons/pdf`, `lessons/video`) |
| `s3` | bucket from `AWS_*` | Defined, not used by code |

The app serves public-disk files through `StorageController` (`course-image/...`, `lesson-file/...`), so `php artisan storage:link` is optional. The `links` entry in `config/filesystems.php` maps `public/storage` to `storage/app/public` if you do run it.

## Mail

- Sent by the application: password reset (`Notifications\ResetPassword`), contact notification (`Mail\ContactMessageReceived` to `MAIL_FROM_ADDRESS`), live-session invitations (`Mail\LiveSessionInvitation`).
- All are sent **synchronously** during the web request; none implements `ShouldQueue`, so no queue worker is needed for mail. A slow or failing SMTP server therefore slows the request (the controllers catch and log failures).
- `php artisan mail:test <address>` prints the effective mailer, host, port, scheme and from address, warns about problems (`MAIL_MAILER=log`, empty host/username/password, invalid scheme, `@example.com` sender) and then sends a test message.
- `MailConfigTest` checks the command's warnings and that `.env.example` documents valid Gmail-style settings. Gmail needs 2-Step Verification and an app password, and always sends as the logged-in account.

## Queue

`QUEUE_CONNECTION=database` and the `jobs` table exist, and `composer run dev` starts `php artisan queue:listen`, but the application code dispatches no queued jobs and no mailable or notification is queued. A production queue worker is therefore **not required** today. If you add queued work, run a supervised `php artisan queue:work`.

## Cache and Settings

`CACHE_STORE=database` stores cache entries in the `cache` table. The only application use is `Setting::get()` (one hour, key `setting.<key>`), invalidated by `Setting::set()`. After changing configuration run `php artisan config:clear` (and `cache:clear` if you edit settings by hand in the database).

## Scheduler and cron

`routes/console.php` schedules one task:

| Command | Frequency | Purpose |
|---|---|---|
| `quizzes:close-expired` | every minute | Submit quiz attempts whose time limit has passed, using the answers autosaved so far, so a learner who closes the browser still gets a result and the attempt counts against `max_attempts`. |

Laravel's scheduler only runs when something calls it. The server needs this single crontab entry (adjust the path and PHP binary):

```text
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Without cron the system still works: expired attempts are closed lazily when the learner next opens or submits the quiz or attempt, but abandoned attempts remain open and uncounted until then.

## Localisation configuration

See `config/localization.php` above. To change the default first-visit language set `APP_INITIAL_LOCALE=rw`; `localization.default` (`en`) must stay `en` because it is the language of the stored content and the fallback. Translation files: `lang/rw.json` (interface text), `lang/rw/*.php` (framework messages), `lang/GLOSSARY.md` (style guide).

## External services contacted by the browser

| Service | Where | Purpose |
|---|---|---|
| Bunny Fonts (`fonts.bunny.net`) | `components/brand-meta.blade.php`, `welcome.blade.php` | Web fonts |
| jsDelivr (`cdn.jsdelivr.net`) | `admin/dashboard.blade.php` | Chart.js 4.4.1 |
| YouTube (`youtube.com/embed`) | `Lesson::youtubeEmbedUrl()`, `resources/js/lesson-progress.js` | Video lessons |

Meeting links for live sessions are plain URLs entered by admins (Zoom, Meet and so on); there is no API integration with a meeting provider.
