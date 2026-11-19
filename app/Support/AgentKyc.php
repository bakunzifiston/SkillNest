<?php

namespace App\Support;

/**
 * Optional KYC details collected from Agents (at registration and on the profile page).
 */
class AgentKyc
{
    public const FIELDS = ['phone', 'national_id', 'date_of_birth', 'gender'];

    /** @return array<string, string> */
    public static function genders(): array
    {
        return [
            'male' => __('Male'),
            'female' => __('Female'),
            'other' => __('Other'),
            'prefer_not_to_say' => __('Prefer not to say'),
        ];
    }

    /**
     * Every field is optional.
     *
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-()]{6,19}$/'],
            'national_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{6,32}$/'],
            'date_of_birth' => ['nullable', 'date', 'after:1900-01-01', 'before:today'],
            'gender' => ['nullable', 'in:'.implode(',', array_keys(self::genders()))],
        ];
    }

    public static function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid phone number, e.g. +250 788 000 000.'),
            'national_id.regex' => __('The national ID number may contain only letters and digits (6 to 32 characters).'),
            'date_of_birth.before' => __('Date of birth must be in the past.'),
        ];
    }

    /**
     * Strip spaces/hyphens from the ID number and trim text before validating.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        $out = [];

        foreach (self::FIELDS as $field) {
            if (! array_key_exists($field, $input)) {
                continue;
            }
            $value = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            if ($field === 'national_id' && is_string($value)) {
                $value = preg_replace('/[\s\-]+/', '', $value);
            }
            $out[$field] = $value === '' ? null : $value;
        }

        return $out;
    }

    /**
     * Pick the KYC fields out of validated data.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function only(array $validated): array
    {
        return array_intersect_key($validated, array_flip(self::FIELDS));
    }
}
