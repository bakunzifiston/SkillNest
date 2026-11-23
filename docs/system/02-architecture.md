# Architecture

## Layers

```text
Browser
  |
  v
routes/web.php (+ routes/auth.php)            URL -> controller method, route middleware
  |
  v
Middleware   web group: cookies, session, CSRF, bindings, SetLocale
             route:     auth | guest | verified | signed | throttle | admin
  |
  v
Controllers  App\Http\Controllers\*           (public / learner)
             App\Http\Controllers\Admin\*     (admin panel)
             App\Http\Controllers\Auth\*      (login, registration, reset)
  |
  +--> Form validation (inline $request->validate(), LoginRequest, ProfileUpdateRequest)
  |
  v
Domain logic  App\Models\*   (business rules live mostly on models)
              App\Services\CourseProgressCsvImporter
              App\Support\*  (AdminAccess, AgentKyc, RwandaLocations, CategoryTheme, Documentation)
  |
  v
Database (Eloquent)  /  Storage disks (public, local)  /  Mail
  |
  v
Blade views  resources/views/**   (layouts + Alpine + Tailwind classes + a little inline CSS)
```

There is no repository layer and no API layer. Controllers are thin to medium; the heavier rules are on models (`Course`, `User`, `Quiz`, `QuizAttempt`, `PartnerOrganization`) so that tests and several controllers can share them.

## Routing

All routes are in `routes/web.php`; Breeze auth routes are in `routes/auth.php` (required at the bottom of `web.php`). `routes/console.php` only registers the `inspire` command and the scheduled `quizzes:close-expired`. There are no API routes.

| Group | Middleware | Contents |
|---|---|---|
| Storage | none | `GET course-image/{path}`, `GET lesson-file/{path}` served by `StorageController` |
| Language | `throttle:60,1` | `POST /language` (`LocaleController@update`) |
| Public site | none | `/`, `/courses`, `/courses/{course}`, `/bundles`, `/bundles/{bundle}`, `/about`, `/contact`, enrol POST routes (they redirect guests to login) |
| Learner | `auth` | `/my-courses`, `/my-bundles`, lesson pages, `POST /lessons/{lesson}/complete`, quiz show/start/take/autosave/submit/result |
| Dashboard | `auth`, `verified` | `GET /dashboard` (`LearnerDashboardController`) |
| Profile | `auth` | `/profile` edit/update/destroy |
| Admin | `auth`, `admin`, prefix `admin`, name prefix `admin.` | every admin module |
| Auth | `guest` / `auth` / `signed` / `throttle` | see `08-security-and-permissions.md` |

Course and bundle URLs use the slug as route key (`getRouteKeyName()` returns `slug`).

## Middleware

Defined or configured in `bootstrap/app.php`:

| Middleware | Where | Purpose |
|---|---|---|
| `trustProxies(at: '*')` | global | Trust any reverse proxy so HTTPS and host are detected correctly (prevents redirect loops behind nginx/Apache). |
| `App\Http\Middleware\SetLocale` | appended to the `web` group, after the session starts | Chooses the request language. Order of precedence: signed-in user's `users.locale`, session key `locale_choice`, cookie `koralink_lang`, then `APP_INITIAL_LOCALE`. Sets `app()->setLocale()` and `Carbon::setLocale()`, writes the cookie and a `Content-Language` header. A signed-in user without a saved language adopts the guest choice. |
| `admin` alias = `App\Http\Middleware\EnsureUserIsAdmin` | route alias | Requires `User::canAccessAdmin()` (active account and either `is_admin` or a role), else 403. Then maps the route name to a (module, action) pair with `AdminAccess::resolve()` and requires `User::hasPermission()`. If a user lacks access to `admin.dashboard` it redirects to the first module they may see. |

Standard Laravel `web` group middleware also run (cookie encryption, queued cookies, session start, error sharing from session, CSRF validation, route model binding). Framework aliases used by the routes: `auth`, `guest`, `verified`, `signed`, `throttle`.

Throttles in use: `throttle:60,1` (language), `throttle:120,1` (quiz autosave), `throttle:10,1` (registration POST), `throttle:6,1` (forgot/reset password POSTs, email verification), plus the login limiter inside `LoginRequest` (5 failed attempts per email+IP key).

## Admin authorisation pipeline

1. `config/admin-modules.php` declares modules, their actions, the navigation entries, a `route_modules` prefix map (route name prefix to module) and `route_actions` overrides.
2. `AdminAccess::moduleForRoute()` picks the longest matching prefix. `AdminAccess::actionFromRoute()` maps the last route-name segment (`create`/`store`/`preview` to create; `edit`/`update`/`add`/`order`/`status` to edit; `destroy`/`destroy-all`/`remove` to delete) or falls back to the HTTP verb.
3. `User::hasPermission()` returns true for Super Admins, otherwise looks the action up in `permissionMap()`: per-user rows in `user_permissions` win over the role's `role_permissions`.
4. Blade uses `@adminCan('module', 'action')` (registered in `AppServiceProvider`) and the layout renders only the navigation groups returned by `AdminAccess::navGroups()`.

An admin route whose name matches no prefix is allowed through for any staff member who passes `canAccessAdmin()`; see `10-known-limitations.md`.

## File storage and serving

Uploads are written to the `public` disk (`storage/app/public`). Files are not served from `public/storage`; instead `StorageController` streams them, so the site works even if `php artisan storage:link` was never run:

| Route | Method | Allowed path prefixes | Notes |
|---|---|---|---|
| `course-image/{path}` | `courseImage` | `courses/`, `bundles/`, `site/`, `partners/` | `Cache-Control: public, max-age=86400`. Used for course banners, bundle thumbnails, the site logo and partner logos. |
| `lesson-file/{path}` | `lessonFile` | `lessons/` | Lesson PDFs and videos; sends `Accept-Ranges: bytes` header. |

Both reject paths containing `..` and any prefix not listed (404). Model accessors build the URLs: `Course::bannerUrl`, `Bundle::thumbnailUrl`, `Partner::logoUrl`, `Lesson::fileUrl()`; values starting with `http` are used as-is. CSV imports use the default `local` disk (`storage/app/private/imports/progress-csv`) and are deleted after processing.

## Front-end approach

- **Blade** templates, server-rendered. Layouts: `layouts/site.blade.php` (public and learner pages), `layouts/admin.blade.php` (admin), `layouts/app.blade.php` and `layouts/guest.blade.php` (Breeze components `x-app-layout` / `x-guest-layout`), `layouts/navigation.blade.php`. Reusable pieces are in `resources/views/components` and `resources/views/partials`.
- **Alpine.js** is started from `resources/js/app.js` (`window.Alpine = Alpine; Alpine.start()`) and used through `x-data` in 13 views for dropdowns, modals and similar behaviours.
- **Tailwind CSS 3** utility classes plus brand colours defined in `tailwind.config.js` and CSS variables in `resources/css/app.css`. `site.blade.php` and `admin.blade.php` each contain one inline `<style>` block.
- **Vite entry points** (`vite.config.js`): `resources/css/app.css`, `resources/js/app.js`, `resources/js/lesson-progress.js`. The first two are loaded by every layout through `@vite`; `lesson-progress.js` is loaded only by `courses/lesson.blade.php`. It handles automatic lesson completion (video end or 90 percent watched, or content read) and mounts the YouTube embed on click, posting to `lessons.complete`.
- The quiz-taking page (`quizzes/take.blade.php`) contains its own countdown and autosave script that calls `quizzes.autosave`.
- External resources loaded at runtime: Bunny Fonts (`fonts.bunny.net`, referenced in `components/brand-meta.blade.php` and `welcome.blade.php`), Chart.js from `cdn.jsdelivr.net` on the admin dashboard, and YouTube embeds in lessons.
- Production assets are the compiled files in `public/build`. If they are missing or stale, pages that use `@vite` fail; see `07-deployment-and-operations.md`.

## Cross-cutting services

| Concern | Implementation |
|---|---|
| Localisation | `SetLocale`, `LocaleController`, `config/localization.php`, `lang/rw.json`, `HasTranslations` trait (`tr()`), `User::preferredLocale()` for mail |
| Settings | `App\Models\Setting` key/value rows cached for one hour (`Cache::remember`); currently only `site_logo` |
| Site logo | `AppServiceProvider::siteLogoUrl()` guarded against a missing `settings` table (safe during `composer install`) |
| Admin navigation | `View::composer('layouts.admin')` calls `AdminAccess::navGroups($user)` |
| HTTPS | `URL::forceScheme('https')` when `APP_URL` starts with `https://` |
| Documentation | `App\Support\Documentation` and `Admin\DocumentationController` render `docs/*` Markdown (see `03-modules.md`) |
