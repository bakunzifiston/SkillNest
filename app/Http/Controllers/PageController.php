<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        try {
            $categories = Category::query()
                ->withCount('courses')
                ->orderBy('name')
                ->get();

            $latestCourses = Course::query()
                ->with(['category', 'instructor', 'chapters.lessons'])
                ->latest()
                ->take(6)
                ->get();

            $partners = Partner::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $stats = [
                'learners' => User::students()->count(),
                'courses' => Course::count(),
                'categories' => $categories->count(),
                'enrollments' => Enrollment::count(),
            ];
        } catch (\Throwable) {
            $categories = collect();
            $latestCourses = collect();
            $partners = collect();
            $stats = [
                'learners' => 0,
                'courses' => 0,
                'categories' => 0,
                'enrollments' => 0,
            ];
        }

        return view('home', compact('categories', 'latestCourses', 'partners', 'stats'));
    }

    public function courses(Request $request): View
    {
        try {
            $query = Course::with(['category', 'instructor', 'chapters.lessons']);
            if ($request->filled('category')) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
            }
            $courses = $query->latest()->paginate(12);
            $categories = Category::query()->withCount('courses')->orderBy('name')->get();
        } catch (\Throwable) {
            $courses = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $categories = collect();
        }

        $activeCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->string('category')->toString())
            : null;

        return view('courses.index', compact('courses', 'categories', 'activeCategory'));
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
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        $adminEmails = [config('mail.from.address')];
        if (! empty($adminEmails[0])) {
            try {
                Mail::to($adminEmails)->send(new ContactMessageReceived($contactMessage));
            } catch (\Exception $e) {
                Log::error('Failed to send contact message notification: '.$e->getMessage());
            }
        }

        return redirect()->route('contact')->with('success', 'Thanks! We\'ve received your message and will get back to you soon.');
    }
}
