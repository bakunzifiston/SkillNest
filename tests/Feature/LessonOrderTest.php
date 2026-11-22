<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Students must finish a lesson before the next one opens; locked lessons are clearly marked. */
class LessonOrderTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private User $student;

    /** @var array<int, Lesson> 1-based */
    private array $l = [];

    protected function setUp(): void
    {
        parent::setUp();
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $this->course = Course::create([
            'category_id' => $category->id, 'title' => 'C', 'slug' => 'c', 'description' => 'd', 'price' => 0,
            'level' => 'beginner', 'status' => Course::STATUS_PUBLISHED,
        ]);
        // Created out of order on purpose: sort_order, not id, decides the sequence.
        $ch2 = Chapter::create(['course_id' => $this->course->id, 'title' => 'Second chapter', 'sort_order' => 2]);
        $ch1 = Chapter::create(['course_id' => $this->course->id, 'title' => 'First chapter', 'sort_order' => 1]);
        $this->l[3] = Lesson::create(['chapter_id' => $ch2->id, 'title' => 'Lesson three', 'type' => 'text', 'content' => 'x', 'sort_order' => 1]);
        $this->l[2] = Lesson::create(['chapter_id' => $ch1->id, 'title' => 'Lesson two', 'type' => 'text', 'content' => 'x', 'sort_order' => 2]);
        $this->l[1] = Lesson::create(['chapter_id' => $ch1->id, 'title' => 'Lesson one', 'type' => 'text', 'content' => 'x', 'sort_order' => 1]);

        $this->student = User::factory()->create();
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
    }

    private function complete(int ...$numbers): void
    {
        foreach ($numbers as $n) {
            LessonCompletion::create(['user_id' => $this->student->id, 'lesson_id' => $this->l[$n]->id, 'completed_at' => now()]);
        }
    }

    private function statuses(?User $viewer = null): array
    {
        $course = Course::find($this->course->id);

        return $course->lessonStatuses($this->student->lessonCompletions()->pluck("lesson_id"), $viewer, $this->student->submittedQuizIds());
    }

    private function lessonStatus(int $n, ?User $viewer = null): string
    {
        return $this->statuses($viewer)[$this->l[$n]->id];
    }

    public function test_only_the_first_lesson_is_open_at_the_start_in_chapter_and_lesson_order(): void
    {
        $this->assertSame('in_progress', $this->lessonStatus(1));
        $this->assertSame('locked', $this->lessonStatus(2));
        $this->assertSame('locked', $this->lessonStatus(3));
    }

    public function test_finishing_a_lesson_unlocks_exactly_the_next_one(): void
    {
        $this->complete(1);

        $this->assertSame('completed', $this->lessonStatus(1));
        $this->assertSame('in_progress', $this->lessonStatus(2));
        $this->assertSame('locked', $this->lessonStatus(3));
    }

    public function test_the_order_continues_across_chapters(): void
    {
        $this->complete(1, 2);

        $this->assertSame('in_progress', $this->lessonStatus(3));
    }

    public function test_a_lesson_that_was_completed_before_stays_open_even_if_earlier_ones_were_skipped(): void
    {
        $this->complete(3); // legacy progress from before lessons were ordered

        $this->assertSame('completed', $this->lessonStatus(3));
        $this->assertSame('in_progress', $this->lessonStatus(1));
        $this->assertSame('locked', $this->lessonStatus(2));
    }

    public function test_opening_a_locked_lesson_redirects_to_the_lesson_to_finish_first(): void
    {
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[3]]))
            ->assertRedirect(route('courses.lessons.show', [$this->course, $this->l[1]]))
            ->assertSessionHas('error', 'This lesson is locked. Finish "Lesson one" first to unlock it.');
    }

    public function test_the_open_lesson_and_completed_lessons_can_be_opened(): void
    {
        $this->complete(1);

        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertOk();
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[2]]))->assertOk();
    }

    public function test_completing_a_lesson_unlocks_the_next_one(): void
    {
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[2]]))->assertRedirect();

        $this->postJson(route('lessons.complete', $this->l[1]))->assertOk();

        $this->get(route('courses.lessons.show', [$this->course, $this->l[2]]))->assertOk();
    }

    public function test_a_locked_lesson_cannot_be_marked_complete_by_hand(): void
    {
        $this->actingAs($this->student)->postJson(route('lessons.complete', $this->l[2]))->assertForbidden();

        $this->assertSame(0, LessonCompletion::where('lesson_id', $this->l[2]->id)->count());
        $this->assertSame('locked', $this->lessonStatus(3));
    }

    public function test_staff_are_never_locked_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Enrollment::create(['user_id' => $admin->id, 'course_id' => $this->course->id]);

        $this->assertNotContains('locked', $this->statuses($admin));
        $this->actingAs($admin)->get(route('courses.lessons.show', [$this->course, $this->l[3]]))->assertOk();
    }

    public function test_a_course_can_allow_any_order(): void
    {
        $this->course->update(['sequential_lessons' => false]);

        $this->assertNotContains('locked', $this->statuses());
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[3]]))->assertOk();
    }

    public function test_course_page_shows_completed_in_progress_and_locked(): void
    {
        $this->complete(1);

        $html = $this->actingAs($this->student)->get(route('courses.show', $this->course))->assertOk()->getContent();

        $this->assertStringContainsString('data-lesson-status-badge="completed"', $html);
        $this->assertStringContainsString('data-lesson-status-badge="in_progress"', $html);
        $this->assertStringContainsString('data-lesson-status-badge="locked"', $html);
        $this->assertSame(1, substr_count($html, 'data-lesson-status-badge="locked"')); // lesson three only
    }

    public function test_sidebar_marks_locked_lessons_and_does_not_link_them_until_unlocked(): void
    {
        $html = $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '<div data-lock-row='));
        $this->assertMatchesRegularExpression('/data-open-row="\d+"[^>]*class="[^"]*\bhidden\b/', $html);
    }

    public function test_next_lesson_button_is_disabled_until_the_lesson_is_completed(): void
    {
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[1]]))
            ->assertOk()->assertSee('<span data-next-locked', false)->assertSee('Complete this lesson to unlock the next one.');

        $this->complete(1);

        $this->get(route('courses.lessons.show', [$this->course, $this->l[1]]))
            ->assertOk()->assertDontSee('<span data-next-locked', false);
    }

    public function test_lesson_header_shows_the_status(): void
    {
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[1]]))
            ->assertSee('id="lesson-status-badge"', false)->assertSee('data-lesson-status-badge="in_progress"', false);

        $this->complete(1);

        $this->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertSee('data-lesson-status-badge="completed"', false);
    }

    public function test_labels_appear_in_kinyarwanda(): void
    {
        $this->complete(1);

        $this->actingAs($this->student)->withSession(['locale_choice' => 'rw'])->get(route('courses.show', $this->course))
            ->assertSee('Byarangiye')->assertSee('Bikomeje')->assertSee('Bifunze');
    }

    public function test_admin_can_turn_the_rule_off_and_new_courses_default_to_on(): void
    {
        $this->assertTrue(Course::find($this->course->id)->sequential_lessons);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.courses.edit', $this->course))->assertOk()
            ->assertSee('Students must complete lessons in order');

        $payload = ['title' => 'C', 'status' => Course::STATUS_PUBLISHED, 'category_id' => $this->course->category_id, 'description' => 'd'];
        $this->put(route('admin.courses.update', $this->course), $payload + ['sequential_lessons' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($this->course->fresh()->sequential_lessons);

        // Field not sent: keeps the current value.
        $this->put(route('admin.courses.update', $this->course), $payload)->assertSessionHasNoErrors();
        $this->assertFalse($this->course->fresh()->sequential_lessons);

        $this->put(route('admin.courses.update', $this->course), $payload + ['sequential_lessons' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($this->course->fresh()->sequential_lessons);
    }

    // ---------------------------------------------------------------- quizzes

    private function quiz(array $placement = []): Quiz
    {
        return Quiz::create(array_merge(['course_id' => $this->course->id, 'title' => 'Final quiz', 'passing_grade' => 50, 'is_published' => true], $placement));
    }

    public function test_a_course_wide_quiz_needs_every_lesson_completed(): void
    {
        $quiz = $this->quiz();
        $this->complete(1, 2);

        $this->actingAs($this->student)->get(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertRedirect(route('courses.lessons.show', [$this->course, $this->l[3]]))
            ->assertSessionHas('error', 'This quiz is locked. Finish "Lesson three" first to unlock it.');

        $this->complete(3);
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk();
    }

    public function test_a_quiz_after_a_lesson_only_needs_the_lessons_up_to_it(): void
    {
        $quiz = $this->quiz(['lesson_id' => $this->l[2]->id, 'chapter_id' => $this->l[2]->chapter_id]);

        $this->actingAs($this->student)->get(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertRedirect(route('courses.lessons.show', [$this->course, $this->l[1]]));

        $this->complete(1);
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertRedirect(route('courses.lessons.show', [$this->course, $this->l[2]]));

        $this->complete(2); // lesson three is not required for this quiz
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk();
    }

    public function test_a_chapter_quiz_needs_the_whole_chapter(): void
    {
        $quiz = $this->quiz(['chapter_id' => $this->l[1]->chapter_id]);
        $this->complete(1);

        $this->actingAs($this->student)->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertRedirect();

        $this->complete(2);
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk();
    }

    public function test_a_locked_quiz_cannot_be_started(): void
    {
        $quiz = $this->quiz();

        $this->actingAs($this->student)->post(route('courses.quizzes.start', [$this->course, $quiz]))->assertRedirect();

        $this->assertSame(0, \App\Models\QuizAttempt::count());
    }

    public function test_staff_can_open_a_locked_quiz(): void
    {
        $quiz = $this->quiz();
        $admin = User::factory()->create(['is_admin' => true]);
        Enrollment::create(['user_id' => $admin->id, 'course_id' => $this->course->id]);

        $this->actingAs($admin)->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk();
    }

    public function test_quiz_shows_as_locked_on_the_course_page_and_sidebar_until_unlocked(): void
    {
        $quiz = $this->quiz(['lesson_id' => $this->l[1]->id, 'chapter_id' => $this->l[1]->chapter_id]);

        $this->actingAs($this->student)->get(route('courses.show', $this->course))
            ->assertSee('Locked until the lessons before it are completed');
        $this->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertSee('data-quiz-locked', false);

        $this->complete(1);

        $this->get(route('courses.show', $this->course))->assertDontSee('Locked until the lessons before it are completed');
        $this->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertDontSee('<div data-quiz-locked', false);
    }

    // ---------------------------------------------------------------- quiz under a lesson holds back the next lesson

    private function lessonQuiz(int $n): Quiz
    {
        return $this->quiz(['lesson_id' => $this->l[$n]->id, 'chapter_id' => $this->l[$n]->chapter_id]);
    }

    private function submitAttempt(Quiz $quiz): void
    {
        \App\Models\QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $this->student->id, 'started_at' => now(), 'submitted_at' => now(), 'score' => 0, 'total_points' => 1, 'passed' => false]);
    }

    public function test_the_next_lesson_stays_locked_until_the_quiz_under_the_lesson_is_taken(): void
    {
        $quiz = $this->lessonQuiz(1);
        $this->complete(1);

        $this->assertSame('completed', $this->lessonStatus(1));
        $this->assertSame('locked', $this->lessonStatus(2));

        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[2]]))
            ->assertRedirect(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertSessionHas('error', 'Take the quiz "Final quiz" first to unlock the next lesson.');
    }

    public function test_taking_the_quiz_unlocks_the_next_lesson(): void
    {
        $quiz = $this->lessonQuiz(1);
        $this->complete(1);
        $this->submitAttempt($quiz);

        $this->assertSame('in_progress', $this->lessonStatus(2));
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[2]]))->assertOk();
    }

    public function test_an_unsubmitted_attempt_does_not_count_as_taking_the_quiz(): void
    {
        $quiz = $this->lessonQuiz(1);
        $this->complete(1);
        \App\Models\QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $this->student->id, 'started_at' => now()]);

        $this->assertSame('locked', $this->lessonStatus(2));
    }

    public function test_an_unpublished_quiz_does_not_hold_anything_back(): void
    {
        $this->lessonQuiz(1)->update(['is_published' => false]);
        $this->complete(1);

        $this->assertSame('in_progress', $this->lessonStatus(2));
    }

    public function test_lesson_page_offers_the_quiz_instead_of_next_lesson(): void
    {
        $quiz = $this->lessonQuiz(1);
        $url = route('courses.lessons.show', [$this->course, $this->l[1]]);

        $this->actingAs($this->student)->get($url)->assertSee('<span data-next-locked', false)->assertSee('<span data-next-quiz class="hidden ', false);

        $this->complete(1);

        $this->get($url)->assertDontSee('<span data-next-locked', false)
            ->assertSee('Take the quiz to continue')->assertSee(route('courses.quizzes.show', [$this->course, $quiz]), false)
            ->assertDontSee('<span data-next-quiz class="hidden ', false);

        $this->submitAttempt($quiz);

        $this->get($url)->assertDontSee('Take the quiz to continue')->assertSee(route('courses.lessons.show', [$this->course, $this->l[2]]), false);
    }

    public function test_staff_and_free_order_courses_are_not_held_back_by_the_quiz(): void
    {
        $this->lessonQuiz(1);
        $this->complete(1);
        $this->course->update(['sequential_lessons' => false]);

        $this->assertNotContains('locked', $this->statuses());
        $this->actingAs($this->student)->get(route('courses.lessons.show', [$this->course, $this->l[1]]))->assertDontSee('Take the quiz to continue');
    }
}
