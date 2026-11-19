<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every page with a top banner shares the same look: floating menu, brand-blue banner, curved bottom edge. */
class PageBannersTest extends TestCase
{
    use RefreshDatabase;

    private function course(): Course
    {
        $category = Category::first() ?? Category::create(['name' => 'Design', 'slug' => 'design']);

        return Course::create([
            'category_id' => $category->id, 'title' => 'Banner Course', 'slug' => 'banner-course', 'description' => 'd',
            'price' => 0, 'level' => 'beginner', 'status' => Course::STATUS_PUBLISHED,
        ]);
    }

    private function floating(string $html): bool
    {
        return (bool) preg_match('/<body class="[^"]*\bhas-banner\b/', $html);
    }

    public function test_public_pages_use_the_shared_banner_and_floating_menu(): void
    {
        $course = $this->course();
        $bundle = Bundle::create(['title' => 'Path', 'slug' => 'path', 'description' => 'd', 'status' => Bundle::STATUS_PUBLISHED]);

        foreach ([
            route('courses.index'), route('about'), route('contact'), route('bundles.index'),
            route('bundles.show', $bundle), route('courses.show', $course),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('class="page-banner', $html, "{$url}: shared banner missing");
            $this->assertTrue($this->floating($html), "{$url}: floating menu missing");
            $this->assertStringNotContainsString('<section class="relative overflow-hidden bg-navy text-white">', $html, "{$url}: old banner still there");
        }
    }

    public function test_the_curve_matches_the_colour_of_the_section_below(): void
    {
        // About and Contact are followed by a white section; the others by the light-grey page background.
        foreach ([route('about'), route('contact')] as $url) {
            $this->assertStringContainsString('page-banner page-banner--white', $this->get($url)->getContent());
        }
        foreach ([route('courses.index'), route('bundles.index')] as $url) {
            $html = $this->get($url)->getContent();
            $this->assertStringContainsString('<section class="page-banner">', $html);
            $this->assertStringNotContainsString('page-banner--white">', $html);
        }
    }

    public function test_learner_pages_use_the_shared_banner(): void
    {
        $course = $this->course();
        $learner = User::factory()->create();
        Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id]);

        foreach ([route('dashboard'), route('courses.my-courses'), route('bundles.my-bundles'), route('profile.edit')] as $url) {
            $html = $this->actingAs($learner)->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('class="page-banner', $html, "{$url}: shared banner missing");
            $this->assertTrue($this->floating($html), "{$url}: floating menu missing");
        }
    }

    public function test_the_enrolled_course_page_keeps_the_normal_menu_because_of_its_sidebar(): void
    {
        $course = $this->course();
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Ch', 'sort_order' => 1]);
        Lesson::create(['chapter_id' => $chapter->id, 'title' => 'L', 'type' => 'text', 'content' => 'x', 'sort_order' => 1]);
        $learner = User::factory()->create();
        Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id]);

        $html = $this->actingAs($learner)->get(route('courses.show', $course))->assertOk()->getContent();

        $this->assertStringContainsString('class="page-banner', $html);       // the banner itself is restyled...
        $this->assertFalse($this->floating($html));                          // ...but the menu stays in the normal flow above the sidebar
        $this->assertStringContainsString('site-header bg-white/95 backdrop-blur', $html);
    }

    public function test_lesson_and_quiz_pages_are_unchanged(): void
    {
        $course = $this->course();
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Ch', 'sort_order' => 1]);
        $lesson = Lesson::create(['chapter_id' => $chapter->id, 'title' => 'L', 'type' => 'text', 'content' => 'x', 'sort_order' => 1]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Q', 'passing_grade' => 70, 'is_published' => true]);
        $learner = User::factory()->create();
        Enrollment::create(['user_id' => $learner->id, 'course_id' => $course->id]);

        foreach ([route('courses.lessons.show', [$course, $lesson]), route('courses.quizzes.show', [$course, $quiz])] as $url) {
            $html = $this->actingAs($learner)->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('class="page-banner', $html);
            $this->assertFalse($this->floating($html));
        }
    }

    public function test_banners_work_in_both_languages_with_the_menu_clear_of_the_content(): void
    {
        foreach (['en', 'rw'] as $locale) {
            $this->post(route('locale.update'), ['locale' => $locale]);
            $html = $this->get(route('about'))->assertOk()->getContent();

            $this->assertStringContainsString('<html lang="'.$locale.'"', $html);
            $this->assertStringContainsString('body.has-banner .page-banner > .relative { padding-top: 7.25rem; }', $html);
            $this->assertStringContainsString('.site-nav a { white-space: nowrap; }', $html);
        }
    }

    public function test_pages_that_are_not_banner_pages_keep_the_normal_menu(): void
    {
        foreach ([route('login'), route('register')] as $url) {
            $this->assertFalse($this->floating($this->get($url)->getContent()));
        }
    }

    public function test_the_login_and_register_side_panel_uses_the_banner_blue(): void
    {
        foreach ([route('login'), route('register'), route('password.request')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match('/<aside class="auth-aside[^>]*>/', $html, $m);

            $this->assertNotEmpty($m, "{$url}: side panel missing");
            foreach (['#19499B', '#153F86', '#102C5E'] as $blue) {
                $this->assertStringContainsString($blue, $m[0], "{$url}: banner blue {$blue} missing in the side panel");
            }
            $this->assertStringNotContainsString('bg-navy', $m[0]);
            $this->assertStringContainsString('border-radius: 999px', $html);   // the logo sits on a white pill so it stays visible
        }
    }

    public function test_the_admin_sidebar_uses_the_banner_blue(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (['en', 'rw'] as $locale) {
            $admin->forceFill(['locale' => $locale])->save();
            $html = $this->actingAs($admin)->get(route('admin.courses.index'))->assertOk()->getContent();
            preg_match('/<aside\s+class="admin-sidebar[^>]*>/s', $html, $m);

            $this->assertNotEmpty($m, 'admin sidebar missing');
            foreach (['#19499B', '#153F86', '#102C5E'] as $blue) {
                $this->assertStringContainsString($blue, $m[0], "banner blue {$blue} missing in the admin sidebar");
            }
            $this->assertStringNotContainsString('bg-navy', $m[0]);
            // The current page stays visible on the blue (the old solid-blue tile would have vanished).
            $this->assertStringContainsString('aside.admin-sidebar a[aria-current="page"] { background: rgba(255, 255, 255, .18) !important;', $html);
            // The logo sits on a white pill.
            $this->assertStringContainsString('background: #fff; border-radius: 999px; padding: .35rem .9rem', $html);
        }
    }
}
