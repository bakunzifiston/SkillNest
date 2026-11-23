# Database

The schema is defined by the files in `database/migrations/` and reflects every later `alter` migration. The default connection in `.env.example` is MySQL; `config/database.php` defaults to SQLite and the test suite uses in-memory SQLite. Timestamps (`created_at`, `updated_at`, both nullable timestamps) are present on every table unless stated otherwise. `FK` means a foreign key to the named table's `id`. Notation: `string` is `VARCHAR(255)` unless a length is given.

## Entity overview

```text
partner_organizations 1---* users *---1 roles 1---* role_permissions
        |                     |   \
        1                     |    *---* user_permissions
        |                     |
        *                     +--* enrollments *---1 courses *---1 categories
     courses -----------------+   (unique user+course)  |  \---1 instructors (nullable)
        |                     |                         |
        |                     +--* lesson_completions   +--* chapters 1---* lessons
        |                     |        (unique)                              |
        |                     +--* bundle_enrollments *---1 bundles          |
        |                     |                            \--* bundle_courses *--1 courses
        |                     +--* reviews (unique user+course)
        |                     +--* live_session_user *---1 live_sessions *---1 courses
        |                     \--* quiz_attempts *---1 quizzes 1---* questions 1---* question_options
        |                              |                 |  (course_id, chapter_id?, lesson_id?)
        |                              \--* quiz_attempt_answers (-> questions, -> question_options)
        \--* quizzes

Standalone: settings, partners (logos), contact_messages
Framework : sessions, password_reset_tokens, cache, cache_locks, jobs, job_batches, failed_jobs
```

## Users and access control

### `users`

Model `User`. Columns after all migrations:

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | bigint unsigned PK | no | auto | |
| `name` | string | no | | Recomposed from first and last name by a `saving` hook; legacy single names are split into first/last. |
| `first_name`, `last_name` | string | yes | NULL | Backfilled from `name` by migration. |
| `email` | string, unique | no | | |
| `locale` | string(5) | yes | NULL | `en` or `rw`; NULL means no choice yet. |
| `phone` | string(30) | yes | NULL | Agent KYC. |
| `national_id` | text | yes | NULL | Agent KYC; stored encrypted (model cast `encrypted`); hidden from serialisation. |
| `date_of_birth` | date | yes | NULL | Agent KYC. |
| `gender` | string(20) | yes | NULL | `male`, `female`, `other`, `prefer_not_to_say`. |
| `country`, `province`, `district`, `sector` | string(100) | yes | NULL | Province, district and sector are only kept when country is Rwanda. |
| `learner_type` | string(20) | no | `agent` | `agent` or `partner`. |
| `partner_organization_id` | FK `partner_organizations` | yes | NULL | `restrictOnDelete`. |
| `is_admin` | boolean | no | false | Legacy/flag for Super Admin. |
| `role_id` | FK `roles` | yes | NULL | `nullOnDelete`. |
| `is_active` | boolean | no | true | Inactive staff cannot use the admin panel; inactive users cannot log in. |
| `email_verified_at` | timestamp | yes | NULL | |
| `password` | string | no | | Hashed (cast `hashed`). |
| `remember_token` | string(100) | yes | NULL | |
| `last_login_at` | timestamp | yes | NULL | Set at login. |

The `occupation` column was added and then dropped by `2026_11_15_130000_drop_occupation_from_users_table`. Indexes: primary key, unique `email`, foreign key indexes.

### `roles`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `name` | string | no | | |
| `slug` | string, unique | no | | Generated from the name if blank. `super-admin` is the system role. |
| `description` | string | yes | NULL | |
| `is_system` | boolean | no | false | System roles cannot be deleted. |

The migration inserts the `super-admin` role with a permission row for every action of every module defined in `config/admin-modules.php` at that moment, and attaches all `is_admin` users to it.

### `role_permissions`

| Column | Type | Null | Notes |
|---|---|---|---|
| `id` | PK | no | |
| `role_id` | FK `roles` | no | `cascadeOnDelete` |
| `module` | string | no | key from `config/admin-modules.php` |
| `action` | string | no | `view`, `create`, `edit`, `delete` |

Unique: (`role_id`, `module`, `action`).

### `user_permissions`

Same shape as `role_permissions` with `user_id` FK `users` (`cascadeOnDelete`). Unique (`user_id`, `module`, `action`). Per-user rows override the role's permissions entirely when any exist.

### `partner_organizations`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `name` | string | no | | |
| `slug` | string, unique | no | | |
| `registration_code` | string(32) | yes | NULL | Partner sign-up code; backfilled with a random 8-character value for existing rows. |
| `is_active` | boolean | no | true | |

The first row, SFH, is inserted by the migration.

## Catalog

### `categories`

| Column | Type | Null | Default |
|---|---|---|---|
| `id` | PK | no | |
| `name` | string | no | |
| `slug` | string, unique | no | |
| `icon` | string | yes | NULL |
| `courses_count` | integer | no | 0 |
| `translations` | json | yes | NULL |

### `instructors`

| Column | Type | Null |
|---|---|---|
| `id` | PK | no |
| `name` | string | no |
| `email` | string | yes |
| `bio` | text | yes |
| `photo` | string | yes |
| `translations` | json | yes |

### `courses`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `category_id` | FK `categories` | no | | `cascadeOnDelete` |
| `instructor_id` | FK `instructors` | yes | NULL | `nullOnDelete`, indexed |
| `title` | string | no | | |
| `slug` | string, unique | no | | also indexed by a separate non-unique index |
| `description` | text | yes | NULL | |
| `image` | string | yes | NULL | path on public disk |
| `price` | decimal(10,2) | no | 0 | informational only |
| `duration` | string | yes | NULL | free text |
| `level` | string | no | `beginner` | |
| `status` | string(20) | no | `published` | database default; the model's `creating` hook sets `draft` when blank, and the admin form defaults to `draft` |
| `access_level` | string(20) | no | `all` | `all`, `agents`, `partners`, `partner` |
| `sequential_lessons` | boolean | no | true | |
| `partner_organization_id` | FK `partner_organizations` | yes | NULL | `restrictOnDelete`; used with `access_level = partner` |
| `students_count` | integer | no | 0 | denormalised counter |
| `translations` | json | yes | NULL | |

### `chapters`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `course_id` | FK `courses` | no | | `cascadeOnDelete` |
| `title` | string | no | | |
| `sort_order` | unsigned integer | no | 0 | indexed |
| `translations` | json | yes | NULL | |

### `lessons`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `chapter_id` | FK `chapters` | no | | `cascadeOnDelete` |
| `title` | string | no | | |
| `type` | string | no | | `text`, `youtube`, `pdf`, `video`; indexed |
| `content` | text | yes | NULL | text lessons |
| `source_url` | string(500) | yes | NULL | YouTube URL |
| `file_path` | string(500) | yes | NULL | path on public disk |
| `sort_order` | unsigned integer | no | 0 | indexed |
| `translations` | json | yes | NULL | |

### `bundles`

| Column | Type | Null | Default |
|---|---|---|---|
| `id` | PK | no | |
| `title` | string | no | |
| `slug` | string, unique | no | |
| `description` | text | yes | NULL |
| `thumbnail` | string | yes | NULL |
| `status` | string | no | `draft` (`draft`, `published`, `archived`) |
| `translations` | json | yes | NULL |

### `bundle_courses`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `bundle_id` | FK `bundles` | no | | `cascadeOnDelete` |
| `course_id` | FK `courses` | no | | `cascadeOnDelete`, indexed |
| `order` | unsigned smallint | no | 0 | indexed |

Unique (`bundle_id`, `course_id`).

### `live_sessions`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `course_id` | FK `courses` | no | | `cascadeOnDelete`, indexed |
| `title` | string | no | | |
| `description` | text | yes | NULL | |
| `scheduled_at` | datetime | no | | indexed |
| `duration_minutes` | unsigned integer | no | 60 | |
| `meeting_url` | string(500) | no | | |
| `meeting_password` | string | yes | NULL | stored in clear text |
| `translations` | json | yes | NULL | |

### `live_session_user`

Pivot of invitees: `id`, `live_session_id` (FK, cascade, indexed), `user_id` (FK, cascade, indexed), timestamps. Unique (`live_session_id`, `user_id`).

## Learner activity

### `enrollments`

| Column | Type | Null | Notes |
|---|---|---|---|
| `id` | PK | no | |
| `user_id` | FK `users` | no | `cascadeOnDelete` |
| `course_id` | FK `courses` | no | `cascadeOnDelete`, indexed |

Unique (`user_id`, `course_id`); extra index on `created_at`.

### `lesson_completions`

| Column | Type | Null | Notes |
|---|---|---|---|
| `id` | PK | no | |
| `user_id` | FK `users` | no | `cascadeOnDelete` |
| `lesson_id` | FK `lessons` | no | `cascadeOnDelete`, indexed |
| `completed_at` | timestamp | yes | indexed |

Unique (`user_id`, `lesson_id`).

### `bundle_enrollments`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `user_id` | FK `users` | no | | `cascadeOnDelete` |
| `bundle_id` | FK `bundles` | no | | `cascadeOnDelete`, indexed |
| `enrolled_at` | timestamp | no | | indexed |
| `expires_at` | timestamp | yes | NULL | not used by application code |
| `completed_courses` | unsigned smallint | no | 0 | |
| `total_courses` | unsigned smallint | no | 0 | |
| `bundle_completion_percentage` | decimal(5,2) | no | 0 | |
| `completed_at` | timestamp | yes | NULL | indexed |

Unique (`user_id`, `bundle_id`).

### `reviews`

`id`, `user_id` (FK users, cascade), `course_id` (FK courses, cascade, indexed), `rating` unsigned tinyint (1-5), `body` text nullable, timestamps (`created_at` indexed). Unique (`user_id`, `course_id`). The `Review` model exists and `Course::reviews()` is defined, but no controller creates reviews.

## Assessments

### `quizzes`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `course_id` | FK `courses` | no | | `cascadeOnDelete` |
| `chapter_id` | FK `chapters` | yes | NULL | `nullOnDelete` |
| `lesson_id` | FK `lessons` | yes | NULL | `nullOnDelete` |
| `title` | string | no | | |
| `description` | text | yes | NULL | |
| `passing_grade` | unsigned tinyint | no | 70 | percentage 0-100 |
| `time_limit_minutes` | unsigned integer | yes | NULL | |
| `max_attempts` | unsigned smallint | yes | NULL | NULL = unlimited |
| `allow_retake_after_pass` | boolean | no | true | |
| `sort_order` | unsigned integer | no | 0 | |
| `is_published` | boolean | no | true | |
| `translations` | json | yes | NULL | |

### `questions`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `quiz_id` | FK `quizzes` | no | | `cascadeOnDelete` |
| `type` | string(20) | no | | `mcq` or `true_false`; indexed |
| `question_text` | text | no | | |
| `points` | unsigned integer | no | 1 | |
| `sort_order` | unsigned integer | no | 0 | indexed |
| `translations` | json | yes | NULL | |

### `question_options`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `question_id` | FK `questions` | no | | `cascadeOnDelete` |
| `option_text` | string | no | | |
| `is_correct` | boolean | no | false | |
| `sort_order` | unsigned integer | no | 0 | indexed |
| `translations` | json | yes | NULL | |

### `quiz_attempts`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `user_id` | FK `users` | no | | `cascadeOnDelete` |
| `quiz_id` | FK `quizzes` | no | | `cascadeOnDelete`, indexed |
| `started_at` | timestamp | no | | |
| `submitted_at` | timestamp | yes | NULL | NULL = unfinished; indexed |
| `score` | unsigned integer | no | 0 | points earned |
| `total_points` | unsigned integer | no | 0 | |
| `percentage` | decimal(5,2) | yes | NULL | |
| `passed` | boolean | no | false | indexed |
| `timed_out` | boolean | no | false | closed because the time limit ran out |
| `draft_answers` | json | yes | NULL | `{"<question id>": <option id>}` saved by autosave; cleared on submit |

### `quiz_attempt_answers`

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| `id` | PK | no | | |
| `quiz_attempt_id` | FK `quiz_attempts` | no | | `cascadeOnDelete`, indexed |
| `question_id` | FK `questions` | no | | `cascadeOnDelete`, indexed |
| `question_option_id` | FK `question_options` | yes | NULL | `nullOnDelete`; NULL = unanswered |
| `is_correct` | boolean | no | false | |
| `points_earned` | unsigned integer | no | 0 | |

## Site content

| Table | Columns | Notes |
|---|---|---|
| `settings` | `key` string PK (not auto-increment), `value` text nullable, timestamps | Model `Setting`; read through a one-hour cache. Only `site_logo` is used. |
| `partners` | `id`, `name` string nullable, `logo` string, `sort_order` unsigned smallint (0), timestamps | Partner logos on the home page (model `Partner`). |
| `contact_messages` | `id`, `name` string, `email` string (indexed), `subject` string nullable, `message` text, `read_at` timestamp nullable, timestamps (`created_at` indexed) | Public contact form submissions. |

## Framework tables

| Table | Purpose and columns |
|---|---|
| `sessions` | Used when `SESSION_DRIVER=database`. `id` string PK, `user_id` bigint nullable (indexed), `ip_address` string(45) nullable, `user_agent` text nullable, `payload` longText, `last_activity` integer (indexed). |
| `password_reset_tokens` | `email` string PK, `token` string, `created_at` timestamp nullable. |
| `cache`, `cache_locks` | Used when `CACHE_STORE=database`. `key` string PK, `value` mediumText (`owner` string for locks), `expiration` integer (indexed). |
| `jobs` | Queue table for `QUEUE_CONNECTION=database`. `id`, `queue` string (indexed), `payload` longText, `attempts` unsigned tinyint, `reserved_at` unsigned int nullable, `available_at` unsigned int, `created_at` unsigned int. |
| `job_batches` | Laravel batch bookkeeping (`id` string PK, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at`). |
| `failed_jobs` | `id`, `uuid` unique, `connection`, `queue`, `payload`, `exception`, `failed_at` (default current). |
| `migrations` | Laravel migration ledger (created by the migrator). |

## Relationship summary (from model methods)

| Model | Relations |
|---|---|
| `User` | belongsTo `PartnerOrganization`, `Role`; hasMany `UserPermission` (`permissions`), `Enrollment`, `LessonCompletion`, `BundleEnrollment`, `QuizAttempt` |
| `Role` | hasMany `RolePermission` (`permissions`), `User` |
| `RolePermission` / `UserPermission` | belongsTo `Role` / `User` |
| `PartnerOrganization` | hasMany `User`, `Course` |
| `Category` | hasMany `Course`; `publishedCourses` (hasMany, published scope) |
| `Instructor` | hasMany `Course` |
| `Course` | belongsTo `Category`, `Instructor`, `PartnerOrganization`; hasMany `Chapter` (ordered), `Enrollment`, `Review`, `Quiz` (ordered), `LiveSession` (ordered); belongsToMany `Bundle` via `bundle_courses` with pivot `order` |
| `Chapter` | belongsTo `Course`; hasMany `Lesson` (ordered) |
| `Lesson` | belongsTo `Chapter`; hasMany `LessonCompletion` |
| `Enrollment` | belongsTo `User`, `Course` |
| `LessonCompletion` | belongsTo `User`, `Lesson` |
| `Bundle` | belongsToMany `Course` (ordered by pivot `order`); hasMany `BundleEnrollment` |
| `BundleEnrollment` | belongsTo `User`, `Bundle` |
| `LiveSession` | belongsTo `Course`; belongsToMany `User` as `invitedAttendees` via `live_session_user` |
| `Quiz` | belongsTo `Course`, `Chapter`, `Lesson`; hasMany `Question` (ordered), `QuizAttempt` |
| `Question` | belongsTo `Quiz`; hasMany `QuestionOption` (ordered) |
| `QuestionOption` | belongsTo `Question` |
| `QuizAttempt` | belongsTo `User`, `Quiz`; hasMany `QuizAttemptAnswer` (`answers`) |
| `QuizAttemptAnswer` | belongsTo `QuizAttempt`, `Question`, `QuestionOption` |
| `Review` | belongsTo `User`, `Course` |
| `Setting`, `Partner`, `ContactMessage` | no relations |

## Delete behaviour worth knowing

- Deleting a course cascades to chapters, lessons, enrollments, completions, quizzes (and their questions, options, attempts, answers), live sessions and bundle links.
- Deleting a chapter or lesson sets `quizzes.chapter_id` / `lesson_id` to NULL, so a quiz then becomes course-wide (or chapter-wide).
- Deleting a user cascades to their enrolments, completions, attempts, bundle enrolments, reviews and invitations.
- Partner organisations that still have users or courses cannot be deleted (`restrictOnDelete`).
- The hard-coded rows inserted by migrations (the `super-admin` role, the SFH organisation) exist only if the migrations ran on that database.
