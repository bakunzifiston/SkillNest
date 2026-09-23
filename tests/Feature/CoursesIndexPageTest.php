<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesIndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_index_shows_cleaned_catalog_ui(): void
    {
        $category = Category::create([
            'name' => 'Design',
            'slug' => 'design',
        ]);

        Course::create([
            'category_id' => $category->id,
            'title' => 'UI Foundations',
            'slug' => 'ui-foundations',
            'description' => 'Learn practical interface skills.',
            'price' => 0,
            'duration' => '3 hours',
            'level' => 'beginner',
        ]);

        $response = $this->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee('All Courses', false);
        $response->assertSee('Filter by category', false);
        $response->assertSee('UI Foundations', false);
        $response->assertSee('Design', false);
        $response->assertSee('View course', false);
        $response->assertDontSee('📖', false);
    }

    public function test_courses_index_can_filter_by_category(): void
    {
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);
        $business = Category::create(['name' => 'Business', 'slug' => 'business']);

        Course::create([
            'category_id' => $design->id,
            'title' => 'Design Course',
            'slug' => 'design-course',
            'description' => 'Design description',
            'price' => 0,
            'level' => 'beginner',
        ]);

        Course::create([
            'category_id' => $business->id,
            'title' => 'Business Course',
            'slug' => 'business-course',
            'description' => 'Business description',
            'price' => 0,
            'level' => 'beginner',
        ]);

        $response = $this->get(route('courses.index', ['category' => 'design']));

        $response->assertOk();
        $response->assertSee('Design Course', false);
        $response->assertDontSee('Business Course', false);
    }
}
