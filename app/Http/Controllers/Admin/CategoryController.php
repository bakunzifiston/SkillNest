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
                'label' => 'Categories',
                'value' => $totalCategories,
                'hint' => $withCourses.' with courses',
                'icon' => 'folder',
                'tone' => 'primary',
            ],
            [
                'label' => 'With courses',
                'value' => $withCourses,
                'hint' => ($totalCategories - $withCourses).' empty',
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Courses linked',
                'value' => $totalCourses,
                'hint' => 'Across all categories',
                'icon' => 'book',
                'tone' => 'accent',
            ],
            [
                'label' => 'Avg. per category',
                'value' => $avgCourses,
                'hint' => 'Courses per category',
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['courses_count'] = 0;
        Category::create($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
