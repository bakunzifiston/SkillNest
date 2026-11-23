# Key Workflows

## Registration with partner and agent flow

Classes: `Auth\RegisteredUserController`, `User`, `PartnerOrganization`, `AgentKyc`, `RwandaLocations`, `Course::registerLinkParams()`.

1. A visitor opens `GET /register` (middleware `guest`). The controller loads active partner organisations. Query parameters pre-fill the form: `type=partner`, `organization=<id>` and `course=<slug>` (these are produced by `Course::registerLinkParams()` on restricted courses). If `course` names a published course, `url.intended` is stored in the session so the learner returns to it after sign-up.
2. `POST /register` (`throttle:10,1`). `AgentKyc::normalize()` cleans phone, national ID, date of birth and gender input.
3. Validation: first and last name required; email lowercase, valid, unique; password confirmed and meeting `Rules\Password::defaults()`; `learner_type` must be `agent` or `partner` (blank means agent); `partner_organization_id` is required for partners and must reference an **active** organisation; KYC rules (all optional); location rules from `RwandaLocations::validationRules()`.
4. For `partner` the controller loads the active organisation and calls `acceptsRegistrationCode()` on the submitted `partner_code`. A missing or wrong code raises a validation error on `partner_code`. Organisations without a code accept nobody.
5. `RwandaLocations::validateHierarchy()` checks province, district and sector for Rwanda. For any other country the three fields are cleared.
6. KYC fields are kept only for Agents (`AgentKyc::only()`). The user is created with `locale = app()->getLocale()`, so later emails use the language of registration.
7. `event(new Registered($user))` fires, the user is logged in (`Auth::login`) and redirected to the intended URL or `/dashboard`.

Administrators can also create users (`Admin\UserController@store`): these get `email_verified_at = now()`, an optional role and optional custom permissions. Partners are managed by `Admin\PartnerOrganizationController`; `regenerate-code` replaces the code and the old one stops working immediately.

## Enrollment and access check

Classes: `CourseController`, `Course::isAvailableTo()`, `User::canAccessCourse()`, `User::hasEnrolled()`.

`Course::isAvailableTo(?User $user)`:

1. `access_level = all` (or blank): true for everyone including guests.
2. No user: false.
3. `canAccessAdmin()`: true (staff can preview any course).
4. `agents`: `isAgent()` (any user whose `learner_type` is not `partner`). `partners`: `isPartner()`. `partner`: user is a Partner **and** `partner_organization_id` is not NULL and equals the user's own.

`User::canAccessCourse($course)` is `isAvailableTo($this) || hasEnrolled($course)`, so learners enrolled before an audience change keep access.

Enrolment sequence (`POST /courses/{course}/enroll`):

1. `ensurePubliclyAccessible()`: unpublished courses return 404 unless the user is staff.
2. Guests are sent to login with `url.intended` stored.
3. Already enrolled: redirect with an info message.
4. `isAvailableTo($user)` must be true, otherwise 403 (server-side check independent of the hidden button).
5. `Enrollment::create()`, `students_count` incremented, redirect to the first lesson (lowest `sort_order` among all lessons) or back to the course if it has none.

Direct lesson URL (`GET /courses/{course}/lessons/{lesson}`) additionally auto-enrols a learner into a free course (`price <= 0`) when `canAccessCourse()` is true; a priced course redirects to the course page with "Please enroll first" (there is no payment step, so this only matters if a price is set and enrolment is done via the button).

Bundle enrolment (`POST /bundles/{bundle}/enroll`) filters the published courses of the bundle through `isAvailableTo()`; if none remain it refuses. Otherwise it creates the `bundle_enrollments` row and course enrolments, then calls `refreshProgress()`.

## Lesson completion and lesson-order locking

Classes: `CourseController@completeLesson`, `Course`, `LessonCompletion`, `resources/js/lesson-progress.js`.

Status calculation, `Course::lessonStatuses($completedLessonIds, $viewer, $takenQuizIds)`:

1. `orderedLessons()` returns all lessons sorted by chapter `sort_order`, then lesson `sort_order`.
2. Ordering applies when `sequential_lessons !== false` and the viewer is not staff (`canAccessAdmin()`).
3. Walking the list with a `previousDone` flag: a completed lesson is `completed`; otherwise `open` (no ordering), `in_progress` (previous step done) or `locked`.
4. After each lesson, `previousDone = completed AND pendingQuizAfter(lesson) === null`. `pendingQuizAfter()` returns the first **published** quiz whose `lesson_id` is that lesson and which the learner has not yet submitted (`User::submittedQuizIds()` looks at `quiz_attempts.submitted_at`). A pending quiz therefore keeps the next lesson locked.
5. A lesson already completed stays open even if earlier lessons were skipped (for example after a CSV import).

Opening a lesson (`showLesson`): if the status is `locked`, `blockingStepBefore($lesson, ...)` returns `[lesson, quiz|null]`, the first unfinished step. The learner is redirected to that quiz (message "Take the quiz first...") or to that lesson (message "This lesson is locked...").

Completing a lesson (`POST /lessons/{lesson}/complete`, normally JSON from `lesson-progress.js` when a video ends, 90 percent is watched or content is read):

1. Requires login (401 JSON otherwise) and enrolment (403).
2. Recomputes statuses; a `locked` lesson cannot be marked complete (403), which prevents unlocking later lessons by crafted requests.
3. `LessonCompletion::firstOrCreate()` (idempotent).
4. For each bundle containing the course in which the learner is enrolled, `BundleEnrollment::refreshProgress()` runs.
5. JSON response carries `completed_count` and `total_lessons`; a non-JSON request redirects to the next lesson or back to the course.

Quiz opening rule, `Course::quizBlocker($quiz, ...)`: staff are never blocked. Otherwise `lessonsRequiredBefore($quiz)` is everything up to the quiz's lesson, up to the last lesson of its chapter, or the entire course for a course-wide quiz; the first incomplete one is returned and the learner is redirected to it.

## Quiz attempt lifecycle

Classes: `QuizController`, `Quiz::attemptStateFor()`, `QuizAttempt`, `QuizAttemptAnswer`, `Console\Commands\CloseExpiredQuizAttempts`.

Constant: `QuizAttempt::GRACE_SECONDS = 20` (extra seconds after the deadline during which a submission is still accepted).

1. **View** `GET /courses/{course}/quizzes/{quiz}`: login, enrolment, published and `quizBlocker` checks. `attemptStateFor($user)` first closes any of the user's unfinished attempts whose time has expired, then returns `used`, `max`, `unfinished`, `passed`, `canStart`, `reason` (`passed` or `limit`) and `last`.
2. **Start** `POST .../start`: inside a transaction that locks the quiz row (`lockForUpdate`) it recomputes the state. An unfinished attempt is resumed; if `canStart` is false (retake after pass forbidden, or `max_attempts` reached) the learner is redirected with an error; otherwise a `quiz_attempts` row is created with `started_at = now()` and `total_points`. The lock stops a double click from creating two attempts or exceeding the maximum. Only submitted attempts count as used, so an unfinished attempt never consumes an attempt.
3. **Take** `GET /quizzes/attempts/{attempt}`: only the owner (403 otherwise). Submitted attempts redirect to the result. If `isExpired()` the attempt is closed as timed out. Otherwise the page receives `remainingSeconds` and any `draft_answers`.
4. **Autosave** `POST .../autosave` (`throttle:120,1`, JSON): the page posts `answers` every 15 seconds. `saveDraft()` keeps only answers whose question and option belong to the quiz (`sanitizeAnswers`) in `draft_answers`. If the attempt is already submitted or has just expired, the server closes it if needed and answers HTTP 409 with a `redirect` URL to the result.
5. **Submit** `POST .../submit`: if `isExpired(GRACE_SECONDS)` the attempt is closed with the saved draft and the posted answers are ignored ("Time is up"). Otherwise the posted `question_<id>` values are graded by `submitAnswers()`.
6. **Grading** `submitAnswers($selected, $timedOut)`: no-op if already submitted. For each question one `quiz_attempt_answers` row is created (`is_correct`, `points_earned`; unanswered scores 0). `percentage = score / total_points * 100` (2 decimals), `passed = percentage >= passing_grade`. `submitted_at` is `now()`, or the deadline (`expiresAt()`) for timed-out attempts. `draft_answers` is cleared.
7. **Timeout** `closeAsTimedOut()` submits `draft_answers` (or nothing) with `timed_out = true`. It is triggered when the learner returns (take, autosave, state check, submit) and by the scheduler: `php artisan quizzes:close-expired` runs `everyMinute()` (`routes/console.php`), selects unsubmitted attempts of quizzes that have a time limit (chunks of 100) and closes the expired ones, so results exist even if the learner never comes back. This needs the server cron entry described in `06-integrations-and-configuration.md`.
8. **Result** `GET /quizzes/attempts/{attempt}/result`: owner only; shows the score, per-question answers and the number answered.

Admins review attempts under Quiz Results (`Admin\QuizResultsController`: filter, detail, CSV export at `admin.quiz-results.export`).

## Roles and permission checks

Classes: `EnsureUserIsAdmin`, `AdminAccess`, `User::hasPermission()`, `Role`, `config/admin-modules.php`.

1. Request to `/admin/...` passes `auth` then `admin`.
2. `canAccessAdmin()` must be true: `is_active` and (`is_admin` or `role_id` not NULL). Otherwise 403.
3. The route name is resolved to `[module, action]` by `AdminAccess::resolve()`. Unknown action names for the module fall back to `view`. Names that map to no module skip the permission step.
4. `hasPermission($module, $action)`: Super Admins (`is_admin` or role slug `super-admin`) are always allowed; otherwise the action must be in `permissionMap()`, which uses the user's own `user_permissions` rows if any exist, else the role's `role_permissions`.
5. On denial: 403, except that denying `admin.dashboard` redirects to the first accessible module.
6. Saving permissions (`Role::syncPermissions`, `User::syncPermissions`) passes input through `AdminAccess::sanitize()`, which drops unknown modules/actions and always adds `view` when a module has any other action and supports `view`.
7. Safeguards: the Super Admin role is re-synced with all permissions whenever `Role::ensureSystemRoles()` runs; system roles cannot be deleted; only a Super Admin may edit the Super Admin role; a user cannot deactivate themselves; the last active Super Admin cannot be demoted, deactivated or deleted.

## Student CSV import

Classes: `Admin\StudentImportController`, `App\Services\CourseProgressCsvImporter`. Permission module `imports` (view to see the page, create for preview and import).

1. `GET admin/imports/students`: choose a course and a CSV.
2. **Preview** `POST .../preview`: validates `course_id` and a CSV file (extension csv, max 5120 KB). The file is stored on the default `local` disk in `imports/progress-csv`; the path and course id are kept in the session.
3. `parse()`: reads the header (UTF-8 BOM stripped), requires the four headers `First Name`, `Last Name`, `Email` and `% Completed` (case-insensitive), detects per-content columns (anything not in `METADATA_HEADERS`) and returns normalised rows. Rows with a missing or invalid email carry an error and are skipped later.
4. `preview()` labels each row with an action (create user and enrol, enrol existing user, already enrolled and update progress, or skip when invalid or outside the course audience) and how many lessons will be marked complete. Invalid files are deleted and shown as a validation error.
5. **Import** `POST admin/imports/students`: uses the stored file (or a newly uploaded one), re-parses it and calls `import()`. Each row runs in its own transaction:
   - existing user outside the course audience and not enrolled: row skipped with an error;
   - missing user: created with names, `password = Str::password(32)` (random, unknown), `is_admin = false`, the learner attributes from `Course::defaultLearnerAttributes()` (a partner-organisation course creates a Partner of that organisation, otherwise an Agent) and `email_verified_at = now()`;
   - an enrolment is created if missing (its `created_at` comes from Activated/Started/Completed At when given);
   - `% Completed` is mapped onto the course's lessons in order: `round(percent / 100 * totalLessons)` lessons (all at 100) get a `lesson_completions` row via `firstOrCreate`.
6. At the end `courses.students_count` is recalculated and the stored CSV is deleted. A summary (created/existing users, enrolments created/skipped, lessons completed, skipped rows, errors) is shown.
7. No email is sent to imported users. They must use "Forgot password" to set a password.

## Content translation fallback

Classes: `Models\Concerns\HasTranslations`, `SetLocale`, `admin/partials/translation-fields`.

1. English text always lives in the normal columns. Other languages are stored as `translations = {"rw": {"title": "...", "description": "..."}}` on the ten translatable tables.
2. Admin forms post `tr[rw][field]`. Validation uses `Model::translationRules()` (max 20000 characters per value). `saveTranslations()` keeps only declared fields for configured non-default locales, trims values, drops blanks and stores NULL when nothing remains.
3. Learner views call `$model->tr('field')`. If the active locale is not the default and `translations[locale][field]` is a non-blank string it is returned; otherwise the English original is returned. Nothing needs translating for the site to work.
4. Interface text uses `__('English sentence')`: Laravel looks the sentence up in `lang/rw.json` and falls back to the English key itself.
5. Admin screens intentionally render the English original (`name`, `title`, and so on) to keep staff screens consistent.

## Password reset mail

Classes: `Auth\PasswordResetLinkController`, `Auth\NewPasswordController`, `User::sendPasswordResetNotification()`, `Notifications\ResetPassword`, view `emails/password-reset`.

1. `GET /forgot-password` shows the form (`guest`). `POST /forgot-password` (`throttle:6,1`) lowercases and validates the email.
2. `Password::sendResetLink()` creates a token in `password_reset_tokens` and calls `User::sendPasswordResetNotification()`, which notifies with the branded `ResetPassword` notification. The message is rendered in the user's language (`preferredLocale`), subject "Reset Your Password - <app name>", with the expiry taken from `auth.passwords.users.expire` (60 minutes default).
3. The notification is sent **synchronously** by the configured mailer (it does not implement `ShouldQueue`). With `MAIL_MAILER=log` the message is only written to `storage/logs/laravel.log`.
4. Any exception is caught and logged (`Password reset email failed to send.`). Whatever happens, the browser sees the same generic message ("If an account exists with that email address..."), which prevents account enumeration.
5. `GET /reset-password/{token}` shows the form (a friendly error page for invalid links); `POST /reset-password` (`throttle:6,1`) validates and sets the new password.
6. Use `php artisan mail:test you@example.com` to verify delivery settings.
