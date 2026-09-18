<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $roleFilter = (string) $request->get('role', 'all');
        $statusFilter = (string) $request->get('status', 'all');

        $users = User::query()
            ->with('role')
            ->withCount('enrollments')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter === 'student', fn ($q) => $q->students())
            ->when($roleFilter === 'staff', fn ($q) => $q->staff())
            ->when(is_numeric($roleFilter), fn ($q) => $q->where('role_id', (int) $roleFilter))
            ->when($statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $totalUsers = User::count();
        $students = User::students()->count();
        $staff = User::staff()->count();
        $inactive = User::query()->where('is_active', false)->count();
        $activeRecently = User::query()
            ->whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        $kpis = [
            [
                'label' => 'Users',
                'value' => $totalUsers,
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Students',
                'value' => $students,
                'icon' => 'user',
                'tone' => 'success',
            ],
            [
                'label' => 'Staff',
                'value' => $staff,
                'icon' => 'shield',
                'tone' => 'accent',
            ],
            [
                'label' => $inactive > 0 ? 'Inactive' : 'Active (30d)',
                'value' => $inactive > 0 ? $inactive : $activeRecently,
                'icon' => 'pulse',
                'tone' => 'slate',
            ],
        ];

        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.index', compact(
            'users',
            'kpis',
            'search',
            'roleFilter',
            'statusFilter',
            'roles'
        ));
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $role = $this->resolveRole($request, $data['role_id'] ?? null);

        $user = new User;
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->is_active = $data['is_active'];
        $user->email_verified_at = now();
        $user->assignRole($role);
        $user->save();

        $this->syncUserPermissions($request, $user);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User created.');
    }

    public function show(Request $request, User $user): View
    {
        $user->load(['role.permissions', 'permissions']);

        $search = trim((string) $request->get('search', ''));
        $filter = $request->get('filter', 'all');

        $courses = Course::with(['chapters.lessons', 'category'])
            ->orderBy('title')
            ->get();

        $enrollmentCourseIds = $user->enrollments()->pluck('course_id');
        $completedByLesson = $user->lessonCompletions()->pluck('lesson_id');

        $courseProgress = $courses->map(function (Course $course) use ($enrollmentCourseIds, $completedByLesson) {
            $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
            $lessonIds = $course->chapters->flatMap->lessons->pluck('id');
            $completed = $completedByLesson->intersect($lessonIds)->count();
            $percent = $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0;
            $enrolled = $enrollmentCourseIds->contains($course->id);

            return (object) [
                'course' => $course,
                'enrolled' => $enrolled,
                'total_lessons' => $totalLessons,
                'completed' => $completed,
                'percent' => $percent,
                'status' => ! $enrolled
                    ? 'not_enrolled'
                    : ($percent >= 100 ? 'completed' : ($percent > 0 ? 'in_progress' : 'not_started')),
            ];
        });

        $enrolledRows = $courseProgress->where('enrolled', true);
        $completedCourses = $enrolledRows->where('status', 'completed')->count();
        $inProgressCourses = $enrolledRows->where('status', 'in_progress')->count();
        $avgProgress = $enrolledRows->count() > 0
            ? round((float) $enrolledRows->avg('percent'), 1)
            : 0;

        $kpis = [
            [
                'label' => 'Enrolled',
                'value' => $enrolledRows->count(),
                'icon' => 'book',
                'tone' => 'primary',
            ],
            [
                'label' => 'Completed',
                'value' => $completedCourses,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'In progress',
                'value' => $inProgressCourses,
                'icon' => 'pulse',
                'tone' => 'accent',
            ],
            [
                'label' => 'Avg. progress',
                'value' => $avgProgress,
                'suffix' => '%',
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        $rows = $courseProgress;
        if ($filter === 'enrolled') {
            $rows = $rows->where('enrolled', true)->values();
        }
        if ($search !== '') {
            $rows = $rows->filter(function ($row) use ($search) {
                $haystack = strtolower(($row->course->title ?? '').' '.($row->course->category->name ?? ''));

                return str_contains($haystack, strtolower($search));
            })->values();
        }

        return view('admin.users.show', compact('user', 'rows', 'kpis', 'search', 'filter'));
    }

    public function edit(User $user): View
    {
        $user->load(['role.permissions', 'permissions']);

        return view('admin.users.edit', array_merge($this->formData($user), compact('user')));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $role = $this->resolveRole($request, $data['role_id'] ?? null);

        $this->guardPrivilegedChange($request->user(), $user, $role, (bool) $data['is_active']);

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = $data['email'];
        $user->is_active = $data['is_active'];
        $user->assignRole($role);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $this->syncUserPermissions($request, $user);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->guardPrivilegedChange($request->user(), $user, $user->role, ! $user->is_active);

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::activeSuperAdminCount($user->id) === 0) {
            return back()->with('error', 'The last Super Admin cannot be deleted.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $user = null): array
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        $rolePermissionMap = $roles->mapWithKeys(fn (Role $role) => [
            (string) $role->id => $role->permissionMap(),
        ])->all();

        $selectedPermissions = $user?->hasCustomPermissions()
            ? $user->permissionMap()
            : ($user?->role?->permissionMap() ?? []);

        return [
            'roles' => $roles,
            'modules' => AdminAccess::modules(),
            'groups' => config('admin-modules.groups', []),
            'rolePermissionMap' => $rolePermissionMap,
            'selectedPermissions' => $selectedPermissions,
            'customizePermissions' => (bool) $user?->hasCustomPermissions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user): array
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
            'customize_permissions' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['role_id'] = $data['role_id'] ?? null;

        return $data;
    }

    private function resolveRole(Request $request, mixed $roleId): ?Role
    {
        if (! $roleId) {
            return null;
        }

        $role = Role::query()->findOrFail((int) $roleId);

        if ($role->isSuperAdmin() && ! $request->user()?->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role_id' => 'Only a Super Admin can assign the Super Admin role.',
            ]);
        }

        return $role;
    }

    private function syncUserPermissions(Request $request, User $user): void
    {
        if ($user->isSuperAdmin() || ! $request->boolean('customize_permissions')) {
            $user->clearCustomPermissions();

            return;
        }

        if (! $user->role_id) {
            $user->clearCustomPermissions();

            return;
        }

        $user->syncPermissions($request->input('permissions', []));
    }

    private function guardPrivilegedChange(?User $actor, User $target, ?Role $newRole, bool $willBeActive): void
    {
        if ($target->is($actor) && ! $willBeActive) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        $willRemainSuper = $willBeActive && $newRole?->isSuperAdmin();

        if ($target->isSuperAdmin() && ! $willRemainSuper && User::activeSuperAdminCount($target->id) === 0) {
            throw ValidationException::withMessages([
                'role_id' => 'The last Super Admin cannot be demoted or deactivated.',
            ]);
        }
    }
}
