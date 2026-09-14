<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Generic course progress CSV importer.
 *
 * Works for any compatible progress CSV export + any course selected at import time.
 * Progress is mapped from "% Completed" (and optional per-content columns)
 * onto the selected course's lessons in sort order.
 */
class CourseProgressCsvImporter
{
    /** Canonical required headers (progress report). */
    public const REQUIRED_HEADERS = [
        'First Name',
        'Last Name',
        'Email',
        '% Completed',
    ];

    /** Optional metadata columns that are never treated as lesson/content progress. */
    public const METADATA_HEADERS = [
        'First Name',
        'Last Name',
        'Email',
        'Company',
        'Completed At',
        'Started At',
        'Activated At',
        'Expires At',
        'Last Sign In',
        '% Viewed',
        '% Completed',
    ];

    /**
     * Parse a progress CSV into normalized row arrays.
     *
     * @return array{
     *     rows: list<array<string, mixed>>,
     *     content_columns: list<string>,
     *     headers: list<string>
     * }
     */
    public function parse(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            throw new RuntimeException('CSV file is not readable.');
        }

        $handle = fopen($absolutePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        try {
            $rawHeaders = fgetcsv($handle);
            if ($rawHeaders === false || $rawHeaders === [null] || $rawHeaders === []) {
                throw new InvalidArgumentException('CSV file is empty.');
            }

            $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rawHeaders);
            $headerMap = $this->buildHeaderMap($headers);
            $this->assertRequiredHeaders($headerMap);

            $contentColumns = $this->detectContentColumns($headers);
            $rows = [];
            $line = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $line++;
                if ($this->isEmptyCsvRow($data)) {
                    continue;
                }

                $assoc = [];
                foreach ($headers as $i => $header) {
                    $assoc[$header] = isset($data[$i]) ? trim((string) $data[$i]) : '';
                }

                $emailRaw = $this->valueByAliases($assoc, $headerMap, ['Email']);
                $email = Str::lower(trim($emailRaw));
                $firstName = trim($this->valueByAliases($assoc, $headerMap, ['First Name']));
                $lastName = trim($this->valueByAliases($assoc, $headerMap, ['Last Name']));
                $name = trim($firstName.' '.$lastName);

                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rows[] = [
                        'email' => $email,
                        'name' => $name,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'percent_completed' => 0,
                        'percent_viewed' => 0,
                        'activated_at' => null,
                        'started_at' => null,
                        'completed_at' => null,
                        'last_sign_in' => null,
                        'content_progress' => [],
                        'raw' => $assoc,
                        'error' => $email === '' ? 'Missing email' : 'Invalid email',
                        'line' => $line,
                    ];
                    continue;
                }

                $contentProgress = $this->extractContentProgress($assoc, $contentColumns);
                $percentCompleted = $this->resolvePercentCompleted($assoc, $headerMap, $contentProgress);
                $percentViewed = (int) round((float) $this->valueByAliases($assoc, $headerMap, ['% Viewed']));

                $rows[] = [
                    'email' => $email,
                    'name' => $name !== '' ? $name : $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'percent_completed' => $percentCompleted,
                    'percent_viewed' => max(0, min(100, $percentViewed)),
                    'activated_at' => $this->parseDate($this->valueByAliases($assoc, $headerMap, ['Activated At'])),
                    'started_at' => $this->parseDate($this->valueByAliases($assoc, $headerMap, ['Started At'])),
                    'completed_at' => $this->parseDate($this->valueByAliases($assoc, $headerMap, ['Completed At'])),
                    'last_sign_in' => $this->parseDate($this->valueByAliases($assoc, $headerMap, ['Last Sign In'])),
                    'content_progress' => $contentProgress,
                    'raw' => $assoc,
                    'error' => null,
                    'line' => $line,
                ];
            }

            return [
                'rows' => $rows,
                'content_columns' => $contentColumns,
                'headers' => $headers,
            ];
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $contentColumns
     * @return list<array<string, mixed>>
     */
    public function preview(array $rows, Course $course, array $contentColumns = []): array
    {
        $course->loadMissing(['chapters.lessons']);
        $lessons = $this->orderedLessons($course);
        $totalLessons = $lessons->count();

        $emails = collect($rows)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        $existingUsers = User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy(fn (User $u) => Str::lower($u->email));

        $enrolledUserIds = Enrollment::query()
            ->where('course_id', $course->id)
            ->whereIn('user_id', $existingUsers->pluck('id'))
            ->pluck('user_id')
            ->all();

        $preview = [];
        foreach ($rows as $row) {
            if (! empty($row['error'])) {
                $preview[] = [
                    ...$row,
                    'action' => 'skip',
                    'action_label' => 'Skip (invalid)',
                    'progress_note' => '—',
                    'lessons_to_complete' => 0,
                ];
                continue;
            }

            $user = $existingUsers->get($row['email']);
            $lessonsToComplete = $this->lessonsToCompleteCount((int) $row['percent_completed'], $totalLessons);

            if (! $user) {
                $action = 'create';
                $actionLabel = 'Create user + enroll';
            } elseif (in_array($user->id, $enrolledUserIds, true)) {
                $action = 'already_enrolled';
                $actionLabel = 'Already enrolled (update progress)';
            } else {
                $action = 'enroll_existing';
                $actionLabel = 'Enroll existing user';
            }

            $progressNote = $totalLessons === 0
                ? 'Target course has no lessons — enroll only'
                : sprintf(
                    'Mark %d of %d lessons complete (%d%%)',
                    $lessonsToComplete,
                    $totalLessons,
                    (int) $row['percent_completed']
                );

            if ($contentColumns !== [] && ! empty($row['content_progress'])) {
                $done = collect($row['content_progress'])->filter(fn ($v) => (int) $v >= 100)->count();
                $progressNote .= sprintf(' · %d/%d content items at 100%%', $done, count($contentColumns));
            }

            $preview[] = [
                ...$row,
                'action' => $action,
                'action_label' => $actionLabel,
                'progress_note' => $progressNote,
                'lessons_to_complete' => $lessonsToComplete,
            ];
        }

        return $preview;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{
     *     created_users: int,
     *     existing_users: int,
     *     enrollments_created: int,
     *     enrollments_skipped: int,
     *     lessons_completed: int,
     *     skipped_rows: int,
     *     errors: list<string>,
     *     course_id: int,
     *     course_title: string
     * }
     */
    public function import(array $rows, Course $course): array
    {
        $course->loadMissing(['chapters.lessons']);
        $lessons = $this->orderedLessons($course);
        $totalLessons = $lessons->count();

        $summary = [
            'created_users' => 0,
            'existing_users' => 0,
            'enrollments_created' => 0,
            'enrollments_skipped' => 0,
            'lessons_completed' => 0,
            'skipped_rows' => 0,
            'errors' => [],
            'course_id' => $course->id,
            'course_title' => $course->title,
        ];

        foreach ($rows as $row) {
            if (! empty($row['error']) || empty($row['email'])) {
                $summary['skipped_rows']++;
                if (! empty($row['error'])) {
                    $summary['errors'][] = 'Line '.($row['line'] ?? '?').': '.$row['error'];
                }
                continue;
            }

            try {
                DB::transaction(function () use ($row, $course, $lessons, $totalLessons, &$summary) {
                    $user = User::query()->where('email', $row['email'])->first();

                    if ($user) {
                        $summary['existing_users']++;
                        if (! empty($row['last_sign_in']) && empty($user->last_login_at)) {
                            $user->forceFill(['last_login_at' => $row['last_sign_in']])->save();
                        }
                    } else {
                        $user = new User;
                        $user->forceFill([
                            'first_name' => $row['first_name'] !== '' ? $row['first_name'] : null,
                            'last_name' => $row['last_name'] !== '' ? $row['last_name'] : null,
                            'name' => $row['name'] !== '' ? $row['name'] : $row['email'],
                            'email' => $row['email'],
                            'password' => Str::password(32),
                            'is_admin' => false,
                            'email_verified_at' => now(),
                            'last_login_at' => $row['last_sign_in'],
                        ])->save();
                        $summary['created_users']++;
                    }

                    $enrolledAt = $row['activated_at']
                        ?? $row['started_at']
                        ?? $row['completed_at']
                        ?? now();

                    $enrollment = Enrollment::query()->firstOrNew([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                    ]);

                    if (! $enrollment->exists) {
                        $enrollment->created_at = $enrolledAt;
                        $enrollment->updated_at = $enrolledAt;
                        $enrollment->save();
                        $summary['enrollments_created']++;
                    } else {
                        $summary['enrollments_skipped']++;
                    }

                    $count = $this->lessonsToCompleteCount((int) $row['percent_completed'], $totalLessons);
                    if ($count === 0 || $totalLessons === 0) {
                        return;
                    }

                    $completedAt = $row['completed_at']
                        ?? $row['started_at']
                        ?? $row['activated_at']
                        ?? now();

                    foreach ($lessons->take($count) as $lesson) {
                        $completion = LessonCompletion::query()->firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'lesson_id' => $lesson->id,
                            ],
                            [
                                'completed_at' => $completedAt,
                            ]
                        );

                        if ($completion->wasRecentlyCreated) {
                            $summary['lessons_completed']++;
                        }
                    }
                });
            } catch (\Throwable $e) {
                $summary['errors'][] = 'Line '.($row['line'] ?? '?').' ('.$row['email'].'): '.$e->getMessage();
            }
        }

        $course->update([
            'students_count' => Enrollment::query()->where('course_id', $course->id)->count(),
        ]);

        return $summary;
    }

    protected function normalizeHeader(string $header): string
    {
        // Strip UTF-8 BOM and surrounding whitespace from CSV exports.
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

        return trim($header);
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, string> lowercase alias => actual header
     */
    protected function buildHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $header) {
            $map[Str::lower($header)] = $header;
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $headerMap
     */
    protected function assertRequiredHeaders(array $headerMap): void
    {
        $missing = [];
        foreach (self::REQUIRED_HEADERS as $required) {
            if (! isset($headerMap[Str::lower($required)])) {
                $missing[] = $required;
            }
        }

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'CSV is missing required columns: '.implode(', ', $missing).'. Export a course progress report and try again.'
            );
        }
    }

    /**
     * Any non-metadata column is treated as chapter/lesson/content progress.
     *
     * @param  list<string>  $headers
     * @return list<string>
     */
    protected function detectContentColumns(array $headers): array
    {
        $metadata = array_map(fn ($h) => Str::lower($h), self::METADATA_HEADERS);

        return array_values(array_filter(
            $headers,
            fn ($header) => $header !== '' && ! in_array(Str::lower($header), $metadata, true)
        ));
    }

    /**
     * @param  array<string, string>  $assoc
     * @param  array<string, string>  $headerMap
     * @param  list<string>  $aliases
     */
    protected function valueByAliases(array $assoc, array $headerMap, array $aliases): string
    {
        foreach ($aliases as $alias) {
            $key = $headerMap[Str::lower($alias)] ?? null;
            if ($key !== null && array_key_exists($key, $assoc)) {
                return (string) $assoc[$key];
            }
        }

        return '';
    }

    /**
     * @param  array<int, mixed>  $data
     */
    protected function isEmptyCsvRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $assoc
     * @param  list<string>  $contentColumns
     * @return array<string, int>
     */
    protected function extractContentProgress(array $assoc, array $contentColumns): array
    {
        $progress = [];
        foreach ($contentColumns as $column) {
            $raw = $assoc[$column] ?? '';
            if ($raw === '' || ! is_numeric($raw)) {
                $progress[$column] = 0;
                continue;
            }
            $progress[$column] = max(0, min(100, (int) round((float) $raw)));
        }

        return $progress;
    }

    /**
     * Prefer "% Completed". If missing/zero but content columns exist, use their average.
     *
     * @param  array<string, string>  $assoc
     * @param  array<string, string>  $headerMap
     * @param  array<string, int>  $contentProgress
     */
    protected function resolvePercentCompleted(array $assoc, array $headerMap, array $contentProgress): int
    {
        $percentRaw = $this->valueByAliases($assoc, $headerMap, ['% Completed']);
        $percent = $percentRaw !== '' && is_numeric($percentRaw)
            ? (int) round((float) $percentRaw)
            : null;

        if ($percent === null && $contentProgress !== []) {
            $percent = (int) round(array_sum($contentProgress) / max(1, count($contentProgress)));
        }

        $percent = $percent ?? 0;

        // If overall % is incomplete but every content item is done, treat as complete.
        if ($percent < 100 && $contentProgress !== [] && min($contentProgress) >= 100) {
            $percent = 100;
        }

        // Single-item courses often put 100 only in the content column.
        if ($percent < 100 && $contentProgress !== [] && max($contentProgress) >= 100 && count($contentProgress) === 1) {
            $percent = 100;
        }

        return max(0, min(100, $percent));
    }

    protected function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function orderedLessons(Course $course): Collection
    {
        return $course->chapters
            ->sortBy('sort_order')
            ->flatMap(fn ($chapter) => $chapter->lessons->sortBy('sort_order')->values())
            ->values();
    }

    protected function lessonsToCompleteCount(int $percentCompleted, int $totalLessons): int
    {
        if ($totalLessons <= 0 || $percentCompleted <= 0) {
            return 0;
        }

        if ($percentCompleted >= 100) {
            return $totalLessons;
        }

        return (int) max(0, min($totalLessons, (int) round(($percentCompleted / 100) * $totalLessons)));
    }
}
