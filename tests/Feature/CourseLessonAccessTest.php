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

class CourseLessonAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_open_free_course_lesson_without_manual_enrollment(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
        ]);

        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Web Design',
            'slug' => 'web-design',
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Getting Started',
            'sort_order' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'title' => 'Introduction',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Welcome',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('courses.lessons.show', [$course, $lesson]));

        $response->assertOk();
        $this->assertTrue(
            Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists()
        );
    }

    public function test_mismatched_lesson_url_redirects_to_first_lesson_in_course(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
        ]);

        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Web Design',
            'slug' => 'web-design',
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
        ]);

        $courseChapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Getting Started',
            'sort_order' => 1,
        ]);

        $firstLesson = Lesson::create([
            'chapter_id' => $courseChapter->id,
            'title' => 'Introduction',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Welcome',
            'sort_order' => 1,
        ]);

        $otherCourse = Course::create([
            'category_id' => $category->id,
            'title' => 'Photoshop Basics',
            'slug' => 'photoshop-basics',
            'description' => 'Other course description',
            'price' => 0,
            'level' => 'beginner',
        ]);

        $otherChapter = Chapter::create([
            'course_id' => $otherCourse->id,
            'title' => 'Other Chapter',
            'sort_order' => 1,
        ]);

        $wrongLesson = Lesson::create([
            'chapter_id' => $otherChapter->id,
            'title' => 'Wrong Lesson',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Other content',
            'sort_order' => 1,
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('courses.lessons.show', [$course, $wrongLesson]));

        $response->assertRedirect(route('courses.lessons.show', [$course, $firstLesson], false));
    }

    public function test_lesson_completion_can_be_recorded_via_json_for_auto_progress(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Web Design',
            'slug' => 'web-design',
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
        ]);
        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Chapter 1',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'title' => 'Intro',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Hello',
            'sort_order' => 1,
        ]);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('lessons.complete', $lesson));

        $response->assertOk()
            ->assertJson([
                'completed' => true,
                'completed_count' => 1,
                'total_lessons' => 1,
            ]);

        $this->assertTrue(
            LessonCompletion::where('user_id', $user->id)->where('lesson_id', $lesson->id)->exists()
        );
    }

    public function test_lesson_page_shows_complete_button_and_auto_tracking(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Web Design',
            'slug' => 'web-design',
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
        ]);
        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Chapter 1',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'title' => 'Intro',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Hello',
            'sort_order' => 1,
        ]);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $response = $this
            ->actingAs($user)
            ->get(route('courses.lessons.show', [$course, $lesson]));

        $response->assertOk();
        $response->assertSee('Mark as complete', false);
        $response->assertSee('data-lesson-progress', false);
        $response->assertSee('data-lesson-complete-btn', false);
    }

    public function test_completed_lesson_shows_completed_state_not_button(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Web Design',
            'slug' => 'web-design',
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
        ]);
        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Chapter 1',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'title' => 'Intro',
            'type' => Lesson::TYPE_TEXT,
            'content' => 'Hello',
            'sort_order' => 1,
        ]);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        LessonCompletion::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('courses.lessons.show', [$course, $lesson]));

        $response->assertOk();
        $response->assertSee('data-lesson-complete-done', false);
        $response->assertDontSee('Mark as complete', false);
    }
}
