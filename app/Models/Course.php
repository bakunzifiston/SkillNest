<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasTranslations;

    /** Fields that can be translated (see HasTranslations). */
    protected array $translatable = ['title', 'description', 'duration'];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'description',
        'image',
        'price',
        'duration',
        'level',
        'status',
        'access_level',
        'sequential_lessons',
        'partner_organization_id',
        'students_count',
    ];

    public const ACCESS_ALL = 'all';

    public const ACCESS_AGENTS = 'agents';

    public const ACCESS_PARTNERS = 'partners';

    public const ACCESS_PARTNER = 'partner';

    /** @return array<string, string> */
    public static function accessLevels(): array
    {
        return [
            self::ACCESS_ALL => __('All learners (Agents and all Partners)'),
            self::ACCESS_AGENTS => __('Agents only'),
            self::ACCESS_PARTNERS => __('All Partners'),
            self::ACCESS_PARTNER => __('Specific Partner organization'),
        ];
    }

    protected $casts = [
        'sequential_lessons' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (blank($course->status)) {
                $course->status = self::STATUS_DRAFT;
            }
        });
    }

    /**
     * Full URL for the course banner image (for use in img src).
     * Served via route so it works even when storage:link is not run.
     */
    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return url('course-image/'.ltrim($this->image, '/'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function partnerOrganization()
    {
        return $this->belongsTo(PartnerOrganization::class);
    }

    public function accessLevelOrAll(): string
    {
        return $this->access_level ?: self::ACCESS_ALL;
    }

    public function accessLabel(): string
    {
        return match ($this->accessLevelOrAll()) {
            self::ACCESS_AGENTS => __('Agents only'),
            self::ACCESS_PARTNERS => __('All Partners'),
            self::ACCESS_PARTNER => __(':name only', ['name' => $this->partnerOrganization?->name ?? __('Partner')]),
            default => __('All learners'),
        };
    }

    /**
     * Is this course in the audience of the given user? Guests only match
     * courses open to everyone. Staff can always preview any course.
     */
    public function isAvailableTo(?User $user): bool
    {
        $level = $this->accessLevelOrAll();

        if ($level === self::ACCESS_ALL) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->canAccessAdmin()) {
            return true;
        }

        return match ($level) {
            self::ACCESS_AGENTS => $user->isAgent(),
            self::ACCESS_PARTNERS => $user->isPartner(),
            self::ACCESS_PARTNER => $user->isPartner()
                && $this->partner_organization_id !== null
                && (int) $user->partner_organization_id === (int) $this->partner_organization_id,
            default => false,
        };
    }

    /**
     * Query string for the Register link on this course's page: partner-only
     * courses open the Partner sign-up (with the organization preselected).
     *
     * @return array<string, mixed>
     */
    public function registerLinkParams(): array
    {
        $params = ['course' => $this->slug];

        if (in_array($this->accessLevelOrAll(), [self::ACCESS_PARTNERS, self::ACCESS_PARTNER], true)) {
            $params['type'] = User::TYPE_PARTNER;
            if ($this->accessLevelOrAll() === self::ACCESS_PARTNER && $this->partner_organization_id) {
                $params['organization'] = $this->partner_organization_id;
            }
        }

        return $params;
    }

    /**
     * Courses assigned to a learner's own category: "Agents only" for Agents;
     * "All Partners" and their own organization's courses for Partners.
     * Courses open to everyone are not "assigned" to anyone in particular.
     */
    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            if ($user->isPartner()) {
                $q->where('access_level', self::ACCESS_PARTNERS);
                if ($user->partner_organization_id) {
                    $q->orWhere(fn (Builder $p) => $p
                        ->where('access_level', self::ACCESS_PARTNER)
                        ->where('partner_organization_id', $user->partner_organization_id));
                }
            } else {
                $q->where('access_level', self::ACCESS_AGENTS);
            }
        });
    }

    /** Every course the learner is allowed to take: those made for their category plus the ones open to all. */
    public function scopeOpenTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('access_level', self::ACCESS_ALL)
                ->orWhere(fn (Builder $assigned) => $assigned->assignedTo($user));
        });
    }

    /** Who the course is for, e.g. "Partners", "Agents" or "SFH". */
    public function audienceName(): string
    {
        return match ($this->accessLevelOrAll()) {
            self::ACCESS_AGENTS => __('Agents'),
            self::ACCESS_PARTNERS => __('Partners'),
            self::ACCESS_PARTNER => $this->partnerOrganization?->name ?? __('Partners'),
            default => __('everyone'),
        };
    }

    /**
     * Warning shown on a restricted course to someone who cannot enroll in it.
     * Null when the viewer may enroll.
     */
    public function audienceWarning(?User $user): ?string
    {
        if ($this->isAvailableTo($user)) {
            return null;
        }

        if ($this->accessLevelOrAll() === self::ACCESS_PARTNER) {
            // The organisation's own name (e.g. "SFH") is never changed.
            $for = __(':audience partner learners only', ['audience' => $this->audienceName()]);
        } else {
            $audience = $this->audienceName();
            // In Kinyarwanda the audience word ("Aba-agent", "Abafatanyabikorwa") comes mid-sentence, so it starts in lower case.
            if (app()->getLocale() !== config('localization.default')) {
                $audience = \Illuminate\Support\Str::lcfirst($audience);
            }
            $for = __(':audience only', ['audience' => $audience]);
        }

        if (! $user) {
            return __('This course is for :for. Log in with an eligible account to enroll.', ['for' => $for]);
        }

        return __('This course is for :for. Your account (:account) can\'t enroll in it.', ['for' => $for, 'account' => $user->learnerCategoryLabel()]);
    }

    /**
     * Learner attributes for accounts created on this course's behalf
     * (e.g. CSV import), so they land inside the course's audience.
     *
     * @return array{learner_type: string, partner_organization_id: int|null}
     */
    public function defaultLearnerAttributes(): array
    {
        if ($this->accessLevelOrAll() === self::ACCESS_PARTNER && $this->partner_organization_id) {
            return [
                'learner_type' => User::TYPE_PARTNER,
                'partner_organization_id' => $this->partner_organization_id,
            ];
        }

        return ['learner_type' => User::TYPE_AGENT, 'partner_organization_id' => null];
    }

    /** Enrolled learners who fall outside the course's current audience. */
    public function enrolledOutsideAudienceCount(): int
    {
        $level = $this->accessLevelOrAll();

        if ($level === self::ACCESS_ALL) {
            return 0;
        }

        return $this->enrollments()->whereHas('user', function (Builder $u) use ($level) {
            $u->students();

            match ($level) {
                self::ACCESS_AGENTS => $u->where('learner_type', '!=', User::TYPE_AGENT),
                self::ACCESS_PARTNERS => $u->where('learner_type', '!=', User::TYPE_PARTNER),
                default => $u->where(fn (Builder $x) => $x
                    ->where('learner_type', '!=', User::TYPE_PARTNER)
                    ->orWhere(fn (Builder $y) => $y
                        ->whereNull('partner_organization_id')
                        ->orWhere('partner_organization_id', '!=', $this->partner_organization_id ?? 0))),
            };
        })->count();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('sort_order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('sort_order');
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class)->orderBy('scheduled_at');
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses')
            ->withPivot('order');
    }

    /** Every lesson in learning order: chapters by their order, then lessons by their order. */
    public function orderedLessons(): \Illuminate\Support\Collection
    {
        $this->loadMissing('chapters.lessons');

        return $this->chapters->sortBy('sort_order')
            ->flatMap(fn ($chapter) => $chapter->lessons->sortBy('sort_order'))
            ->values();
    }

    /**
     * Where a student stands on each lesson: id => completed | in_progress | locked | open.
     *
     * With lessons in order (the default), a lesson is open to the student when it is already completed
     * or the lesson before it is completed (the first lesson is always open). Open but unfinished = in_progress.
     * Everything after the first unfinished lesson is locked. Staff, and courses that turned the rule off,
     * have no locks ("open" = no restriction).
     *
     * @param  iterable<int>  $completedLessonIds
     * @return array<int, string>
     */
    public function lessonStatuses(iterable $completedLessonIds, ?User $viewer = null): array
    {
        $done = collect($completedLessonIds)->map(fn ($id) => (int) $id)->flip();
        $inOrder = $this->sequential_lessons !== false && ! $viewer?->canAccessAdmin();

        $statuses = [];
        $previousDone = true;

        foreach ($this->orderedLessons() as $lesson) {
            $isDone = $done->has($lesson->id);

            $statuses[$lesson->id] = match (true) {
                $isDone => 'completed',
                ! $inOrder => 'open',
                $previousDone => 'in_progress',
                default => 'locked',
            };

            $previousDone = $isDone;
        }

        return $statuses;
    }

    public function totalLessonsCount(): int
    {
        return $this->chapters->sum(fn ($ch) => $ch->lessons->count());
    }
}
