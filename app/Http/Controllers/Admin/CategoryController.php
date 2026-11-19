<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $categoriesQuery = Category::withCount('courses')->orderBy('name');
        if ($search !== '') {
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $categoriesQuery->paginate(15)->withQueryString();

        $all = Category::withCount('courses')->get();
        $totalCategories = $all->count();
        $withCourses = $all->filter(fn ($c) => $c->courses_count > 0)->count();
        $totalCourses = (int) $all->sum('courses_count');
        $avgCourses = $totalCategories > 0 ? round($totalCourses / $totalCategories, 1) : 0;

        $kpis = [
            [
                'label' => __('Categories'),
                'value' => $totalCategories,
                'hint' => __(':count with courses', ['count' => $withCourses]),
                'icon' => 'folder',
                'tone' => 'primary',
            ],
            [
                'label' => __('With courses'),
                'value' => $withCourses,
                'hint' => __(':count empty', ['count' => $totalCategories - $withCourses]),
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => __('Courses linked'),
                'value' => $totalCourses,
                'hint' => __('Across all categories'),
                'icon' => 'book',
                'tone' => 'accent',
            ],
            [
                'label' => __('Avg. per category'),
                'value' => $avgCourses,
                'hint' => __('Courses per category'),
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        return view('admin.categories.index', compact('categories', 'kpis', 'search', 'totalCategories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
        ], Category::translationRules()));
        unset($validated['tr']);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['courses_count'] = 0;
        $category = Category::create($validated);
        $category->saveTranslations($request->input('tr', []));
        return redirect()->route('admin.categories.index')->with('success', __('Category created.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
        ], Category::translationRules()));
        unset($validated['tr']);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $category->update($validated);
        $category->saveTranslations($request->input('tr', []));
        return redirect()->route('admin.categories.index')->with('success', __('Category updated.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', __('Category deleted.'));
    }
}
