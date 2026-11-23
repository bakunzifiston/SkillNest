# Functional Modules

Admin controllers live in `App\Http\Controllers\Admin`, views in `resources/views/admin/<module>`, and route names are prefixed `admin.`. The permission module key (from `config/admin-modules.php`) is given in brackets.

## Catalog

### Categories [`categories`]

- **Purpose**: group courses; shown as cards on the home page and as a filter on `/courses`.
- **Classes**: `Admin\CategoryController` (resource without `show`), `Category` (translatable `name`; `slug`, `icon`, `courses_count`), `App\Support\CategoryTheme`, view partial `partials/category-icon`.
- **Behaviour**: the slug is regenerated from the name on create and update. Deleting a category cascades to its courses (foreign key `cascadeOnDelete`). Two migrations add default Kinyarwanda names for the seeded categories (Business, Design, Development, Marketing).
- **Routes**: `admin.categories.*`.

### Courses [`courses`]

- **Purpose**: the unit of learning. A course has a category, optional instructor, status, audience (`access_level`), a sequential-lessons switch, chapters, lessons, quizzes and live sessions.
- **Classes**: `Admin\CourseController` (resource without `show`, plus `PATCH courses/{course}/status` and `courses/{id}/enrolled-users` via `EnrolledUsersController`), public `CourseController` (`show`, `enroll`, `myCourses`, `showLesson`, `completeLesson`), `Course`, `Enrollment`, `Review`.
- **Behaviour**: the slug is `slug(title)-uniqid()`. `price` is stored but never charged. `Course::STATUS_DRAFT` courses are hidden (404) from non-staff; staff can preview them by URL. A banner image (jpeg, png, jpg, gif, webp, max 2 MB) is stored under `courses/` on the public disk. `students_count` is a denormalised counter incremented on enrolment and recalculated by the CSV importer.
- **Routes**: `admin.courses.*`, `admin.courses.status`, `admin.courses.enrolled-users`; public `courses.index`, `courses.show`, `courses.enroll`, `courses.my-courses`, `courses.lessons.show`, `lessons.complete`.

### Chapters and lessons [`courses`]

- **Purpose**: structure course content. Chapters have `sort_order`; lessons belong to a chapter and have a type.
- **Classes**: `Admin\ChapterController`, `Admin\LessonController`, `Chapter`, `Lesson`.
- **Lesson types** (`Lesson::TYPE_*`): `text` (HTML-escaped text in `content`, max 10000 characters), `youtube` (`source_url`; the id is parsed by `Lesson::youtubeVideoId()`), `pdf` (upload, `mimes:pdf`, max 50 MB), `video` (upload, `mp4,mov,avi,webm`, max 100 MB). Uploads go to `lessons/pdf` or `lessons/video` on the public disk and are served by `lesson-file/{path}`.
- **Ordering**: a new lesson gets `max(sort_order) + 1` in its chapter. The admin UI has no drag-and-drop reordering controller; order is by `sort_order`.
- **Routes**: `admin.courses.chapters.*`, `admin.chapters.*`, `admin.chapters.lessons.*`, `admin.lessons.*`.

### Instructors [`instructors`]

- `Admin\InstructorController`, `Instructor` (translatable `name`, `bio`; `email`, `photo`). Deleting an instructor sets `courses.instructor_id` to NULL.

### Bundles [`bundles`]

- **Purpose**: sell/offer several courses together. Status `draft`, `published` or `archived`.
- **Classes**: `Admin\BundleController` (resource plus `addCourse`, `removeCourse`, `updateCourseOrder`), public `BundleController` (`index`, `show`, `enroll`, `myBundles`), `Bundle`, `BundleEnrollment`, pivot table `bundle_courses` (column `order`).
- **Behaviour**: enrolling in a bundle creates a `bundle_enrollments` row and an `enrollments` row for each published course in the bundle that the learner's audience allows (restricted courses are skipped). `BundleEnrollment::refreshProgress()` recomputes `completed_courses`, `total_courses`, `bundle_completion_percentage` and `completed_at`; it runs on enrolment, on viewing the bundle and whenever a lesson is completed.
- **Routes**: `admin.bundles.*`, `admin.bundles.courses.add|remove|order`; public `bundles.*`.

### Live sessions [`live_sessions`]

- **Purpose**: schedule an online meeting (URL and optional password) for a course and invite enrolled learners.
- **Classes**: `Admin\LiveSessionController` (full resource), `LiveSession`, pivot `live_session_user`, `Mail\LiveSessionInvitation` with view `emails/live-session-invitation`.
- **Behaviour**: invitees are restricted to users already enrolled in the course. Invitation emails are sent synchronously with `Mail::to()->send()`; failures are reported (`report($e)`) and do not abort the save. Upcoming sessions appear on the public course page.

## Learners and access

### Users, learner types and registration [`users`]

- **Classes**: `User`, `Admin\UserController`, `Auth\RegisteredUserController`, `ProfileController`, `App\Support\RwandaLocations`, `App\Support\AgentKyc`, partials `agent-kyc-fields` and `location-fields`.
- **Learner types**: `User::TYPE_AGENT` (default) and `User::TYPE_PARTNER`. `User::learnerCategoryLabel()` renders "Agent" or "Partner (SFH)".
- **Profile data**: first/last name (the `name` column is recomposed on save), email, locale, country/province/district/sector (Rwanda hierarchy validated against `RwandaLocations::tree()`), and for Agents the optional KYC fields. `national_id` is cast `encrypted` and hidden from serialisation.
- **Admin**: list/search, create, view (progress per course), edit, activate/deactivate (`PATCH users/{user}/status`), delete. Guards in `UserController::guardPrivilegedChange` prevent deactivating oneself and removing or deactivating the last active Super Admin.
- **Routes**: `register`, `login`, `profile.*`, `admin.users.*`.

### Partner organisations [`partner_organizations`]

- **Classes**: `Admin\PartnerOrganizationController`, `PartnerOrganization`.
- **Behaviour**: each organisation has a `registration_code` (8 characters from an unambiguous alphabet, `PartnerOrganization::generateCode()`). Partner registration requires the active organisation and its code (`acceptsRegistrationCode()`, case-insensitive, constant-time compare, fails closed when no code is set). `POST admin/partner-organizations/{id}/regenerate-code` invalidates the old code. An organisation with learners or courses cannot be deleted (foreign keys are `restrictOnDelete`); deactivate it instead. The migration seeds one organisation, SFH.

### Agent KYC

- `App\Support\AgentKyc` normalises (strips spaces and hyphens from the ID), validates (`phone`, `national_id`, `date_of_birth`, `gender`) and picks the KYC fields. All are optional and only stored for Agents at registration; Partners' KYC input is ignored.

### Enrollments and access levels

- **Classes**: `Enrollment` (unique per user and course), `Course::isAvailableTo()`, `User::canAccessCourse()`, `Course::scopeAssignedTo/scopeOpenTo`, `Course::audienceWarning()`.
- **Access levels** (`Course::ACCESS_*`): `all` (everyone, also guests), `agents` (Agent learners), `partners` (any Partner), `partner` (Partners of one `partner_organization_id`). Staff always pass.
- **Behaviour**: restricted courses stay visible with a warning and no enrol button; enrolling is blocked server-side (403). Learners who were enrolled before the audience changed keep access (`canAccessCourse`). `Course::enrolledOutsideAudienceCount()` reports such learners.
- A free course (`price <= 0`) is auto-enrolled when the learner opens a lesson URL directly.

### Student progress and import [`course_progress`, `imports`]

- `Admin\CourseProgressController` lists courses and per-student progress; `Admin\StudentImportController` and `App\Services\CourseProgressCsvImporter` import learners and progress from a CSV (see `05-workflows.md`).

## Learning

- **Progress**: `LessonCompletion` rows (unique per user and lesson) written by `CourseController::completeLesson`, normally from `lesson-progress.js` as a JSON request. `User::completedLessonsCountForCourse()` and `Course::totalLessonsCount()` feed progress bars.
- **Lesson order locking**: `Course::lessonStatuses()` returns `completed`, `in_progress`, `locked` or `open` for every lesson. Locking is on by default (`courses.sequential_lessons`) and never applies to staff.
- **Quiz gating**: a published quiz placed under a lesson (`quizzes.lesson_id`) holds back the next lesson until the learner has submitted it once (`Course::pendingQuizAfter`). A quiz is itself locked until the lessons before it are complete (`Course::quizBlocker`, `lessonsRequiredBefore`).
- **Pages**: `courses/show`, `courses/lesson`, `courses/my-courses`, `dashboard` (`LearnerDashboardController`: organisation courses, assigned courses, browse list) with partials under `courses/partials`.

## Assessments

### Quizzes and questions [`quizzes`]

- **Classes**: `Admin\QuizController` (resource without `show`), `Admin\QuestionController`, `Quiz`, `Question`, `QuestionOption`.
- **Quiz fields**: `passing_grade` (0-100), `time_limit_minutes` (1-300, optional), `max_attempts` (1-100, NULL means unlimited), `allow_retake_after_pass`, `is_published`, and optional placement in a chapter (`chapter_id`) or after a lesson (`lesson_id`); neither means course-wide.
- **Question types**: `mcq` (several options, one correct) and `true_false`. Options are stored in `question_options`. Blank options are dropped and the correct option must not be blank.

### Attempts and results [`quiz_results`]

- **Classes**: public `QuizController` (`show`, `start`, `take`, `autosave`, `submit`, `result`), `QuizAttempt`, `QuizAttemptAnswer`, `Admin\QuizResultsController` (list, detail, CSV export), console command `CloseExpiredQuizAttempts`.
- **Rules**: maximum attempts, retake after pass, time limit with a 20-second submission grace (`QuizAttempt::GRACE_SECONDS`), draft answers saved every 15 seconds, automatic closing of expired attempts. Details in `05-workflows.md`.

## Administration

### Roles and permissions [`roles`]

- `Admin\RoleController`, `Role`, `RolePermission`, `UserPermission`, `AdminAccess`, `config/admin-modules.php`, partial `admin/partials/permission-matrix`. The system role `super-admin` (`is_system = true`) cannot be deleted and is always re-synced with every permission (`Role::ensureSystemRoles()`). Only a Super Admin may edit it. See `05-workflows.md` and `08-security-and-permissions.md`.

### Reports and dashboard [`reports`, `dashboard`]

- `Admin\DashboardController` (single-action; KPIs, trends, recent activity) and `Admin\ReportsController` (course and quiz performance over a date range). Both are read-only aggregations computed at request time with Eloquent; there is no caching layer. Charts use Chart.js from a CDN.

### Settings and contact messages [`settings`]

- `Admin\SettingsController` stores the platform logo (`Setting` key `site_logo`, image jpeg/png/jpg/gif/webp/svg, max 2 MB, saved under `site/`).
- `Admin\ContactMessageController` lists, shows and deletes messages submitted from `/contact` (`PageController::contactSubmit`, stored in `contact_messages`). A notification email (`Mail\ContactMessageReceived`) is sent to `MAIL_FROM_ADDRESS`; mail errors are logged and do not fail the submission. Contact messages are guarded by the `settings` permission module (`route_modules` maps `admin.contact-messages` to `settings`; `destroy` and `destroy-all` map to the `delete` action).

### Partner logos [`partners`]

- `Admin\PartnerController` and `Partner`: logos shown on the home page, ordered by `sort_order`, stored under `partners/`.

### Documentation [`documentation`]

- **Purpose**: renders the Markdown in `docs/` inside the admin panel (group "Help") and offers downloads.
- **Classes**: `Admin\DocumentationController` (`index`, `show`, `download`, `image`), `App\Support\Documentation` (books `user-guide` and `system`, chapter discovery by file name, CommonMark rendering with raw HTML stripped and unsafe links disallowed, combined Markdown and standalone HTML export), config module `documentation` (action `view` only).
- **Routes**: `admin.documentation.index`, `.show`, `.image`, `.download` (formats `html`, `md`).
- Chapters are read from disk on each request; nothing is stored in the database. See `09-testing-and-maintenance.md`.

## Localization

- **Classes and files**: `SetLocale`, `LocaleController`, `config/localization.php`, `lang/rw.json`, `lang/en/*.php`, `lang/rw/*.php`, `lang/GLOSSARY.md`, `Models\Concerns\HasTranslations`, view component `language-switcher`, partial `admin/partials/translation-fields`.
- **Two mechanisms**: (1) interface text wrapped in `__()` is translated through `lang/rw.json`, with English as key and fallback; (2) database content (course titles, lesson text, quiz questions and so on) is translated through the `translations` JSON column and `$model->tr('field')`, which returns the Kinyarwanda text if present and non-blank, otherwise the English original. Admin screens always edit and display the English original, with extra `tr[rw][field]` inputs for Kinyarwanda.
- **Translatable models**: `Category`, `Course`, `Chapter`, `Lesson`, `Quiz`, `Question`, `QuestionOption`, `Bundle`, `Instructor`, `LiveSession`.
- Emails to a user are rendered in `User::preferredLocale()` (the `HasLocalePreference` contract).
