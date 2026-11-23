# Testing and Maintenance

## Running the tests

```bash
composer install            # with dev dependencies
php artisan config:clear
php artisan test
php artisan test --filter=LessonOrderTest        # one class
php artisan test tests/Feature/QuizAttemptRulesTest.php
```

`composer test` runs `config:clear` and then `php artisan test`. `phpunit.xml` defines two suites (`Unit` in `tests/Unit`, `Feature` in `tests/Feature`) and forces an isolated environment: `APP_ENV=testing`, in-memory SQLite (`DB_DATABASE=:memory:`), `CACHE_STORE=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `BCRYPT_ROUNDS=4`, `APP_INITIAL_LOCALE=en`. Tests therefore never touch the development or production database or send mail. Run them after `config:clear`; a cached configuration would override these values. Run `./vendor/bin/pint` to apply the code style (Laravel Pint is installed as a dev dependency).

## What the test suites cover

### Feature tests (`tests/Feature`)

| File | Coverage |
|---|---|
| `AdminQuizQuestionsTest.php` | Quiz creation and publishing, MCQ and true/false question rules (blank options, required correct answer), quiz placement in a chapter or after a lesson, cross-course placement rejected, quizzes on course and lesson pages, admin dashboard with attempts. |
| `AdminUsersRolesPermissionsTest.php` | Students and inactive staff blocked from admin, role and per-user permission enforcement, view-only roles, creating users and roles, last Super Admin protection, deactivated login, system role protection. |
| `BundlesIndexPageTest.php` | Bundles catalogue page content and that draft bundles are hidden. |
| `CategoryCardsTest.php` | Home page category card theming, colour contrast and safe animations. |
| `ContentTranslationAdminTest.php` | Saving, editing, clearing and validating Kinyarwanda content translations; admin-only; translated content reaching learners (courses, categories, lessons, quizzes) while grading is unchanged; admin screens staying in English. |
| `CourseLessonAccessTest.php` | Opening lessons of free courses with auto-enrolment, lesson/course mismatch redirect, JSON completion, completed-lesson display. |
| `CourseStatusTest.php` | Draft default, hidden drafts, staff preview, status toggle, status badges. |
| `CoursesIndexPageTest.php` | Courses catalogue UI and category filter. |
| `ExampleTest.php` | Smoke test that the application responds. |
| `HomeHeroTest.php` | Home page hero banner, brand colours, call-to-action card, Kinyarwanda wording. |
| `HomePageTest.php` | Home sections with real data; admin link visibility by role. |
| `LearnerAccessTest.php` | Agent/Partner defaults, SFH seed, course audience matrix, partner registration rules and access code, restricted course warnings, enrolment blocking. |
| `LessonOrderTest.php` | Sequential unlocking within and across chapters, locked redirects, locked lesson cannot be completed, staff never locked, `sequential_lessons` off, quiz-under-lesson gating. |
| `LocalizationSmokeTest.php` | Every page renders in English and Kinyarwanda without leaked keys or unfilled placeholders. |
| `LocalizationTest.php` | Default language, switcher, unknown language rejected, safe redirects, persistence in session, cookie and account, email language. |
| `MailConfigTest.php` | `mail:test` warnings, failed reset email handling, `.env.example` mail documentation. |
| `PageBannersTest.php` | Shared banners, menus and colours on public, learner, auth and admin pages in both languages. |
| `ProfileTest.php` | Profile display, update, email verification status on update, account deletion with password. |
| `QuizAttemptRulesTest.php` | Resume instead of duplicate attempts, maximum attempts, retake-after-pass rule, per-student attempts, timed-out attempts closed and counted, late answers ignored, grace period. |
| `TranslationCoverageTest.php` | `lang/rw.json` validity, every `__()`/`trans()` string has a translation, placeholders preserved, admin config labels translated, plural handling. |
| `Auth/AuthenticationTest.php` | Login, bad password, logout (POST and GET), no redirect back to logout. |
| `Auth/EmailVerificationTest.php` | Verification screen, verifying with a signed link, invalid hash. |
| `Auth/PasswordConfirmationTest.php` | Confirm-password screen and checks. |
| `Auth/PasswordResetTest.php` | Reset request and branded email, generic response for unknown emails, invalid links, validation, successful reset. |
| `Auth/PasswordUpdateTest.php` | Changing password with the current password. |
| `Auth/RegistrationTest.php` | Registration page, successful registration, Rwanda location hierarchy rules, non-Rwanda registration. |

### Unit tests (`tests/Unit`)

| File | Coverage |
|---|---|
| `ExampleTest.php` | Placeholder (`assertTrue(true)`). There are no real unit tests; logic is tested through feature tests. |

`tests/TestCase.php` is the shared base class. `database/factories/UserFactory.php` is the only factory.

## Translations: adding Kinyarwanda strings

Interface text:

1. In PHP or Blade wrap the English sentence in `__('English sentence')` (or `trans_choice` for plurals). Use named placeholders (`:name`, `:count`).
2. Add the same English text as a key in `lang/rw.json` with the Kinyarwanda value. Keep placeholders and punctuation identical.
3. Use the approved vocabulary and style in `lang/GLOSSARY.md` (core terms, tone, brand names left untranslated).
4. Laravel's own messages (validation, auth, passwords, pagination) are in `lang/rw/*.php`.
5. Run `php artisan test --filter=TranslationCoverageTest` and `--filter=LocalizationSmokeTest`.

`TranslationCoverageTest` scans every `.php` file in `resources/views` and `app/` (excluding `app/Console`, which stays English) for `__()`, `trans()`, `trans_choice()`, `@lang` and `Lang::get()` calls, and fails when a key has no entry in `lang/rw.json`, when a translation loses a placeholder, or when a label in `config/admin-modules.php` (actions, group names, module labels, nav labels) is not translated. Because only literal string arguments are detected, avoid building translation keys dynamically.

Database content (course titles, lessons, quiz text) is translated by admins in the admin forms, not in files.

## Adding a new admin module

1. **Controller and views**: create `App\Http\Controllers\Admin\<Name>Controller` and views under `resources/views/admin/<name>`. Extend `layouts.admin`.
2. **Routes**: add them inside the `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')` group in `routes/web.php` using a consistent route-name prefix, for example `admin.<name>.index`.
3. **Register the module in `config/admin-modules.php`**:
   - `modules.<key>`: `label`, `icon`, `actions` (any of `view`, `create`, `edit`, `delete`) and one or more `nav` entries (`label`, `route`, `icon`, `active` pattern);
   - add the key to a group in `groups` (or create a group);
   - `route_modules`: map the route-name prefix to the module key (longer prefixes win). Without this the routes are **not** permission-checked;
   - `route_actions`: only for route names whose action cannot be inferred (`create/store/preview` is create; `edit/update/add/order/status` is edit; `destroy/destroy-all/remove` is delete; otherwise the HTTP method decides).
4. **Permission seeding**: Super Admins pass automatically because `hasPermission()` short-circuits for them and `AdminAccess::allPermissions()` is built from the config. To make the stored `super-admin` role's `role_permissions` rows match, run `php artisan admin:ensure` (or `DatabaseSeeder`), which calls `Role::ensureSystemRoles()` and re-syncs all permissions. Other roles get the new module by editing them under Admin > Roles & permissions (the matrix is generated from the config) or on the user. Note the original roles migration copied the modules that existed at that time only; new modules always need the step above for the stored role.
5. **Icons**: `icon` values are rendered by `resources/views/admin/partials/dashboard-icon.blade.php`; reuse an existing name or add a case there.
6. **Translations**: add the module label, nav labels and any new group name to `lang/rw.json`; `TranslationCoverageTest` fails otherwise.
7. **Tests**: add feature tests along the lines of `AdminUsersRolesPermissionsTest` (a role without the module gets 403, a view-only role cannot create or delete).

## Adding a new language

1. Add the locale code to `locales` in `config/localization.php` with `name`, `native` and `short`. It then appears in the language switcher (`components/language-switcher`) and is accepted by `LocaleController` and `SetLocale`. `users.locale` is `string(5)`, so keep codes up to five characters.
2. Create `lang/<code>.json` (copy the keys of `lang/rw.json`) and `lang/<code>/auth.php`, `pagination.php`, `passwords.php`, `validation.php`.
3. Translated content: `HasTranslations::saveTranslations()` and the admin partial `admin/partials/translation-fields` iterate the configured locales (other than `default`), so extra input fields appear automatically; stored data goes under `translations.<code>`.
4. Add a glossary section next to `lang/GLOSSARY.md` and extend `TranslationCoverageTest` and `LocalizationSmokeTest`, which currently check Kinyarwanda only (`rw`).
5. Review hard-coded language checks such as `Course::audienceWarning()` (lower-casing for non-default locales) and the Kinyarwanda plural handling.

## Adding a new lesson type

Lesson types are `text`, `youtube`, `pdf`, `video` (`Lesson::TYPE_*`). To add one:

1. Add a `TYPE_*` constant and an entry in `Lesson::types()` (translated label); add helper accessors if needed.
2. Update the admin validation: both `store` and `update` in `Admin\LessonController` use the literal rule `in:text,youtube,pdf,video`, and the controller handles uploaded files for `pdf` and `video` only.
3. Update the admin form `resources/views/admin/lessons/create` and `edit` (the `lesson-type-fields` blocks and the script that toggles them).
4. Render it in `resources/views/courses/lesson.blade.php` (the `@switch` on `$lesson->type`).
5. Decide how completion is detected: extend `resources/js/lesson-progress.js` (automatic completion) and keep `POST /lessons/{lesson}/complete` as the single place that writes `lesson_completions`.
6. If the type stores uploads, make sure the path prefix is served by `StorageController` and add upload limits to the documentation.
7. Add `lang/rw.json` entries, rebuild assets (`npm ci && npm run build`) and add tests (see `CourseLessonAccessTest`).

## Maintaining this documentation

- The documentation lives in the `docs/` folder in the repository: `docs/user-guide/` (learners and administrators) and `docs/system/` (this System Documentation), with screenshots, where used, in `docs/<book>/images/`. A `docs/README.md` describes the writing conventions.
- Each chapter is one Markdown file named `NN-name.md`. The numeric prefix defines the order; the first `# ` line is the chapter title; headings should not go deeper than `###`.
- The platform renders these files in the admin panel under **Help > Documentation** (permission module `documentation`, view only; classes `Admin\DocumentationController` and `App\Support\Documentation`). It reads the files on each request, strips raw HTML, and offers a combined Markdown or standalone HTML download.
- To update the documentation, edit the Markdown in `docs/`, review it in a pull request, and deploy; no database change or build step is needed. Keep it in step with the code: when you change a route, table, environment variable or workflow, update the matching chapter in the same change.
- Verification tip: after changing the schema, compare `04-database.md` with `php artisan db:table <name>` on a development database.
