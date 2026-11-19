<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Admins can add optional Kinyarwanda text to content; learners see it; English stays the original. */
class ContentTranslationAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    }

    private function category(): Category
    {
        return Category::first() ?? Category::create(['name' => 'Design', 'slug' => 'design']);
    }

    private function publishedCourse(array $extra = []): Course
    {
        return Course::create(array_merge([
            'category_id' => $this->category()->id, 'title' => 'English Course', 'slug' => 'english-course',
            'description' => 'English description', 'price' => 0, 'level' => 'beginner', 'status' => Course::STATUS_PUBLISHED,
        ], $extra));
    }

    private function asRw(): void
    {
        $this->admin->forceFill(['locale' => 'rw'])->save();
    }

    public function test_admin_creates_a_course_with_a_kinyarwanda_version(): void
    {
        $this->get(route('admin.courses.create'))->assertOk()->assertSee('Kinyarwanda translation (optional)');

        $this->post(route('admin.courses.store'), [
            'category_id' => $this->category()->id, 'title' => 'Digital Marketing', 'status' => 'published',
            'description' => 'Learn marketing online',
            'tr' => ['rw' => ['title' => 'Kwamamaza kuri murandasi', 'description' => 'Wige kwamamaza kuri murandasi']],
        ])->assertRedirect();

        $course = Course::where('title', 'Digital Marketing')->firstOrFail();
        $this->assertSame('Digital Marketing', $course->title);                 // English stays the original
        $this->assertSame('Kwamamaza kuri murandasi', $course->translationFor('title', 'rw'));
        $this->assertSame('Wige kwamamaza kuri murandasi', $course->translationFor('description', 'rw'));
    }

    public function test_a_course_can_be_created_without_any_translation(): void
    {
        $this->post(route('admin.courses.store'), [
            'category_id' => $this->category()->id, 'title' => 'English only', 'status' => 'draft',
            'tr' => ['rw' => ['title' => '', 'description' => '']],
        ])->assertRedirect();

        $course = Course::where('title', 'English only')->firstOrFail();
        $this->assertNull($course->translations);
        $this->assertSame('English only', $course->tr('title', 'rw'));          // falls back
    }

    public function test_edit_form_shows_saved_translations_and_blanking_them_removes_them(): void
    {
        $course = $this->publishedCourse();
        $course->saveTranslations(['rw' => ['title' => 'Umutwe w\'isomo']]);

        $this->get(route('admin.courses.edit', $course))->assertOk()->assertSee('Umutwe w&#039;isomo', false);

        $payload = ['category_id' => $course->category_id, 'title' => 'English Course', 'status' => 'published', 'tr' => ['rw' => ['title' => '']]];
        $this->put(route('admin.courses.update', $course), $payload)->assertRedirect();

        $this->assertNull($course->fresh()->translations);
        $this->assertSame('English Course', $course->fresh()->title);
    }

    public function test_updating_without_the_translation_fields_leaves_existing_translations_alone_or_clears_them_predictably(): void
    {
        $course = $this->publishedCourse();
        $course->saveTranslations(['rw' => ['title' => 'Isomo']]);

        // An older client that never sends `tr`: the English edit must still work.
        $this->put(route('admin.courses.update', $course), ['category_id' => $course->category_id, 'title' => 'Renamed', 'status' => 'published'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Renamed', $course->fresh()->title);
    }

    public function test_translations_are_validated(): void
    {
        $this->post(route('admin.courses.store'), [
            'category_id' => $this->category()->id, 'title' => 'Too long', 'status' => 'draft',
            'tr' => ['rw' => ['title' => str_repeat('x', 20001)]],
        ])->assertSessionHasErrors('tr.rw.title');

        $this->post(route('admin.courses.store'), [
            'category_id' => $this->category()->id, 'title' => 'Bad shape', 'status' => 'draft', 'tr' => 'not-an-array',
        ])->assertSessionHasErrors('tr');
    }

    public function test_only_admins_can_save_translations(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.courses.store'), [
            'category_id' => $this->category()->id, 'title' => 'Sneaky', 'status' => 'draft', 'tr' => ['rw' => ['title' => 'x']],
        ])->assertForbidden();

        $this->assertSame(0, Course::count());
    }

    public function test_category_and_lesson_translations_reach_the_learner_in_kinyarwanda(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Health', 'tr' => ['rw' => ['name' => 'Ubuzima']]])->assertRedirect();
        $category = Category::where('name', 'Health')->firstOrFail();
        $this->assertSame('Ubuzima', $category->translationFor('name', 'rw'));

        $course = $this->publishedCourse(['category_id' => $category->id]);
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Chapter one', 'sort_order' => 1]);
        $this->post(route('admin.chapters.lessons.store', $chapter), [
            'title' => 'First lesson', 'type' => 'text', 'content' => 'English lesson text',
            'tr' => ['rw' => ['title' => 'Icyigwa cya mbere', 'content' => 'Inyandiko y\'icyigwa mu Kinyarwanda']],
        ])->assertRedirect();
        $lesson = Lesson::where('title', 'First lesson')->firstOrFail();

        $learner = User::factory()->create(['locale' => 'rw']);
        Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id]);

        $rw = $this->actingAs($learner)->get(route('courses.lessons.show', [$course, $lesson]))->assertOk()->getContent();
        $this->assertStringContainsString('Icyigwa cya mbere', $rw);
        $this->assertStringContainsString('Inyandiko y&#039;icyigwa mu Kinyarwanda', $rw);

        $learner->forceFill(['locale' => 'en'])->save();
        $en = $this->actingAs($learner->fresh())->get(route('courses.lessons.show', [$course, $lesson]))->assertOk()->getContent();
        $this->assertStringContainsString('English lesson text', $en);
        $this->assertStringNotContainsString('Icyigwa cya mbere', $en);
    }

    public function test_quiz_question_and_option_translations_show_to_learners_and_grading_is_unchanged(): void
    {
        $course = $this->publishedCourse();
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Quiz', 'passing_grade' => 50, 'is_published' => true]);

        $this->post(route('admin.quizzes.questions.store', $quiz), [
            'type' => 'mcq', 'question_text' => 'Which is a colour?', 'points' => 1,
            'options' => ['Red', 'Table', 'Chair'], 'correct_option' => 0,
            'tr' => ['rw' => ['question_text' => 'Ni ikihe kibara?']],
            'tr_new_options' => ['Umutuku', 'Ameza', 'Intebe'],
        ])->assertRedirect();

        $question = Question::with('options')->firstOrFail();
        $this->assertSame('Ni ikihe kibara?', $question->translationFor('question_text', 'rw'));
        $this->assertSame(['Umutuku', 'Ameza', 'Intebe'], $question->options->map(fn ($o) => $o->translationFor('option_text', 'rw'))->all());
        $this->assertSame('Red', $question->options->first()->option_text);       // English originals kept
        $this->assertTrue($question->options->first()->is_correct);

        $learner = User::factory()->create(['locale' => 'rw']);
        Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id]);
        $attempt = QuizAttempt::create(['user_id' => $learner->id, 'quiz_id' => $quiz->id, 'started_at' => now(), 'total_points' => 1]);

        $html = $this->actingAs($learner)->get(route('quizzes.take', $attempt))->assertOk()->getContent();
        $this->assertStringContainsString('Ni ikihe kibara?', $html);
        $this->assertStringContainsString('Umutuku', $html);
        $this->assertStringNotContainsString('>Red<', $html);

        // Answering the (Kinyarwanda-labelled) correct option is still graded as correct: ids are unchanged.
        $correct = $question->options->firstWhere('is_correct', true);
        $this->post(route('quizzes.submit', $attempt), ['question_'.$question->id => $correct->id])->assertRedirect();
        $this->assertSame(1, $attempt->fresh()->score);
        $this->assertTrue((bool) $attempt->fresh()->passed);
    }

    public function test_admin_screens_keep_showing_the_english_original_even_in_kinyarwanda(): void
    {
        $course = $this->publishedCourse(['title' => 'Original Title']);
        $course->saveTranslations(['rw' => ['title' => 'Umutwe wahinduwe']]);
        $this->asRw();

        $html = $this->get(route('admin.courses.edit', $course))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="title"[^>]*value="Original Title"/', $html);  // never edit the translation by accident

        $this->get(route('admin.courses.index'))->assertOk()->assertSee('Original Title')->assertSee('RW');
    }

    public function test_the_default_description_migration_translates_only_untouched_courses(): void
    {
        $default = 'Learn essential skills with hands-on projects and expert instruction. Perfect for beginners and intermediate learners.';
        $plain = $this->publishedCourse(['title' => 'Plain', 'slug' => 'plain', 'description' => $default]);
        $written = $this->publishedCourse(['title' => 'Written', 'slug' => 'written', 'description' => $default]);
        $written->saveTranslations(['rw' => ['description' => 'Ibisobanuro byanditswe n\'umuyobozi']]);
        $other = $this->publishedCourse(['title' => 'Other', 'slug' => 'other', 'description' => 'A different description']);

        $migration = require database_path('migrations/2026_11_19_120000_add_kinyarwanda_to_default_course_description.php');
        $migration->up();
        $migration->up(); // running it twice changes nothing more

        $this->assertStringStartsWith('Iga ubumenyi bw\'ingenzi', $plain->fresh()->translationFor('description', 'rw'));
        $this->assertSame('Ibisobanuro byanditswe n\'umuyobozi', $written->fresh()->translationFor('description', 'rw')); // admin text kept
        $this->assertNull($other->fresh()->translations);                                                                  // other text untouched
        $this->assertSame($default, $plain->fresh()->description);                                                         // English original intact

        $migration->down();
        $this->assertNull($plain->fresh()->translations);
        $this->assertSame('Ibisobanuro byanditswe n\'umuyobozi', $written->fresh()->translationFor('description', 'rw'));
    }
}
