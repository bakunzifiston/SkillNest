<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseLessonAccessTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertSessionHas('error', 'That lesson link is no longer valid for this course. We redirected you to the first lesson.');
    }
}
