<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
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

        return $course->lessonStatuses($this->student->lessonCompletions()->pluck('lesson_id'), $viewer);
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
}
