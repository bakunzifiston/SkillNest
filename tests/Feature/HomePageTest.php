<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_redesigned_sections_with_real_data(): void
    {
        $category = Category::create([
            'name' => 'Business',
            'slug' => 'business',
        ]);

        Course::create([
            'category_id' => $category->id,
            'title' => 'Marketplace Essentials',
            'slug' => 'marketplace-essentials',
            'description' => 'Learn how to serve customers well.',
            'price' => 0,
            'duration' => '4 hours',
            'level' => 'beginner',
            'status' => Course::STATUS_PUBLISHED,
        ]);

        Partner::create([
            'name' => 'Health Initiative',
            'logo' => 'partners/health.png',
            'sort_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Build Digital Skills', false);
        $response->assertSee('Browse by category', false);
        $response->assertSee('Start Learning Today', false);
        $response->assertSee('How KoraLink Academy Works', false);
        $response->assertSee('Our Partners', false);
        $response->assertSee('Ready to grow your digital skills?', false);
        $response->assertSee('Marketplace Essentials', false);
        $response->assertSee('Business', false);
        $response->assertSee('Health Initiative', false);
        $response->assertDontSee('Trusted by teams everywhere', false);
        $response->assertDontSee('4.9', false);
    }

    public function test_admin_nav_link_is_hidden_from_students(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('My Learning', false)
            ->assertDontSee('>Admin<', false);
    }

    public function test_admin_nav_link_is_visible_to_super_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Admin', false);
    }
}
