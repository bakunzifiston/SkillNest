<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, list<string>>|null
     */
    protected ?array $permissionMapCache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'phone',
        'national_id',
        'date_of_birth',
        'gender',
        'country',
        'province',
        'district',
        'sector',
        'learner_type',
        'partner_organization_id',
        'password',
        'is_admin',
        'role_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'national_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'national_id' => 'encrypted',
            'date_of_birth' => 'date',
        ];
    }

    public const TYPE_AGENT = 'agent';

    public const TYPE_PARTNER = 'partner';

    public static function learnerTypes(): array
    {
        return [
            self::TYPE_AGENT => __('Agent'),
            self::TYPE_PARTNER => __('Partner'),
        ];
    }

    /** Emails and notifications to this user are written in their language. */
    public function preferredLocale(): string
    {
        $supported = array_keys(config('localization.locales'));

        if (in_array($this->locale, $supported, true)) {
            return $this->locale;
        }

        $initial = config('localization.initial');

        return in_array($initial, $supported, true) ? $initial : config('localization.default');
    }

    public function partnerOrganization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class);
    }

    public function isPartner(): bool
    {
        return $this->learner_type === self::TYPE_PARTNER;
    }

    public function isAgent(): bool
    {
        return ! $this->isPartner();
    }

    /** "Agent" or "Partner (SFH)". */
    public function learnerCategoryLabel(): string
    {
        if ($this->isPartner()) {
            $org = $this->partnerOrganization?->name;

            return $org ? __('Partner (:org)', ['org' => $org]) : __('Partner');
        }

        return __('Agent');
    }

    /**
     * Whether this user may view/open a course: it is in their audience, they
     * are staff, or they were already enrolled before the audience changed.
     */
    public function canAccessCourse(Course $course): bool
    {
        return $course->isAvailableTo($this) || $this->hasEnrolled($course);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('is_admin', false)->whereNull('role_id');
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('is_admin', true)->orWhereNotNull('role_id');
        });
    }

    public function isStaff(): bool
    {
        return (bool) $this->is_admin || $this->role_id !== null;
    }

    public function isStudent(): bool
    {
        return ! $this->isStaff();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin || $this->role?->isSuperAdmin();
    }

    public function isAccountActive(): bool
    {
        if (! array_key_exists('is_active', $this->getAttributes())) {
            return true;
        }

        return (bool) $this->getAttributes()['is_active'];
    }

    public function canAccessAdmin(): bool
    {
        return $this->isAccountActive() && ($this->is_admin || $this->role_id !== null);
    }

    public function hasCustomPermissions(): bool
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->isNotEmpty();
        }

        return $this->permissions()->exists();
    }

    public function hasPermission(string $module, string $action = 'view'): bool
    {
        if (! $this->canAccessAdmin() || ! AdminAccess::isValid($module, $action)) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($action, $this->permissionMap()[$module] ?? [], true);
    }

    /**
     * @return array<string, list<string>>
     */
    public function permissionMap(): array
    {
        if ($this->permissionMapCache !== null) {
            return $this->permissionMapCache;
        }

        if ($this->isSuperAdmin()) {
            return $this->permissionMapCache = AdminAccess::allPermissions();
        }

        $this->loadMissing(['permissions', 'role.permissions']);

        if ($this->permissions->isNotEmpty()) {
            return $this->permissionMapCache = AdminAccess::mapPermissions($this->permissions);
        }

        if ($this->role) {
            return $this->permissionMapCache = $this->role->permissionMap();
        }

        return $this->permissionMapCache = [];
    }

    /**
     * @param  array<string, mixed>  $permissions
     */
    public function syncPermissions(array $permissions): void
    {
        $granted = AdminAccess::sanitize($permissions);

        $this->permissions()->delete();
        $this->permissionMapCache = null;

        $rows = [];

        foreach ($granted as $module => $actions) {
            foreach ($actions as $action) {
                $rows[] = [
                    'module' => $module,
                    'action' => $action,
                ];
            }
        }

        if ($rows !== []) {
            $this->permissions()->createMany($rows);
        }
    }

    public function clearCustomPermissions(): void
    {
        $this->permissions()->delete();
        $this->permissionMapCache = null;
    }

    public function assignRole(?Role $role): void
    {
        $this->role()->associate($role);
        $this->is_admin = $role?->isSuperAdmin() ?? false;
        $this->permissionMapCache = null;
    }

    public function accessLevelLabel(): string
    {
        if (! $this->is_active) {
            return $this->role?->name ? __(':role (inactive)', ['role' => $this->role->name]) : __('Inactive');
        }

        if ($this->isSuperAdmin()) {
            return __('Super Admin');
        }

        if ($this->role) {
            return $this->role->name;
        }

        return __('Student');
    }

    public static function activeSuperAdminCount(?int $exceptId = null): int
    {
        return static::query()
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->where('is_admin', true)
                    ->orWhereHas('role', fn (Builder $role) => $role->where('slug', Role::SUPER_ADMIN));
            })
            ->when($exceptId, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->count();
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $first = trim((string) $user->first_name);
            $last = trim((string) $user->last_name);
            $composed = trim($first.' '.$last);

            if ($composed !== '') {
                $user->name = $composed;
            } elseif (trim((string) $user->name) !== '' && ($user->first_name === null || $user->first_name === '')) {
                // Keep legacy single-name accounts usable until they update their profile.
                $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
                $user->first_name = $parts[0] ?? null;
                $user->last_name = $parts[1] ?? null;
            }
        });
    }

    public function displayFirstName(): string
    {
        if (filled($this->first_name)) {
            return (string) $this->first_name;
        }

        $parts = preg_split('/\s+/', trim((string) $this->name), 2) ?: [];

        return $parts[0] ?? '';
    }

    public function displayLastName(): string
    {
        if (filled($this->last_name)) {
            return (string) $this->last_name;
        }

        $parts = preg_split('/\s+/', trim((string) $this->name), 2) ?: [];

        return $parts[1] ?? '';
    }

    public function displayLocation(): string
    {
        return \App\Support\RwandaLocations::format(
            $this->country,
            $this->province,
            $this->district,
            $this->sector
        );
    }

    /**
     * Send the password reset notification (branded KoraLink Academy email).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function hasEnrolled(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)->exists();
    }

    public function completedLessonsCountForCourse(Course $course): int
    {
        $lessonIds = $course->chapters->pluck('lessons')->flatten()->pluck('id');

        return $this->lessonCompletions()->whereIn('lesson_id', $lessonIds)->count();
    }

    public function bundleEnrollments()
    {
        return $this->hasMany(BundleEnrollment::class);
    }

    public function hasEnrolledBundle(Bundle $bundle): bool
    {
        return $this->bundleEnrollments()->where('bundle_id', $bundle->id)->exists();
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
