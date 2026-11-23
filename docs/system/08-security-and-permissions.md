# Security and Permissions

Everything on this page is derived from the code in the repository. Items that are weaknesses or need operator action are repeated in `10-known-limitations.md`.

## Authentication

- Session-based login with the `web` guard and the `users` Eloquent provider (`config/auth.php`). Sessions are stored in the `sessions` table by default; the session id is regenerated on login (`AuthenticatedSessionController@store`) and invalidated with a new CSRF token on logout.
- Passwords are hashed through the model cast `hashed` (bcrypt, cost `BCRYPT_ROUNDS`, 12 by default). Registration and reset use `Rules\Password::defaults()` and require confirmation.
- Login (`LoginRequest`): validates email and password, then limits attempts to **5 failures per `email|IP` key** (`RateLimiter`), after which a throttle message with the wait time is shown. A deactivated account (`users.is_active = false`) is logged out immediately with the message "This account has been deactivated". `last_login_at` is updated on success. A "remember me" checkbox is supported.
- `login` redirects admins to `admin.dashboard` and others to `dashboard` unless an intended URL is stored; an intended URL containing `/logout` is ignored.
- `logout` accepts both GET and POST (`Route::match(['get','post'], 'logout', ...)`). A GET logout can be triggered by any link or image; see limitations.
- Password confirmation (`confirm-password`) and password change (`PUT /password`, requires the current password) come from Breeze. Account deletion (`DELETE /profile`) requires the current password.
- Password reset: tokens in `password_reset_tokens`, expiry 60 minutes, throttle `throttle:6,1` on request and on submit, generic response regardless of whether the email exists, failures logged server-side.

## Email verification

The Breeze verification routes exist (`verify-email`, `verify-email/{id}/{hash}` with `signed` and `throttle:6,1`, `email/verification-notification`), and `/dashboard` uses the `verified` middleware. However, `App\Models\User` does **not** implement `MustVerifyEmail` (the import is commented out in the class). Consequences:

- The `verified` middleware lets every authenticated user through, so verification is not enforced.
- Registration fires the `Registered` event but no verification email is generated.
- Admin-created users and CSV-imported users get `email_verified_at = now()` anyway.
- The verification controllers call methods defined by `MustVerifyEmail` (for example `hasVerifiedEmail()`), which `User` does not have, so those routes are not functional for this model.

If email verification is required, implement `MustVerifyEmail` on `User` and run the existing tests in `tests/Feature/Auth/EmailVerificationTest.php`.

## Authorisation: staff and permissions

### Concepts

| Concept | Definition |
|---|---|
| Staff | `User::isStaff()`: `is_admin` is true or `role_id` is set. |
| Admin access | `User::canAccessAdmin()`: account active and (`is_admin` or has a role). |
| Super Admin | `is_admin` true or role slug `super-admin`. Has every permission automatically. |
| Module / action | Defined in `config/admin-modules.php`. Actions: `view`, `create`, `edit`, `delete`. Some modules offer fewer (for example `reports`, `quiz_results`, `course_progress`, `dashboard` and `documentation` are view-only; `imports` has view and create; `settings` has view, edit and delete). |
| Role | `roles` row with a set of `role_permissions` rows. |
| Custom user permissions | `user_permissions` rows. If a user has any, they **replace** the role's permissions entirely. |

### Enforcement points

1. **`admin` middleware** (`EnsureUserIsAdmin`) on the whole `/admin` group: checks admin access and the route's (module, action), returning 403 otherwise. This is the authoritative check.
2. **Navigation** (`AdminAccess::navGroups`) and `@adminCan` hide links the user cannot use.
3. **Controllers** add business safeguards: self-deactivation and last-Super-Admin protection (`UserController`), system-role protection and Super-Admin-only editing of the Super Admin role (`RoleController`).
4. Route names without a mapping in `route_modules` are not permission-checked beyond `canAccessAdmin()`. When adding an admin route group, add its prefix to `route_modules` (see `09-testing-and-maintenance.md`).

`AdminAccess::sanitize()` discards unknown modules and actions from submitted permission data, and ensures `view` accompanies any other action of a module that has it.

### Learner-side authorisation

- Course audience: `Course::isAvailableTo()` is enforced server-side on enrolment and bundle enrolment; `User::canAccessCourse()` on lesson pages. Draft courses and bundles return 404 to non-staff.
- Quiz and attempt routes check enrolment and publish state; attempt routes (`take`, `autosave`, `submit`, `result`) abort with 403 unless `quiz_attempts.user_id` equals the authenticated user.
- `lessons.complete` requires enrolment and refuses locked lessons, so progress cannot be forged to bypass lesson order.
- Time limits are enforced on the server (deadline plus a 20-second grace); the browser timer is only a convenience.

## CSRF

Laravel's CSRF validation is part of the `web` group and no routes are excluded (there are no `except` entries in `bootstrap/app.php`). Forms use `@csrf`; the JSON calls (lesson completion, quiz autosave) are made with the page's token. A stale token yields HTTP 419; see the `SESSION_DOMAIN` note in `07-deployment-and-operations.md`.

## Rate limits

| Route | Limit |
|---|---|
| `POST /register` | `throttle:10,1` |
| `POST /forgot-password`, `POST /reset-password` | `throttle:6,1` |
| `GET verify-email/{id}/{hash}`, `POST email/verification-notification` | `throttle:6,1` |
| `POST /language` | `throttle:60,1` |
| `POST /quizzes/attempts/{attempt}/autosave` | `throttle:120,1` |
| `POST /login` | 5 failed attempts per email and IP (`LoginRequest`) |

The public contact form (`POST /contact`), enrolment POSTs and all admin routes have no explicit throttle. The limiter store is the configured cache (`CACHE_STORE`, `database` by default).

## Input validation highlights

- Registration: required names, unique lowercase email, strong password with confirmation, `learner_type` in the allowed list, partner organisation must exist and be active, access code check, optional KYC with regexes (phone `^\+?[0-9][0-9\s\-()]{6,19}$`, national ID 6-32 letters and digits, date of birth between 1900 and today, gender from a fixed list), location values validated against the built-in Rwanda province/district/sector tree.
- Contact form: `name` and `email` required (max 255), `subject` max 255, `message` max 5000.
- Lessons: `content` max 10000, `source_url` must be a URL (max 500).
- Quizzes: `passing_grade` 0-100, `time_limit_minutes` 1-300, `max_attempts` 1-100; questions require a valid correct option; quiz placement (chapter/lesson) is checked against the course.
- Translations: arrays of strings, max 20000 characters each.
- CSV import: file extension `csv`, max 5120 KB, required headers, per-row email validation (`FILTER_VALIDATE_EMAIL`).
- Language switch: `locale` must be one of the configured locales; the redirect target is only used if it begins with the site's own URL.
- Output encoding: Blade `{{ }}` is used for user-supplied text, including text lessons and contact messages. `{!! !!}` appears only where the interpolated course title is passed through `e()` (live session email) or in static admin help text.

## File upload handling

| Upload | Rules | Stored at |
|---|---|---|
| Course banner | image, `jpeg,png,jpg,gif,webp`, max 2 MB | `courses/` (public disk) |
| Site logo | image, `jpeg,png,jpg,gif,webp,svg`, max 2 MB | `site/` |
| Lesson PDF | `mimes:pdf`, max 50 MB | `lessons/pdf/` |
| Lesson video | `mimes:mp4,mov,avi,webm`, max 100 MB | `lessons/video/` |
| Student CSV | extension `csv`, max 5 MB | `imports/progress-csv` on the private `local` disk, deleted after import or on failure |

Files are named by Laravel (random hash names) rather than the client's file name. `StorageController` only serves paths under fixed prefixes and rejects `..`. Uploads happen only through the admin panel. **`lesson-file/{path}` and `course-image/{path}` are public: no login is checked**, so anyone who knows a lesson file URL can download it, including PDFs and videos of restricted courses. The file names are unguessable but the URL appears in the page source of the lesson.

## Sensitive data

- `users.national_id` is encrypted at rest with `APP_KEY` (cast `encrypted`) and is in `$hidden`. It is shown decrypted on the admin user detail page to staff with the `users` view permission.
- `live_sessions.meeting_password` is stored in plain text and included in invitation emails.
- Contact messages (name, email, message) are stored in plain text and visible to staff with the `settings` permission.
- Imported users receive a random 32-character password they do not know; they must use password reset.

## Secrets handling and operator duties

1. Keep `.env` out of version control. `.gitignore` excludes `.env`, `.env.backup` and `.env.production`; never commit `.env.example` with real values. Restrict file permissions on `.env` to the app user.
2. Back up `APP_KEY` securely. Losing or changing it breaks decryption of `national_id`, invalidates sessions and signed URLs.
3. Set `APP_ENV=production` and `APP_DEBUG=false`; debug pages leak configuration.
4. Change the initial administrator password immediately and set `ADMIN_PASSWORD` to a strong value before seeding. The fallback in `config/admin.php` and the placeholder in `.env.example` are weak and public. `php artisan admin:ensure` prints the password to the terminal.
5. Do not run the demo seed on production: `DatabaseSeeder` creates a `test@example.com` learner with a fixed weak password and sample courses. Delete that account if it was created.
6. Rotate credentials on staff turnover or suspected exposure: the SMTP password (`MAIL_PASSWORD`), the database password, `ADMIN_PASSWORD`/the admin user's password, any `AWS_*` keys, and `APP_KEY` only with a plan for re-encrypting `national_id`.
7. Use HTTPS (`APP_URL=https://...`) so cookies get the `Secure` flag (`SESSION_SECURE_COOKIE` defaults to true then). Cookies are `HttpOnly` and `SameSite=lax` by default.
8. Because the app trusts every proxy (`trustProxies(at: '*')`), restrict direct access to the PHP upstream so client-supplied `X-Forwarded-*` headers cannot be spoofed.
9. Review role assignments periodically; give staff the minimum modules and actions. The `users` and `roles` modules effectively allow privilege changes.
10. Keep dependencies updated (`composer audit`, `npm audit`) and install from lock files.
11. Restrict who can read `storage/logs` and database backups; they contain email addresses and, in the `log` mail mode, full email bodies including password reset links.
