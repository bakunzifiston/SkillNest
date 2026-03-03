<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrolledUsersController extends Controller
{
    /**
     * Return enrolled students (id, name, email) for a course. Optional ?search= to filter. Limit 50.
     * Uses course id in URL (admin uses id in dropdown).
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $query = $course->enrollments()->with('user:id,name,email');
        $search = $request->input('search', '');
        if (strlen(trim($search)) >= 2) {
            $term = '%' . trim($search) . '%';
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }
        $users = $query->limit(50)->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->take(50)
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])
            ->values();

        return response()->json($users);
    }
}
