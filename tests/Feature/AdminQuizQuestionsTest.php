<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuizQuestionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuiz(): Quiz
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $cat = Category::create(['name' => 'D', 'slug' => 'd']);
        $course = Course::create(['category_id' => $cat->id, 'title' => 'C', 'slug' => 'c', 'description' => 'x', 'price' => 0, 'level' => 'beginner', 'status' => 'published']);

        return Quiz::create(['course_id' => $course->id, 'title' => 'Q', 'passing_grade' => 70, 'is_published' => true]);
    }

    public function test_quiz_can_be_created_unpublished_and_unpublished_later(): void
    {
        $quiz = $this->makeQuiz();
        $this->post(route('admin.quizzes.store'), ['course_id' => $quiz->course_id, 'title' => 'New', 'passing_grade' => 70, 'is_published' => '0']);
        $this->assertFalse(Quiz::where('title', 'New')->first()->is_published);

        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'Q', 'passing_grade' => 70, 'is_published' => '0']);
        $this->assertFalse($quiz->fresh()->is_published);

        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'Q', 'passing_grade' => 70, 'is_published' => '1']);
        $this->assertTrue($quiz->fresh()->is_published);
    }

    public function test_mcq_correct_answer_survives_blank_options(): void
    {
        $quiz = $this->makeQuiz();
        $this->post(route('admin.quizzes.questions.store', $quiz), ['type' => 'mcq', 'question_text' => 'B?', 'points' => 1, 'options' => ['a', '', 'c', 'd'], 'correct_option' => 2]);
        $q = Question::with('options')->first();
        $this->assertSame('c', $q->options->firstWhere('is_correct', true)->option_text);
        $this->assertCount(1, $q->options->where('is_correct', true));
    }

    public function test_mcq_rejects_blank_correct_option(): void
    {
        $quiz = $this->makeQuiz();
        $this->post(route('admin.quizzes.questions.store', $quiz), ['type' => 'mcq', 'question_text' => 'B?', 'points' => 1, 'options' => ['a', '', 'c'], 'correct_option' => 1])
            ->assertSessionHasErrors('options');
        $this->assertSame(0, Question::count());
    }

    public function test_true_false_requires_an_answer(): void
    {
        $quiz = $this->makeQuiz();
        $this->post(route('admin.quizzes.questions.store', $quiz), ['type' => 'true_false', 'question_text' => 'T?', 'points' => 1])
            ->assertSessionHasErrors('true_false_correct');
        $this->assertSame(0, Question::count());

        $this->post(route('admin.quizzes.questions.store', $quiz), ['type' => 'true_false', 'question_text' => 'T?', 'points' => 1, 'true_false_correct' => 'true']);
        $this->assertSame('True', Question::first()->options()->where('is_correct', true)->first()->option_text);
    }

    public function test_quiz_can_be_placed_in_a_section_and_after_a_lesson(): void
    {
        $quiz = $this->makeQuiz();
        $chapter = Chapter::create(['course_id' => $quiz->course_id, 'title' => 'Intro', 'sort_order' => 1]);
        $lesson = Lesson::create(['chapter_id' => $chapter->id, 'title' => 'L1', 'type' => 'text', 'sort_order' => 1]);

        $this->get(route('admin.quizzes.create'))->assertOk();
        $this->post(route('admin.quizzes.store'), ['course_id' => $quiz->course_id, 'title' => 'Placed', 'passing_grade' => 70, 'chapter_id' => $chapter->id, 'lesson_id' => $lesson->id]);
        $placed = Quiz::where('title', 'Placed')->first();
        $this->assertSame($chapter->id, $placed->chapter_id);
        $this->assertSame($lesson->id, $placed->lesson_id);

        $this->get(route('admin.quizzes.edit', $placed))->assertOk();
        $this->put(route('admin.quizzes.update', $placed), ['title' => 'Placed', 'passing_grade' => 70, 'chapter_id' => $chapter->id, 'lesson_id' => '']);
        $this->assertNull($placed->fresh()->lesson_id);
        $this->assertSame($chapter->id, $placed->fresh()->chapter_id);
    }

    public function test_section_from_another_course_is_rejected(): void
    {
        $quiz = $this->makeQuiz();
        $other = Course::create(['category_id' => $quiz->course->category_id, 'title' => 'Other', 'slug' => 'other', 'description' => 'x', 'price' => 0, 'level' => 'beginner', 'status' => 'published']);
        $foreign = Chapter::create(['course_id' => $other->id, 'title' => 'Foreign', 'sort_order' => 1]);

        $this->post(route('admin.quizzes.store'), ['course_id' => $quiz->course_id, 'title' => 'Bad', 'passing_grade' => 70, 'chapter_id' => $foreign->id])
            ->assertSessionHasErrors('chapter_id');
        $this->assertNull(Quiz::where('title', 'Bad')->first());
    }

    public function test_course_page_shows_quiz_under_its_section_for_enrolled_students(): void
    {
        $quiz = $this->makeQuiz();
        $quiz->course->update(['status' => 'published']);
        $chapter = Chapter::create(['course_id' => $quiz->course_id, 'title' => 'Intro', 'sort_order' => 1]);
        $lesson = Lesson::create(['chapter_id' => $chapter->id, 'title' => 'L1', 'type' => 'text', 'sort_order' => 1]);
        $quiz->update(['chapter_id' => $chapter->id, 'lesson_id' => $lesson->id, 'title' => 'Placed Quiz']);
        $general = Quiz::create(['course_id' => $quiz->course_id, 'title' => 'General Quiz', 'passing_grade' => 70, 'is_published' => true]);

        $student = User::factory()->create();
        Enrollment::create(['user_id' => $student->id, 'course_id' => $quiz->course_id]);
        $html = $this->actingAs($student)->get(route('courses.show', $quiz->course))->assertOk()->getContent();

        // Enrolled students see the quiz in the sidebar and in the curriculum list.
        $this->assertSame(2, substr_count($html, 'Placed Quiz'));
        $this->assertSame(1, substr_count($html, 'General Quiz'));
        $this->assertLessThan(strrpos($html, 'Placed Quiz'), strrpos($html, 'L1'));
    }

    public function test_quiz_is_visible_but_locked_for_non_enrolled_visitors(): void
    {
        $quiz = $this->makeQuiz();
        $chapter = Chapter::create(['course_id' => $quiz->course_id, 'title' => 'Chap2', 'sort_order' => 1]);
        $quiz->update(['chapter_id' => $chapter->id, 'title' => 'Visible Quiz']);

        $html = $this->actingAs(User::factory()->create())->get(route('courses.show', $quiz->course))->assertOk()->getContent();
        $this->assertStringContainsString('Visible Quiz', $html);
        $this->assertStringNotContainsString(route('courses.quizzes.show', [$quiz->course, $quiz]), $html);
    }

    public function test_lesson_page_sidebar_lists_quizzes(): void
    {
        $quiz = $this->makeQuiz();
        $chapter = Chapter::create(['course_id' => $quiz->course_id, 'title' => 'Chap1', 'sort_order' => 1]);
        $lesson = Lesson::create(['chapter_id' => $chapter->id, 'title' => 'Intro', 'type' => 'text', 'sort_order' => 1]);
        $empty = Chapter::create(['course_id' => $quiz->course_id, 'title' => 'chap2', 'sort_order' => 2]);
        $quiz->update(['chapter_id' => $chapter->id, 'lesson_id' => $lesson->id, 'title' => 'Sidebar Quiz A']);
        Quiz::create(['course_id' => $quiz->course_id, 'chapter_id' => $empty->id, 'title' => 'Sidebar Quiz B', 'passing_grade' => 70, 'is_published' => true]);

        $student = User::factory()->create();
        Enrollment::create(['user_id' => $student->id, 'course_id' => $quiz->course_id]);
        $html = $this->actingAs($student)->get(route('courses.lessons.show', [$quiz->course, $lesson]))->assertOk()->getContent();

        $this->assertStringContainsString('Sidebar Quiz A', $html);
        $this->assertStringContainsString('Sidebar Quiz B', $html);
    }

    public function test_admin_dashboard_loads_when_quiz_attempts_exist(): void
    {
        $quiz = $this->makeQuiz();
        $student = User::factory()->create();
        Enrollment::create(['user_id' => $student->id, 'course_id' => $quiz->course_id]);
        QuizAttempt::create(['user_id' => $student->id, 'quiz_id' => $quiz->id, 'started_at' => now(), 'submitted_at' => now(), 'score' => 1, 'total_points' => 2, 'percentage' => 50, 'passed' => false]);

        $this->get(route('admin.dashboard'))->assertOk();
    }
}
