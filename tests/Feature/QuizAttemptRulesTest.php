<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** How often a student may take a quiz, resuming, retake after passing, and the enforced time limit. */
class QuizAttemptRulesTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private User $student;

    private Question $question;

    private QuestionOption $right;

    private QuestionOption $wrong;

    protected function setUp(): void
    {
        parent::setUp();
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $this->course = Course::create([
            'category_id' => $category->id, 'title' => 'C', 'slug' => 'c', 'description' => 'd', 'price' => 0,
            'level' => 'beginner', 'status' => Course::STATUS_PUBLISHED,
        ]);
        $this->student = User::factory()->create();
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
    }

    private function quiz(array $extra = []): Quiz
    {
        $quiz = Quiz::create(array_merge(['course_id' => $this->course->id, 'title' => 'Quiz', 'passing_grade' => 50, 'is_published' => true], $extra));
        $this->question = Question::create(['quiz_id' => $quiz->id, 'type' => 'mcq', 'question_text' => 'Q?', 'points' => 1, 'sort_order' => 1]);
        $this->right = QuestionOption::create(['question_id' => $this->question->id, 'option_text' => 'Right', 'is_correct' => true, 'sort_order' => 1]);
        $this->wrong = QuestionOption::create(['question_id' => $this->question->id, 'option_text' => 'Wrong', 'is_correct' => false, 'sort_order' => 2]);

        return $quiz;
    }

    private function start(Quiz $quiz, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->student)->post(route('courses.quizzes.start', [$this->course, $quiz]));
    }

    private function take(Quiz $quiz, bool $correct): QuizAttempt
    {
        $this->start($quiz);
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->whereNull('submitted_at')->latest('id')->firstOrFail();
        $this->post(route('quizzes.submit', $attempt), ['question_'.$this->question->id => ($correct ? $this->right : $this->wrong)->id]);

        return $attempt->fresh();
    }

    // ---------------------------------------------------------------- unlimited (today's behaviour) + resume

    public function test_without_a_maximum_a_student_can_still_retake_as_often_as_they_like(): void
    {
        $quiz = $this->quiz();

        foreach (range(1, 4) as $n) {
            $this->take($quiz, false);
        }

        $this->assertSame(4, QuizAttempt::where('quiz_id', $quiz->id)->whereNotNull('submitted_at')->count());
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk()->assertSee('Attempts: 4 used (no limit)')->assertSee('Retake quiz');
    }

    public function test_starting_again_resumes_the_unfinished_attempt_instead_of_creating_another(): void
    {
        $quiz = $this->quiz();

        $first = $this->start($quiz);
        $second = $this->start($quiz);
        $third = $this->start($quiz);

        $this->assertSame(1, QuizAttempt::where('quiz_id', $quiz->id)->count());
        $attempt = QuizAttempt::first();
        $first->assertRedirect(route('quizzes.take', $attempt));
        $second->assertRedirect(route('quizzes.take', $attempt));
        $third->assertRedirect(route('quizzes.take', $attempt));

        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertSee('Resume quiz')->assertSee('unfinished attempt');
    }

    // ---------------------------------------------------------------- maximum attempts

    public function test_the_maximum_number_of_attempts_is_enforced_on_the_server(): void
    {
        $quiz = $this->quiz(['max_attempts' => 2]);

        $this->take($quiz, false);
        $this->take($quiz, false);

        $this->start($quiz)
            ->assertRedirect(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertSessionHas('error', 'You have used all 2 attempts for this quiz.');
        $this->assertSame(2, QuizAttempt::where('quiz_id', $quiz->id)->count());       // no third attempt was created

        $page = $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertOk();
        $page->assertSee('Attempts: 2 of 2 used')->assertDontSee('Start quiz')->assertDontSee('Retake quiz')->assertSee('View your last result');
    }

    public function test_an_unfinished_attempt_does_not_use_up_an_attempt(): void
    {
        $quiz = $this->quiz(['max_attempts' => 1]);

        $this->start($quiz);
        $this->start($quiz);                                                              // resumes, does not count twice
        $this->assertSame(1, QuizAttempt::count());
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertSee('Attempts: 0 of 1 used');

        $attempt = QuizAttempt::first();
        $this->post(route('quizzes.submit', $attempt), ['question_'.$this->question->id => $this->wrong->id]);

        $this->start($quiz)->assertSessionHas('error');                                   // now the single attempt is used
        $this->assertSame(1, QuizAttempt::count());
    }

    public function test_each_student_has_their_own_attempts(): void
    {
        $quiz = $this->quiz(['max_attempts' => 1]);
        $other = User::factory()->create();
        Enrollment::create(['user_id' => $other->id, 'course_id' => $this->course->id]);

        $this->take($quiz, false);
        $this->start($quiz)->assertSessionHas('error');

        $this->start($quiz, $other)->assertSessionMissing('error');                       // the other student can still start
        $this->assertSame(1, QuizAttempt::where('user_id', $other->id)->count());
    }

    // ---------------------------------------------------------------- retake after passing

    public function test_a_quiz_can_forbid_retaking_after_a_pass_but_still_allows_retrying_after_a_fail(): void
    {
        $quiz = $this->quiz(['allow_retake_after_pass' => false]);

        $this->take($quiz, false);
        $this->start($quiz)->assertSessionMissing('error');                               // failed: may try again
        $attempt = QuizAttempt::whereNull('submitted_at')->first();
        $this->post(route('quizzes.submit', $attempt), ['question_'.$this->question->id => $this->right->id]);   // passes

        $this->start($quiz)->assertSessionHas('error', 'You already passed this quiz and cannot retake it.');
        $this->assertSame(2, QuizAttempt::count());
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertSee('You cannot retake this quiz after passing it.');
        // One clear message when retaking is blocked (no second "You passed" box saying the same thing).
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))->assertSee('You already passed this quiz and cannot retake it.')->assertDontSee('You passed this quiz.');
    }

    public function test_by_default_a_student_may_retake_after_passing(): void
    {
        $quiz = $this->quiz();

        $this->take($quiz, true);
        $this->start($quiz)->assertSessionMissing('error')->assertRedirect();

        $this->assertSame(2, QuizAttempt::count());
    }

    // ---------------------------------------------------------------- time limit

    public function test_an_attempt_whose_time_ran_out_is_closed_and_counts_as_used(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10, 'max_attempts' => 1]);
        $attempt = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes(30), 'total_points' => 1]);

        $this->actingAs($this->student)->get(route('quizzes.take', $attempt))
            ->assertRedirect(route('quizzes.result', $attempt))
            ->assertSessionHas('info', 'Time is up. Your attempt was closed.');

        $attempt->refresh();
        $this->assertNotNull($attempt->submitted_at);
        $this->assertTrue($attempt->timed_out);
        $this->assertSame(0, $attempt->score);
        $this->assertFalse($attempt->passed);

        $this->start($quiz)->assertSessionHas('error');                                    // it used the only attempt
        $this->get(route('quizzes.result', $attempt))->assertOk()->assertSee('closed without answers');
    }

    public function test_late_answers_are_ignored_but_answers_in_time_or_within_the_grace_period_are_graded(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $answer = fn (QuizAttempt $a, $option) => $this->actingAs($this->student)->post(route('quizzes.submit', $a), ['question_'.$this->question->id => $option->id]);

        // On time.
        $a = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes(5), 'total_points' => 1]);
        $answer($a, $this->right);
        $this->assertSame(1, $a->fresh()->score);
        $this->assertFalse($a->fresh()->timed_out);

        // Just after the deadline, inside the 20 second grace (the browser's automatic submit arrives here).
        $b = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes(10)->subSeconds(8), 'total_points' => 1]);
        $answer($b, $this->right);
        $this->assertSame(1, $b->fresh()->score);
        $this->assertFalse($b->fresh()->timed_out);

        // Well after the deadline with nothing saved: the late answer is not accepted.
        $c = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes(15), 'total_points' => 1]);
        $answer($c, $this->right)->assertRedirect(route('quizzes.result', $c));
        $this->assertSame(0, $c->fresh()->score);
        $this->assertTrue($c->fresh()->timed_out);
        $this->assertFalse($c->fresh()->passed);
        $this->assertSame(1, QuizAttemptAnswer::where('quiz_attempt_id', $c->id)->whereNull('question_option_id')->count());   // recorded as unanswered
    }

    public function test_quizzes_without_a_time_limit_never_expire(): void
    {
        $quiz = $this->quiz();
        $attempt = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subDays(30), 'total_points' => 1]);

        $page = $this->actingAs($this->student)->get(route('quizzes.take', $attempt))->assertOk();
        $this->assertStringNotContainsString('id="quiz-timer"', $page->getContent());
        $this->start($quiz)->assertRedirect(route('quizzes.take', $attempt));              // resumes it
    }

    public function test_the_quiz_page_shows_a_countdown_from_the_servers_clock(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $this->start($quiz);
        $attempt = QuizAttempt::first();

        $html = $this->get(route('quizzes.take', $attempt))->assertOk()->getContent();

        preg_match('/id="quiz-timer" data-remaining="(\d+)"/', $html, $m);
        $this->assertNotEmpty($m);
        $this->assertGreaterThan(590, (int) $m[1]);
        $this->assertLessThanOrEqual(600, (int) $m[1]);
        $this->assertStringContainsString('form.submit()', $html);                          // sends the answers when time is up
    }

    // ---------------------------------------------------------------- guards that must stay

    public function test_a_student_who_is_not_enrolled_cannot_start_and_nothing_is_created(): void
    {
        $quiz = $this->quiz();
        $stranger = User::factory()->create();

        $this->start($quiz, $stranger)->assertRedirect(route('courses.show', $this->course));

        $this->assertSame(0, QuizAttempt::count());
    }

    public function test_only_the_owner_can_open_or_submit_an_attempt(): void
    {
        $quiz = $this->quiz();
        $this->start($quiz);
        $attempt = QuizAttempt::first();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('quizzes.take', $attempt))->assertForbidden();
        $this->actingAs($other)->post(route('quizzes.submit', $attempt), [])->assertForbidden();
    }

    // ---------------------------------------------------------------- Kinyarwanda

    public function test_the_attempt_messages_read_in_kinyarwanda(): void
    {
        $quiz = $this->quiz(['max_attempts' => 1]);
        $this->take($quiz, false);
        $this->student->forceFill(['locale' => 'rw'])->save();

        $this->start($quiz)->assertSessionHas('error', "Wakoresheje ibigerageza byose 1 by'iri suzuma.");
        $this->get(route('courses.quizzes.show', [$this->course, $quiz]))
            ->assertSee('Ibigerageza: 1 kuri 1 byakoreshejwe')
            ->assertSee('Reba ibisubizo byawe bya nyuma');
    }

    // ---------------------------------------------------------------- admin settings

    private function admin(): User
    {
        return tap(User::factory()->create(['is_admin' => true]), fn ($a) => $this->actingAs($a));
    }

    public function test_admin_can_set_the_attempt_rules_when_creating_and_editing_a_quiz(): void
    {
        $this->admin();
        $this->get(route('admin.quizzes.create'))->assertOk()->assertSee('Maximum attempts (optional)')->assertSee('Students can retake the quiz after passing it');

        $this->post(route('admin.quizzes.store'), [
            'course_id' => $this->course->id, 'title' => 'Limited', 'passing_grade' => 70,
            'max_attempts' => '3', 'allow_retake_after_pass' => '0',
        ])->assertRedirect();
        $quiz = Quiz::where('title', 'Limited')->firstOrFail();
        $this->assertSame(3, $quiz->max_attempts);
        $this->assertFalse($quiz->allow_retake_after_pass);

        $this->get(route('admin.quizzes.edit', $quiz))->assertOk()->assertSee('value="3"', false);

        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'Limited', 'passing_grade' => 70, 'max_attempts' => '', 'allow_retake_after_pass' => '1'])->assertRedirect();
        $this->assertNull($quiz->fresh()->max_attempts);
        $this->assertTrue($quiz->fresh()->allow_retake_after_pass);
    }

    public function test_new_quizzes_default_to_unlimited_attempts_with_retake_allowed(): void
    {
        $this->admin();
        $this->post(route('admin.quizzes.store'), ['course_id' => $this->course->id, 'title' => 'Plain', 'passing_grade' => 70]);

        $quiz = Quiz::where('title', 'Plain')->firstOrFail();
        $this->assertNull($quiz->max_attempts);
        $this->assertTrue($quiz->allow_retake_after_pass);
    }

    public function test_updating_without_the_new_fields_keeps_the_current_rules(): void
    {
        $this->admin();
        $quiz = $this->quiz(['max_attempts' => 2, 'allow_retake_after_pass' => false]);

        // An older form that never sends the checkbox: the setting must not flip back on silently.
        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'Renamed', 'passing_grade' => 70, 'max_attempts' => '2'])->assertRedirect();

        $this->assertFalse($quiz->fresh()->allow_retake_after_pass);
        $this->assertSame(2, $quiz->fresh()->max_attempts);
    }

    public function test_the_maximum_must_be_a_sensible_number(): void
    {
        $this->admin();

        foreach (['0', '101', 'abc', '-3', '2.5'] as $bad) {
            $this->post(route('admin.quizzes.store'), ['course_id' => $this->course->id, 'title' => 'Bad', 'passing_grade' => 70, 'max_attempts' => $bad])
                ->assertSessionHasErrors('max_attempts');
        }
        $this->assertSame(0, Quiz::where('title', 'Bad')->count());
    }

    public function test_students_cannot_change_the_rules(): void
    {
        $quiz = $this->quiz();

        $this->actingAs($this->student)->put(route('admin.quizzes.update', $quiz), ['title' => 'x', 'passing_grade' => 70, 'max_attempts' => 1])->assertForbidden();

        $this->assertNull($quiz->fresh()->max_attempts);
    }

    // ---------------------------------------------------------------- who may set the limit (staff roles, not only super admins)

    private function staffWith(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'Staff '.uniqid(), 'slug' => 'staff-'.uniqid(), 'description' => 'test']);
        $role->syncPermissions($permissions);
        $staff = User::factory()->create();
        $staff->assignRole($role);
        $staff->save();

        return $staff;
    }

    public function test_staff_with_the_quiz_permissions_can_configure_attempts_without_being_a_super_admin(): void
    {
        $editor = $this->staffWith(['dashboard' => ['view'], 'quizzes' => ['view', 'create', 'edit']]);
        $this->actingAs($editor);

        $this->post(route('admin.quizzes.store'), ['course_id' => $this->course->id, 'title' => 'By staff', 'passing_grade' => 60, 'max_attempts' => 5])->assertRedirect();
        $quiz = Quiz::where('title', 'By staff')->firstOrFail();
        $this->assertSame(5, $quiz->max_attempts);

        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'By staff', 'passing_grade' => 60, 'max_attempts' => 1])->assertRedirect();
        $this->assertSame(1, $quiz->fresh()->max_attempts);
    }

    public function test_staff_who_can_only_view_quizzes_cannot_change_the_limit(): void
    {
        $viewer = $this->staffWith(['dashboard' => ['view'], 'quizzes' => ['view']]);
        $quiz = $this->quiz(['max_attempts' => 3]);
        $this->actingAs($viewer);

        $this->get(route('admin.quizzes.index'))->assertOk();
        $this->put(route('admin.quizzes.update', $quiz), ['title' => 'x', 'passing_grade' => 60, 'max_attempts' => 99])->assertForbidden();
        $this->post(route('admin.quizzes.store'), ['course_id' => $this->course->id, 'title' => 'nope', 'passing_grade' => 60, 'max_attempts' => 2])->assertForbidden();

        $this->assertSame(3, $quiz->fresh()->max_attempts);
        $this->assertSame(0, Quiz::where('title', 'nope')->count());
    }

    public function test_staff_without_the_quiz_module_cannot_even_open_the_quiz_forms(): void
    {
        $other = $this->staffWith(['dashboard' => ['view'], 'courses' => ['view', 'edit']]);

        $this->actingAs($other)->get(route('admin.quizzes.create'))->assertForbidden();
    }

    // ---------------------------------------------------------------- autosave + automatic submission

    private function openAttempt(Quiz $quiz, int $minutesAgo = 0): QuizAttempt
    {
        return QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes($minutesAgo), 'total_points' => 1]);
    }

    private function autosave(QuizAttempt $attempt, array $answers, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->student)->postJson(route('quizzes.autosave', $attempt), ['answers' => $answers]);
    }

    public function test_answers_are_saved_as_the_student_goes_and_restored_when_they_return(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 30]);
        $attempt = $this->openAttempt($quiz, 2);

        $this->autosave($attempt, [$this->question->id => $this->right->id])->assertOk()->assertJson(['saved' => true]);
        $this->assertSame([(string) $this->question->id => $this->right->id], $attempt->fresh()->draft_answers);

        $html = $this->actingAs($this->student)->get(route('quizzes.take', $attempt))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/value="'.$this->right->id.'"[^>]*required\s+checked/', $html);   // the saved answer is ticked again
        $this->assertDoesNotMatchRegularExpression('/value="'.$this->wrong->id.'"[^>]*checked/', $html);
        $this->assertStringContainsString('Your answers are saved automatically.', $html);
    }

    public function test_autosave_only_accepts_real_answers_from_the_owner(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 30]);
        $attempt = $this->openAttempt($quiz);
        $otherQuestion = Question::create(['quiz_id' => $quiz->id, 'type' => 'mcq', 'question_text' => 'Other', 'points' => 1, 'sort_order' => 2]);
        $foreignOption = QuestionOption::create(['question_id' => $otherQuestion->id, 'option_text' => 'x', 'is_correct' => true, 'sort_order' => 1]);

        // An option that belongs to another question, a made-up id and text are all dropped.
        $this->autosave($attempt, [$this->question->id => $foreignOption->id, 999999 => $this->right->id, $otherQuestion->id => 'abc'])->assertOk();
        $this->assertNull($attempt->fresh()->draft_answers);

        $stranger = User::factory()->create();
        $this->autosave($attempt, [$this->question->id => $this->right->id], $stranger)->assertForbidden();
        $this->assertNull($attempt->fresh()->draft_answers);

        $this->autosave($attempt, ['not' => 'a list'])->assertOk();                          // junk keys are simply ignored
        $this->actingAs($this->student)->postJson(route('quizzes.autosave', $attempt), ['answers' => 'text'])->assertStatus(422);
    }

    public function test_when_time_runs_out_the_saved_answers_are_submitted_automatically(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $attempt = $this->openAttempt($quiz, 8);
        $this->autosave($attempt, [$this->question->id => $this->right->id])->assertOk();

        $this->travel(5)->minutes();                                                       // the student's tab is gone; the deadline passes

        // ...and the next time the system looks at the attempt, the saved answer is graded.
        $this->actingAs($this->student)->get(route('quizzes.take', $attempt))->assertRedirect(route('quizzes.result', $attempt));

        $attempt->refresh();
        $this->assertTrue($attempt->timed_out);
        $this->assertSame(1, $attempt->score);                                             // not 0: the saved answer counted
        $this->assertTrue($attempt->passed);
        $this->assertNull($attempt->draft_answers);
        $this->get(route('quizzes.result', $attempt))->assertOk()->assertSee('the answers you had saved were submitted automatically');
    }

    public function test_a_late_payload_cannot_replace_what_was_saved_before_the_deadline(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $attempt = $this->openAttempt($quiz, 9);
        $this->autosave($attempt, [$this->question->id => $this->wrong->id])->assertOk();            // saved in time: wrong

        $this->travel(5)->minutes();
        // Sent long after the deadline with the right answer: ignored.
        $this->actingAs($this->student)->post(route('quizzes.submit', $attempt), ['question_'.$this->question->id => $this->right->id]);

        $this->assertSame(0, $attempt->fresh()->score);
        $this->assertTrue($attempt->fresh()->timed_out);
    }

    public function test_autosave_after_the_deadline_submits_and_tells_the_page_to_leave(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $attempt = $this->openAttempt($quiz, 11);

        $this->autosave($attempt, [$this->question->id => $this->right->id])
            ->assertStatus(409)
            ->assertJson(['closed' => true, 'redirect' => route('quizzes.result', $attempt)]);

        $this->assertTrue($attempt->fresh()->timed_out);
        $this->assertSame(0, $attempt->fresh()->score);                                    // this answer arrived after the deadline
    }

    public function test_autosave_for_an_attempt_that_is_already_finished_is_refused(): void
    {
        $quiz = $this->quiz();
        $done = $this->take($quiz, true);

        $this->autosave($done, [$this->question->id => $this->wrong->id])->assertStatus(409);
        $this->assertSame(1, $done->fresh()->score);                                       // the finished result cannot change
    }

    public function test_the_scheduled_command_closes_abandoned_attempts_for_every_student(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $other = User::factory()->create();

        $abandoned = $this->openAttempt($quiz, 40);
        $this->autosave($this->openAttempt($quiz, 1), [$this->question->id => $this->right->id]);              // still running: untouched
        $running = QuizAttempt::latest('id')->first();
        $mine = QuizAttempt::create(['user_id' => $other->id, 'quiz_id' => $quiz->id, 'started_at' => now()->subMinutes(30), 'total_points' => 1, 'draft_answers' => [$this->question->id => $this->right->id]]);
        $noLimit = $this->quiz();                                                           // another quiz without a time limit
        $untimed = QuizAttempt::create(['user_id' => $this->student->id, 'quiz_id' => $noLimit->id, 'started_at' => now()->subDays(3), 'total_points' => 1]);

        $this->artisan('quizzes:close-expired')->expectsOutput('Closed 2 expired quiz attempt(s).')->assertSuccessful();

        $this->assertNotNull($abandoned->fresh()->submitted_at);
        $this->assertSame(0, $abandoned->fresh()->score);                                  // nothing was saved
        $this->assertNotNull($mine->fresh()->submitted_at);
        $this->assertSame(1, $mine->fresh()->score);                                       // the saved answer counted
        $this->assertNull($running->fresh()->submitted_at);                                // time is left
        $this->assertNull($untimed->fresh()->submitted_at);                                // no time limit: never expires

        $this->artisan('quizzes:close-expired')->expectsOutput('Closed 0 expired quiz attempt(s).');   // nothing left to do
    }

    public function test_the_command_is_scheduled_to_run_every_minute(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->filter(fn ($e) => str_contains($e->command, 'quizzes:close-expired'));

        $this->assertCount(1, $events);
        $this->assertSame('* * * * *', $events->first()->expression);
    }

    public function test_the_automatic_submission_note_reads_in_kinyarwanda(): void
    {
        $quiz = $this->quiz(['time_limit_minutes' => 10]);
        $attempt = $this->openAttempt($quiz, 9);
        $this->autosave($attempt, [$this->question->id => $this->right->id]);
        $this->travel(5)->minutes();
        $this->student->forceFill(['locale' => 'rw'])->save();

        $this->actingAs($this->student->fresh())->get(route('quizzes.take', $attempt));

        $this->get(route('quizzes.result', $attempt))->assertSee("Igihe cyarangiye, bityo ibisubizo wari wabitse byoherejwe ako kanya.");
    }
}
