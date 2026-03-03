<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        try {
            $categories = Category::orderBy('name')->get();
            $latestCourses = Course::with('category')->latest()->take(6)->get();
        } catch (\Throwable) {
            $categories = collect();
            $latestCourses = collect();
        }

        if ($categories->isEmpty()) {
            $categories = collect([
                (object)['name' => 'Development', 'slug' => 'development', 'icon' => '💻', 'courses_count' => 12],
                (object)['name' => 'Design', 'slug' => 'design', 'icon' => '🎨', 'courses_count' => 8],
                (object)['name' => 'Business', 'slug' => 'business', 'icon' => '📊', 'courses_count' => 10],
                (object)['name' => 'Marketing', 'slug' => 'marketing', 'icon' => '📣', 'courses_count' => 6],
            ]);
        }

        if ($latestCourses->isEmpty()) {
            $latestCourses = collect([
                (object)['id' => 1, 'title' => 'PHP & Laravel Fundamentals', 'description' => 'Build modern web applications with Laravel.', 'duration' => '8 hours', 'price' => 0, 'category' => (object)['name' => 'Development'], 'image' => null],
                (object)['id' => 2, 'title' => 'JavaScript from Zero to Hero', 'description' => 'Master JavaScript and modern frontend tools.', 'duration' => '12 hours', 'price' => 49, 'category' => (object)['name' => 'Development'], 'image' => null],
                (object)['id' => 3, 'title' => 'UI/UX Fundamentals', 'description' => 'Design user-friendly interfaces and experiences.', 'duration' => '6 hours', 'price' => 39, 'category' => (object)['name' => 'Design'], 'image' => null],
            ]);
        }

        try {
            $partners = Partner::orderBy('sort_order')->orderBy('id')->get();
        } catch (\Throwable) {
            $partners = collect();
        }

        return view('home', compact('categories', 'latestCourses', 'partners'));
    }

    public function courses(Request $request): View
    {
        try {
            $query = Course::with('category');
            if ($request->filled('category')) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
            }
            $courses = $query->latest()->paginate(12);
            $categories = Category::orderBy('name')->get();
        } catch (\Throwable) {
            $courses = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $categories = collect();
        }

        return view('courses.index', compact('courses', 'categories'));
    }

    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // TODO: send email or store in DB
        return redirect()->route('contact')->with('success', 'Thanks! We’ve received your message and will get back to you soon.');
    }
}
