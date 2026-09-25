<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourse(array $overrides = []): Course
    {
        $category = Category::query()->first() ?? Category::create([
            'name' => 'Design',
            'slug' => 'design',
        ]);

        return Course::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Status Course',
            'slug' => 'status-course-'.uniqid(),
            'description' => 'Course description',
            'price' => 0,
            'level' => 'beginner',
            'status' => Course::STATUS_PUBLISHED,
        ], $overrides));
    }

    public function test_new_course_defaults_to_draft_when_status_omitted(): void
    {
        $course = $this->makeCourse([
            'status' => null,
            'title' => 'Untitled Draft',
            'slug' => 'untitled-draft',
        ]);

        $this->assertSame(Course::STATUS_DRAFT, $course->fresh()->status);
        $this->assertTrue($course->fresh()->isDraft());
    }

    public function test_draft_course_is_hidden_from_public_catalog_and_home(): void
    {
        $draft = $this->makeCourse([
            'title' => 'Secret Draft',
            'slug' => 'secret-draft',
            'status' => Course::STATUS_DRAFT,
        ]);
        $published = $this->makeCourse([
            'title' => 'Live Course',
            'slug' => 'live-course',
            'status' => Course::STATUS_PUBLISHED,
        ]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Live Course', false)
            ->assertDontSee('Secret Draft', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Live Course', false)
            ->assertDontSee('Secret Draft', false);

        $this->get(route('courses.show', $draft))->assertNotFound();
        $this->get(route('courses.show', $published))->assertOk();
    }

    public function test_admin_can_preview_draft_course_on_public_url(): void
    {
        $admin = User::factory()->admin()->create();
        $draft = $this->makeCourse([
            'title' => 'Admin Preview',
            'slug' => 'admin-preview',
            'status' => Course::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->get(route('courses.show', $draft))
            ->assertOk()
            ->assertSee('Admin Preview', false);
    }

    public function test_admin_can_toggle_course_status_from_list(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->makeCourse([
            'title' => 'Toggle Me',
            'slug' => 'toggle-me',
            'status' => Course::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.courses.index'))
            ->patch(route('admin.courses.status', $course), [
                'status' => Course::STATUS_PUBLISHED,
            ])
            ->assertRedirect();

        $this->assertSame(Course::STATUS_PUBLISHED, $course->fresh()->status);

        $this->get(route('courses.show', $course))->assertOk();
    }

    public function test_admin_course_index_shows_status_badges(): void
    {
        $admin = User::factory()->admin()->create();
        $this->makeCourse([
            'title' => 'Published Badge',
            'slug' => 'published-badge',
            'status' => Course::STATUS_PUBLISHED,
        ]);
        $this->makeCourse([
            'title' => 'Draft Badge',
            'slug' => 'draft-badge',
            'status' => Course::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('Published', false)
            ->assertSee('Draft', false)
            ->assertSee('Published Badge', false)
            ->assertSee('Draft Badge', false);
    }
}
