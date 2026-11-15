<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A partner organization (e.g. SFH) whose learners can be given access to
 * partner-only courses. Not to be confused with App\Models\Partner, which
 * holds the logos shown on the public home page.
 */
class PartnerOrganization extends Model
{
    protected $fillable = ['name', 'slug', 'is_active', 'registration_code'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Unambiguous characters only (no 0/O, 1/I/L) so codes are easy to read out and type. */
    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Does the submitted code match this organization's registration code?
     * Case-insensitive, ignores surrounding spaces. An organization without a
     * code accepts nobody (fails closed).
     */
    public function acceptsRegistrationCode(?string $submitted): bool
    {
        $expected = (string) $this->registration_code;
        $submitted = strtoupper(trim((string) $submitted));

        return $expected !== '' && $submitted !== '' && hash_equals(strtoupper($expected), $submitted);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
