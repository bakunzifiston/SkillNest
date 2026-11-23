# System Overview

## Purpose

KoraLink Academy (the repository folder is `SkillNest`, the default `APP_NAME` is `Koralink`) is a server-rendered e-learning platform built on Laravel 12. It lets an organisation:

- publish a catalogue of courses (category, instructor, chapters, lessons, quizzes, live sessions, course bundles);
- register learners as **Agents** or **Partners** (members of a partner organisation such as SFH) and restrict courses to one of those audiences;
- track lesson completion and quiz attempts, and release lessons in order;
- administer the platform through an admin panel with role-based, per-module permissions;
- serve the whole site in English and Kinyarwanda.

There is no payment processing (see `10-known-limitations.md`). Courses have a `price` column but enrolment is always free.

## Technology stack

Versions are taken from `composer.json`, `composer.lock`, `package.json` and `package-lock.json`.

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | `^8.2` required (dev machine runs 8.4) |
| Framework | `laravel/framework` | `^12.0` (locked at 12.50.0) |
| Console REPL | `laravel/tinker` | `^2.10.1` |
| Markdown rendering (docs module) | `league/commonmark` (framework dependency) | 2.8.0 locked |
| Dev tools | `laravel/breeze` `^2.3`, `laravel/pail` `^1.2.2`, `laravel/pint` `^1.24`, `laravel/sail` `^1.41`, `phpunit/phpunit` `^11.5.3`, `mockery/mockery`, `fakerphp/faker`, `nunomaduro/collision` | dev only |
| Database | MySQL (default in `.env.example`); SQLite supported (default in `config/database.php`, used by tests); MariaDB, PostgreSQL, SQL Server drivers exist in config | - |
| Front-end build | Vite `^7.0.7` (locked 7.3.1) with `laravel-vite-plugin` `^2.0.0` | - |
| CSS | Tailwind CSS `^3.1.0` (locked 3.4.19) with `@tailwindcss/forms`, PostCSS and Autoprefixer | - |
| JS | Alpine.js `^3.4.2` (locked 3.15.8), Axios `^1.11.0` | - |
| Admin charts | Chart.js 4.4.1 loaded from the jsDelivr CDN by `admin/dashboard.blade.php` | - |
| Tests | PHPUnit via `php artisan test` | - |

`package.json` also lists `@tailwindcss/vite` `^4`, but `vite.config.js` does not use it; Tailwind runs through `postcss.config.js`.

## Repository layout

```text
app/
  Console/Commands/      quizzes:close-expired, admin:ensure, mail:test
  Http/Controllers/      public + learner controllers
    Admin/               one controller per admin module
    Auth/                Breeze authentication controllers
  Http/Middleware/       EnsureUserIsAdmin, SetLocale
  Http/Requests/         LoginRequest, ProfileUpdateRequest
  Mail/                  ContactMessageReceived, LiveSessionInvitation
  Models/                Eloquent models (+ Concerns/HasTranslations)
  Notifications/         ResetPassword (branded)
  Providers/             AppServiceProvider
  Services/              CourseProgressCsvImporter
  Support/               AdminAccess, AgentKyc, CategoryTheme, RwandaLocations, Documentation
  View/Components/       AppLayout, GuestLayout
bootstrap/app.php        routing, middleware, health endpoint
config/                  Laravel config + admin.php, admin-modules.php, localization.php
database/migrations/     schema history (see 04-database.md)
database/seeders/        DatabaseSeeder, CategoryCourseSeeder
docs/                    user-guide/ and system/ Markdown (this documentation)
lang/                    rw.json, en/*.php, rw/*.php, GLOSSARY.md
public/                  web root (index.php, build/ Vite output, images)
resources/views/         Blade templates (layouts, admin, courses, quizzes, auth, emails, ...)
resources/js, css/       Vite entry points
routes/                  web.php, auth.php, console.php
storage/app/public       uploaded images, PDFs, videos
storage/app/private      CSV imports (local disk)
tests/Feature, Unit/     PHPUnit tests
```

Middleware is registered in `bootstrap/app.php` (Laravel 12 style). There are no API routes and no `routes/channels.php`.

## Request lifecycle (high level)

1. The web server sends every request to `public/index.php`, which boots Laravel via `bootstrap/app.php`.
2. `bootstrap/app.php` registers `routes/web.php` (which includes `routes/auth.php`), `routes/console.php` and a health endpoint at `/up`. It trusts all reverse proxies (`trustProxies(at: '*')`).
3. The `web` middleware group runs (cookies, session, CSRF, route-model binding) followed by the appended `SetLocale` middleware that selects English or Kinyarwanda.
4. Route-level middleware runs: `auth`, `verified`, `guest`, `throttle:N,M` or the custom `admin` alias (`EnsureUserIsAdmin`), which also enforces per-module permissions.
5. A controller loads models (Eloquent), applies business rules (often in the model: `Course::lessonStatuses`, `User::canAccessCourse`, `Quiz::attemptStateFor`) and returns a Blade view or a redirect/JSON response.
6. Blade views extend one of the layouts in `resources/views/layouts/` and call `__()` for translatable text and `$model->tr('field')` for translatable content.
7. `AppServiceProvider` shares `siteLogoUrl` with every view and feeds `layouts.admin` with the permission-filtered navigation (`adminNavGroups`).

## Glossary

| Term | Meaning |
|---|---|
| Agent | A learner with `users.learner_type = 'agent'` (the default). May supply optional KYC details. |
| Partner | A learner with `learner_type = 'partner'`, tied to a `partner_organizations` row and registered with that organisation's access code. |
| Partner organisation | A `PartnerOrganization` (for example SFH) whose learners can be given partner-only courses. Not the same as a Partner logo. |
| Partner logo | A `Partner` row: a logo shown on the public home page. |
| KYC | Optional identity details for Agents: phone, national ID (stored encrypted), date of birth, gender (`App\Support\AgentKyc`). |
| Access level | `courses.access_level`: `all`, `agents`, `partners` or `partner` (one specific organisation). |
| Student | A user who is not staff (`User::students()`: `is_admin = false` and `role_id IS NULL`). |
| Staff | A user with `is_admin = true` or a `role_id`. |
| Super Admin | `is_admin = true` or the role with slug `super-admin`; implicitly holds every permission. |
| Module | An entry in `config/admin-modules.php` (for example `courses`); the unit of permission. |
| Action | One of `view`, `create`, `edit`, `delete` within a module. |
| Sequential lessons | `courses.sequential_lessons`: a lesson unlocks only when the previous one (and any quiz under it) is done. |
| Bundle | A group of courses (`bundles`, `bundle_courses`) learners can enrol in at once. |
| Live session | A scheduled online meeting linked to a course, with invited attendees and invitation emails. |
| Draft / Published | `courses.status` and `bundles.status` (bundles also have `archived`). Only published items are public. |
| Translations | The `translations` JSON column on content tables; English stays in the normal columns. |
| `rw` / `en` | Locale codes for Kinyarwanda and English. |
