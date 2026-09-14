<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'password',
        'is_admin',
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
        ];
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
