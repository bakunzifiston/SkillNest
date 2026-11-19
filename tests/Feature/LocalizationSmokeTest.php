<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\LiveSession;
use App\Models\Partner;
use App\Models\PartnerOrganization;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Opens every page of the platform in English and in Kinyarwanda, as the right kind of user, with
 * real data, and checks that nothing breaks or leaks (raw keys, unfilled :placeholders).
 */
class LocalizationSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, int|string> */
    private array $ids = [];

    private User $admin;

    private User $student;

    private function seedWorld(): void
    {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->student = User::factory()->create();

        $category = Category::create(['name' => 'Digital Marketing', 'slug' => 'digital-marketing']);
        $instructor = Instructor::create(['name' => 'Jane Teacher', 'email' => 'jane@example.com', 'bio' => 'Teacher bio']);
        $course = Course::create([
            'category_id' => $category->id, 'instructor_id' => $instructor->id, 'title' => 'Marketing 101', 'slug' => 'marketing-101',
            'description' => 'Learn marketing', 'price' => 10, 'level' => 'beginner', 'duration' => '4 hours', 'status' => Course::STATUS_PUBLISHED,
        ]);
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Basics', 'sort_order' => 1]);
        $lesson = Lesson::create(['chapter_id' => $chapter->id, 'title' => 'Intro', 'type' => 'text', 'content' => 'Welcome', 'sort_order' => 1]);
        $quiz = Quiz::create(['course_id' => $course->id, 'chapter_id' => $chapter->id, 'title' => 'First quiz', 'passing_grade' => 70, 'is_published' => true, 'time_limit_minutes' => 10]);
        $question = Question::create(['quiz_id' => $quiz->id, 'type' => 'mcq', 'question_text' => 'Pick one', 'points' => 1, 'sort_order' => 1]);
        $option = QuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false, 'sort_order' => 2]);

        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $course->id]);
        $bundle = Bundle::create(['title' => 'Starter path', 'slug' => 'starter-path', 'description' => 'Bundle', 'status' => Bundle::STATUS_PUBLISHED]);
        $bundle->courses()->attach([$course->id => ['order' => 1]]);
        $session = LiveSession::create([
            'course_id' => $course->id, 'title' => 'Live Q&A', 'description' => 'Ask anything',
            'scheduled_at' => now()->addDays(3), 'duration_minutes' => 60, 'meeting_url' => 'https://example.com/meet',
        ]);
        $inProgress = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now(), 'total_points' => 1]);
        $done = QuizAttempt::create([
            'user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subHour(), 'submitted_at' => now(),
            'score' => 1, 'total_points' => 1, 'percentage' => 100, 'passed' => true,
        ]);
        $message = ContactMessage::create(['name' => 'Visitor', 'email' => 'v@example.com', 'subject' => 'Hello', 'message' => 'Hi there']);
        PartnerOrganization::where('slug', 'sfh')->firstOrFail();
        Partner::create(['name' => 'P', 'logo' => 'partners/x.png']);
        $role = Role::ensureSystemRoles();

        $this->ids = [
            'course' => $course->slug, 'bundle' => $bundle->slug, 'lesson' => $lesson->id, 'quiz' => $quiz->id,
            'chapter' => $chapter->id, 'category' => $category->id, 'instructor' => $instructor->id, 'question' => $question->id,
            'live_session' => $session->id, 'contact_message' => $message->id, 'quizAttempt_open' => $inProgress->id,
            'quizAttempt_done' => $done->id, 'user' => $this->student->id, 'role' => $role->id ?? Role::first()->id,
            'partner' => Partner::first()->id, 'partner_organization' => PartnerOrganization::first()->id,
            'course_id' => $course->id, 'option' => $option->id,
        ];
    }

    /** @return array<string, array{0: string, 1: array, 2: string}> name => [route, params, actor] */
    private function pages(): array
    {
        $i = $this->ids;

        return [
            // public
            'home' => ['home', [], 'guest'], 'about' => ['about', [], 'guest'], 'contact' => ['contact', [], 'guest'],
            'courses' => ['courses.index', [], 'guest'], 'course' => ['courses.show', ['course' => $i['course']], 'guest'],
            'bundles' => ['bundles.index', [], 'guest'], 'bundle' => ['bundles.show', ['bundle' => $i['bundle']], 'guest'],
            'login' => ['login', [], 'guest'], 'register' => ['register', [], 'guest'], 'forgot' => ['password.request', [], 'guest'],
            // learner
            'dashboard' => ['dashboard', [], 'student'], 'my-courses' => ['courses.my-courses', [], 'student'],
            'my-bundles' => ['bundles.my-bundles', [], 'student'], 'profile' => ['profile.edit', [], 'student'],
            'course (enrolled)' => ['courses.show', ['course' => $i['course']], 'student'],
            'lesson' => ['courses.lessons.show', ['course' => $i['course'], 'lesson' => $i['lesson']], 'student'],
            'quiz intro' => ['courses.quizzes.show', ['course' => $i['course'], 'quiz' => $i['quiz']], 'student'],
            'quiz take' => ['quizzes.take', ['quizAttempt' => $i['quizAttempt_open']], 'student'],
            'quiz result' => ['quizzes.result', ['quizAttempt' => $i['quizAttempt_done']], 'student'],
            // admin
            // (admin.reports.index is left out: it uses MySQL-only SQL such as DATE_FORMAT, which the in-memory SQLite test database lacks.)
            'a-dashboard' => ['admin.dashboard', [], 'admin'],
            'a-settings' => ['admin.settings.edit', [], 'admin'], 'a-imports' => ['admin.imports.students.create', [], 'admin'],
            'a-courses' => ['admin.courses.index', [], 'admin'], 'a-course-create' => ['admin.courses.create', [], 'admin'],
            'a-course-edit' => ['admin.courses.edit', ['course' => $i['course']], 'admin'],
            'a-chapters' => ['admin.courses.chapters.index', ['course' => $i['course']], 'admin'],
            'a-chapter-create' => ['admin.courses.chapters.create', ['course' => $i['course']], 'admin'],
            'a-chapter-edit' => ['admin.chapters.edit', ['chapter' => $i['chapter']], 'admin'],
            'a-lessons' => ['admin.chapters.lessons.index', ['chapter' => $i['chapter']], 'admin'],
            'a-lesson-create' => ['admin.chapters.lessons.create', ['chapter' => $i['chapter']], 'admin'],
            'a-lesson-edit' => ['admin.lessons.edit', ['lesson' => $i['lesson']], 'admin'],
            'a-categories' => ['admin.categories.index', [], 'admin'], 'a-category-create' => ['admin.categories.create', [], 'admin'],
            'a-category-edit' => ['admin.categories.edit', ['category' => $i['category']], 'admin'],
            'a-instructors' => ['admin.instructors.index', [], 'admin'], 'a-instructor-create' => ['admin.instructors.create', [], 'admin'],
            'a-instructor-edit' => ['admin.instructors.edit', ['instructor' => $i['instructor']], 'admin'],
            'a-bundles' => ['admin.bundles.index', [], 'admin'], 'a-bundle-create' => ['admin.bundles.create', [], 'admin'],
            'a-bundle-edit' => ['admin.bundles.edit', ['bundle' => $i['bundle']], 'admin'],
            'a-quizzes' => ['admin.quizzes.index', [], 'admin'], 'a-quiz-create' => ['admin.quizzes.create', [], 'admin'],
            'a-quiz-edit' => ['admin.quizzes.edit', ['quiz' => $i['quiz']], 'admin'],
            'a-questions' => ['admin.quizzes.questions.index', ['quiz' => $i['quiz']], 'admin'],
            'a-question-create' => ['admin.quizzes.questions.create', ['quiz' => $i['quiz']], 'admin'],
            'a-question-edit' => ['admin.questions.edit', ['question' => $i['question']], 'admin'],
            'a-quiz-results' => ['admin.quiz-results.index', [], 'admin'],
            'a-quiz-result' => ['admin.quiz-results.show', ['quizAttempt' => $i['quizAttempt_done']], 'admin'],
            'a-live' => ['admin.live-sessions.index', [], 'admin'], 'a-live-create' => ['admin.live-sessions.create', [], 'admin'],
            'a-live-show' => ['admin.live-sessions.show', ['live_session' => $i['live_session']], 'admin'],
            'a-live-edit' => ['admin.live-sessions.edit', ['live_session' => $i['live_session']], 'admin'],
            'a-users' => ['admin.users.index', [], 'admin'], 'a-user-create' => ['admin.users.create', [], 'admin'],
            'a-user-show' => ['admin.users.show', ['user' => $i['user']], 'admin'],
            'a-user-edit' => ['admin.users.edit', ['user' => $i['user']], 'admin'],
            'a-roles' => ['admin.roles.index', [], 'admin'], 'a-role-create' => ['admin.roles.create', [], 'admin'],
            'a-orgs' => ['admin.partner-organizations.index', [], 'admin'], 'a-org-create' => ['admin.partner-organizations.create', [], 'admin'],
            'a-org-edit' => ['admin.partner-organizations.edit', ['partner_organization' => $i['partner_organization']], 'admin'],
            'a-partners' => ['admin.partners.index', [], 'admin'], 'a-partner-create' => ['admin.partners.create', [], 'admin'],
            'a-messages' => ['admin.contact-messages.index', [], 'admin'],
            'a-message' => ['admin.contact-messages.show', ['contact_message' => $i['contact_message']], 'admin'],
            'a-progress' => ['admin.course-progress.index', [], 'admin'],
            'a-progress-course' => ['admin.course-progress.show', ['course' => $i['course']], 'admin'],
        ];
    }

    private function visit(string $locale, string $name, array $page): string
    {
        [$route, $params, $actor] = $page;
        $this->student->forceFill(['locale' => $locale])->save();
        $this->admin->forceFill(['locale' => $locale])->save();

        $request = match ($actor) {
            'admin' => $this->actingAs($this->admin),
            'student' => $this->actingAs($this->student),
            default => $this->withSession([config('localization.session_key') => $locale]),
        };
        $response = $request->get(route($route, $params));
        $this->assertSame(200, $response->getStatusCode(), "{$name} [{$locale}] ({$route}) did not render");
        $response->assertHeader('Content-Language', $locale);

        return $response->getContent();
    }

    public function test_every_page_renders_in_english_and_kinyarwanda(): void
    {
        $this->seedWorld();

        foreach ($this->pages() as $name => $page) {
            foreach (['en', 'rw'] as $locale) {
                auth()->logout();
                $html = $this->visit($locale, $name, $page);
                $this->assertStringContainsString('<html lang="'.$locale.'"', $html, "{$name} [{$locale}]: wrong lang attribute");
            }
        }
    }

    public function test_translated_pages_have_no_leaked_keys_or_unfilled_placeholders(): void
    {
        $this->seedWorld();

        $names = [];
        foreach (array_keys(json_decode(file_get_contents(lang_path('rw.json')), true)) as $key) {
            if (preg_match_all('/(?<![A-Za-z0-9])(:[a-z_]+)\b/', $key, $m)) {
                array_push($names, ...$m[1]);
            }
        }
        $placeholders = implode('|', array_map('preg_quote', array_unique($names)));

        foreach ($this->pages() as $name => $page) {
            foreach (['en', 'rw'] as $locale) {
                auth()->logout();
                $html = $this->visit($locale, $name, $page);
                // Ignore scripts/styles (Alpine shorthand like :class is not a text placeholder).
                $text = preg_replace('#<(script|style)\b.*?</\1>#si', ' ', $html);
                $text = preg_replace('/<[^>]+>/', ' ', $text);

                $this->assertDoesNotMatchRegularExpression('/(?<![A-Za-z0-9\/])('.$placeholders.')\b/', $text, "{$name} [{$locale}]: an unfilled placeholder is visible");
                $this->assertDoesNotMatchRegularExpression('/\b(validation|passwords|auth|pagination)\.[a-z_]+\b/', $text, "{$name} [{$locale}]: a raw translation key is visible");
            }
        }
    }

    public function test_kinyarwanda_pages_actually_show_kinyarwanda_text(): void
    {
        $this->seedWorld();
        $rw = json_decode(file_get_contents(lang_path('rw.json')), true);

        $expect = [
            'home' => ['guest', 'home', [], ['Browse by category', 'How KoraLink Academy Works']],
            'login' => ['guest', 'login', [], ['Log in']],
            'dashboard' => ['student', 'dashboard', [], ['Courses for you']],
            'admin' => ['admin', 'admin.dashboard', [], ['Dashboard']],
            'admin courses' => ['admin', 'admin.courses.index', [], ['Courses']],
        ];

        foreach ($expect as $label => [$actor, $route, $params, $phrases]) {
            auth()->logout();
            $html = $this->visit('rw', $label, [$route, $params, $actor]);
            $translated = 0;
            foreach ($phrases as $phrase) {
                if (isset($rw[$phrase]) && str_contains($html, e($rw[$phrase]))) {
                    $translated++;
                }
            }
            $this->assertGreaterThan(0, $translated, "{$label}: none of the expected Kinyarwanda phrases appear");
        }
    }

    public function test_the_same_page_differs_between_languages(): void
    {
        $this->seedWorld();

        auth()->logout();
        $en = $this->visit('en', 'home', ['home', [], 'guest']);
        auth()->logout();
        $rw = $this->visit('rw', 'home', ['home', [], 'guest']);

        $this->assertNotSame($en, $rw);
        $this->assertStringContainsString('Browse by category', $en);
        $this->assertStringNotContainsString('Browse by category', $rw);
    }
}
