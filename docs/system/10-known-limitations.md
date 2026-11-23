# Known Limitations

Each item below was verified in the code at the time of writing. They are facts about the current implementation, not a roadmap.

## Features that are absent

| Area | Limitation |
|---|---|
| Payments | There is no payment processing, checkout or invoicing. `courses.price` is informational; enrolment (`CourseController@enroll`) never checks it. Bundles have no price. |
| Reviews | The `reviews` table, `Review` model and `Course::reviews()` exist, but no controller, route or view creates or shows reviews. |
| Certificates | No certificate generation or completion documents. |
| Bundle expiry | `bundle_enrollments.expires_at` exists but is never set or checked. |
| Reordering | `sort_order` for chapters, lessons, questions and quizzes is assigned once at creation (`max + 1`). The controllers and routes offer no way to reorder them afterwards (only bundle course order has a route, `admin.bundles.courses.order`). Because lesson order drives sequential unlocking, a wrongly ordered curriculum has to be recreated. |
| Question types | Only multiple choice (`mcq`) and `true_false`; one correct option and one answer per question; no free text, partial credit or question banks. |
| Tracking video | Completion by "90 percent watched" depends on browser JavaScript; there is no server-side verification of viewing time. |
| Notifications | No email on enrolment, quiz completion or course completion. Live-session invitation emails are sent only when the session is saved. Imported users are not emailed. |
| REST API | No API routes or token authentication; the app is server-rendered only. |
| Queued mail | No queue is used; see the next section. |
| Search | The public catalogue has category filtering and pagination only; admin lists have simple `LIKE` search. |
| Languages | Only English and Kinyarwanda are configured; the tests check Kinyarwanda only. |

## Things that require operator setup

| Item | Detail |
|---|---|
| Cron | `php artisan schedule:run` must run every minute for `quizzes:close-expired`. Without it abandoned timed attempts stay open (they are closed lazily when the learner returns). |
| Real email | The default `.env.example` uses `MAIL_MAILER=log`, which delivers nothing. Password reset, contact notifications and live-session invitations need SMTP (or another real mailer) configured. For a custom sending domain, SPF and DKIM DNS records on that domain (and a sender address the SMTP provider allows, since for example Gmail always sends as the authenticated account) determine deliverability; the application cannot set these. |
| Assets | `public/build` is not committed. Every deployment must run `npm ci && npm run build`. |
| Uploads | Large lesson videos (100 MB) and PDFs (50 MB) need matching PHP and web-server limits. Files live on the server disk (`storage/app/public`); there is no CDN, virus scanning or transcoding, and no automatic backup. |
| Initial admin | The Super Admin is created by seeding or `admin:ensure`; there is no web installer. |

## Behaviour to be aware of

- **Email verification is not enforced.** `User` does not implement `MustVerifyEmail`, so the `verified` middleware passes everyone, no verification email is sent at registration, and the verification routes call methods the model lacks.
- **Lesson and image files are public.** `GET lesson-file/{path}` and `GET course-image/{path}` do not check login, enrolment or course audience; protection relies on random file names.
- **GET logout.** `/logout` accepts GET as well as POST, so a cross-site link can sign a user out.
- **No throttle on the contact form** (`POST /contact`) or on enrolment requests; the contact form writes to the database and sends an email on every submission.
- **`route:cache` cannot be used.** `routes/web.php` has a closure route (the `/admin` redirect), which Laravel cannot cache. `config:cache`, `view:cache` and `event:cache` work.
- **Admin routes without a `route_modules` prefix are not permission-checked.** Any active staff member passes `EnsureUserIsAdmin` for them. All current admin routes are mapped; new ones must be added to `config/admin-modules.php`.
- **Course `status` default.** The database default for `courses.status` is `published` (from a backfill migration) while the application creates courses as `draft`. Rows inserted outside the app (SQL, imports by other tools) become published immediately. The demo `CategoryCourseSeeder` courses are therefore published and have random prices and student counts.
- **Seeder side effects.** `DatabaseSeeder` creates a demo learner with a fixed weak password and resets the admin password from `ADMIN_PASSWORD` every time it runs. `admin:ensure` prints the password it sets. Both are suitable for setup only.
- **Plain-text secrets in the database.** `live_sessions.meeting_password` is not encrypted. `users.national_id` is encrypted but displayed in clear to staff with the `users` view permission.
- **Denormalised counters.** `courses.students_count` and `categories.courses_count` are stored values. `students_count` is incremented on enrolment and recomputed by the CSV importer; enrolment deletions (admin user deletion cascades) do not decrement it, and `categories.courses_count` is only set at creation (public pages count published courses live, but the admin category list statistics read the stored column).
- **Progress after content changes.** Completion is per lesson. Adding lessons to a finished course lowers its percentage; with lesson locking, a learner who finished earlier keeps completed lessons open but new lessons are locked until the previous one is completed.
- **Audience changes do not unenrol.** Learners enrolled before a course's audience was narrowed keep access (`User::canAccessCourse`); `Course::enrolledOutsideAudienceCount()` only reports them.
- **Quiz gating uses one submission.** A quiz placed under a lesson unlocks the next lesson as soon as it has been submitted once, whether or not the learner passed.
- **CSV progress import is approximate.** `% Completed` is converted into "the first N lessons in order"; per-lesson detail from the source file is not mapped to individual lessons.
- **Reports are computed per request.** The dashboard and reports controllers aggregate with Eloquent on every page load and no caching; they may become slow on very large datasets.
- **Third-party assets at runtime.** Fonts (Bunny Fonts), Chart.js (jsDelivr) and YouTube embeds require the browser to reach those hosts; there is no offline fallback.
- **Admin help text.** Some admin strings (for example the True/False option labels created by `QuestionController`) are stored in English in the database.
- **Tests.** `tests/Unit` contains only a placeholder; coverage is feature-level. There is no automated browser (JavaScript) testing, so `lesson-progress.js` and the quiz timer script are not covered.
- **Documentation module.** The admin Documentation pages read Markdown files from `docs/` on disk; they are part of the deployment, so changes require a deploy, and the Markdown pipeline strips raw HTML and links with unsafe schemes.
