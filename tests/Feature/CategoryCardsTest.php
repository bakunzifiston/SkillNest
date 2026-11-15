<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Support\CategoryTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCardsTest extends TestCase
{
    use RefreshDatabase;

    private function luminance(string $hex): float
    {
        $rgb = array_map(
            fn ($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4,
            sscanf(ltrim($hex, '#'), '%02x%02x%02x')
        );

        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }

    private function contrast(string $a, string $b): float
    {
        [$l1, $l2] = [$this->luminance($a), $this->luminance($b)];

        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    public function test_theme_text_and_icon_are_readable(): void
    {
        $t = CategoryTheme::theme();

        $this->assertGreaterThanOrEqual(4.5, $this->contrast($t['text'], $t['light']), 'label text on card tint');
        $this->assertGreaterThanOrEqual(3.0, $this->contrast('#FFFFFF', $t['base']), 'white icon on solid colour');
    }

    public function test_all_category_cards_use_the_same_blue_theme(): void
    {
        foreach (['Development', 'Health', 'Marketing', 'Design', 'Business', 'Something New'] as $name) {
            Category::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name)]);
        }

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(6, substr_count($html, 'class="cat-card group"'));
        $this->assertSame(6, substr_count($html, '--c:#19499B'));
        foreach (['#D6336C', '#F16029;--bg', '#6D4AC4', '#B87A00', '#1D9E75', '#0E8FA6'] as $otherColour) {
            $this->assertStringNotContainsString('--c:'.$otherColour, $html);
        }
    }

    public function test_how_it_works_cards_use_the_brand_orange(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(4, substr_count($html, '<li class="step-card">'));
        $this->assertStringContainsString('--c: #F16029', $html);
        $this->assertStringContainsString('How KoraLink Academy Works', $html);

        // White step number on the orange badge stays legible (large bold text needs 3:1).
        $this->assertGreaterThanOrEqual(3.0, $this->contrast('#FFFFFF', '#F16029'));
        // Hover/label colour readable on the orange tint.
        $this->assertGreaterThanOrEqual(4.5, $this->contrast('#B8461A', '#FEF0EA'));
    }

    public function test_impact_numbers_are_neutral_cards_without_blue(): void
    {
        $category = Category::create(['name' => 'Design', 'slug' => 'design']);
        $course = Course::create([
            'category_id' => $category->id, 'title' => 'C', 'slug' => 'c', 'description' => 'd',
            'price' => 0, 'level' => 'beginner', 'status' => Course::STATUS_PUBLISHED,
        ]);
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(4, substr_count($html, '<div class="stat-card">'));
        foreach (['Learners', 'Courses', 'Categories', 'Enrollments'] as $label) {
            $this->assertStringContainsString('<p class="stat-label">'.$label.'</p>', $html);
        }
        $this->assertSame(4, substr_count($html, 'class="stat-num font-display tabular-nums"'));

        // No blue: neutral card CSS, and the band behind is light grey, not the light-blue brand tint.
        $css = substr($html, strpos($html, '.stat-card {'), 900);
        $css = substr($css, 0, strpos($css, '</style>'));
        foreach (['#19499B', '#DCE6F5', '#E8EEF7', '#C9D6EE', '#102C5E', 'primary'] as $blue) {
            $this->assertStringNotContainsString($blue, $css, "stat styles must not use {$blue}");
        }
        $this->assertStringContainsString('<section class="py-14 lg:py-16 bg-slate-50">', $html);

        // Charcoal number and slate label stay readable on white.
        $this->assertGreaterThanOrEqual(7.0, $this->contrast('#1F2937', '#FFFFFF'));
        $this->assertGreaterThanOrEqual(4.5, $this->contrast('#475569', '#FFFFFF'));
    }
}
