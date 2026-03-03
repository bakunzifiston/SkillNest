<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CategoryCourseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryData = [
            ['name' => 'Development', 'slug' => 'development', 'icon' => '💻'],
            ['name' => 'Design', 'slug' => 'design', 'icon' => '🎨'],
            ['name' => 'Business', 'slug' => 'business', 'icon' => '📊'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'icon' => '📣'],
        ];

        foreach ($categoryData as $cat) {
            $category = Category::create(array_merge($cat, ['courses_count' => 0]));
            $titles = $this->courseTitles($category->slug);
            foreach ($titles as $i => $title) {
                Course::create([
                    'category_id' => $category->id,
                    'title' => $title,
                    'slug' => \Illuminate\Support\Str::slug($title) . '-' . $category->id . '-' . ($i + 1),
                    'description' => 'Learn essential skills with hands-on projects and expert instruction. Perfect for beginners and intermediate learners.',
                    'price' => $i === 0 ? 0 : rand(29, 99),
                    'duration' => rand(2, 12) . ' hours',
                    'level' => ['beginner', 'intermediate', 'advanced'][rand(0, 2)],
                    'students_count' => rand(100, 5000),
                ]);
            }
            $category->update(['courses_count' => $category->courses()->count()]);
        }
    }

    private function courseTitles(string $cat): array
    {
        return match ($cat) {
            'development' => ['PHP & Laravel Fundamentals', 'JavaScript from Zero to Hero', 'React and Next.js', 'API Design with Laravel'],
            'design' => ['UI/UX Fundamentals', 'Figma for Designers', 'Design Systems'],
            'business' => ['Project Management', 'Data Analysis with Excel', 'Leadership Basics'],
            'marketing' => ['Digital Marketing 101', 'SEO and Content Strategy'],
            default => ['Introduction to ' . ucfirst($cat)],
        };
    }
}
