<?php

namespace App\Models\Concerns;

/**
 * Optional translations for a model's public text.
 *
 * The normal columns (title, description, ...) always hold the English original, so nothing
 * existing changes. Other languages live in the `translations` JSON column:
 *     {"rw": {"title": "...", "description": "..."}}
 *
 * Use $model->tr('title') wherever text is shown to learners. It returns the translation for the
 * current language, and falls back to the English original when there is none, so content never
 * has to be translated.
 *
 * The model declares:  protected array $translatable = ['title', 'description'];
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(['translations' => 'array']);
        $this->mergeFillable(['translations']);
    }

    /** Validation rules for the `tr` array posted by the admin translation fields. @return array<string, list<string>> */
    public static function translationRules(): array
    {
        return [
            'tr' => ['nullable', 'array'],
            'tr.*' => ['nullable', 'array'],
            'tr.*.*' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /** @return list<string> */
    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }

    /** Text for $field in $locale (default: current language), or the English original. */
    public function tr(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();

        if ($locale !== config('localization.default')) {
            $value = $this->translationFor($field, $locale);
            if ($value !== null) {
                return $value;
            }
        }

        return $this->getAttribute($field);
    }

    /** The stored translation, or null when none (empty strings count as none). */
    public function translationFor(string $field, string $locale): ?string
    {
        $value = $this->translations[$locale][$field] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    public function isTranslatedTo(string $locale): bool
    {
        foreach ($this->translatableFields() as $field) {
            if ($this->translationFor($field, $locale) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save translations from the admin form: ['rw' => ['title' => '...', ...]].
     * Only known languages (other than the default) and declared fields are kept; blanks are removed.
     *
     * @param  array<string, mixed>  $input
     */
    public function saveTranslations(array $input): void
    {
        $locales = array_diff(array_keys(config('localization.locales')), [config('localization.default')]);
        $clean = [];

        foreach ($locales as $locale) {
            foreach ($this->translatableFields() as $field) {
                $value = $input[$locale][$field] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    $clean[$locale][$field] = trim($value);
                }
            }
        }

        $this->translations = $clean === [] ? null : $clean;
        $this->save();
    }
}
