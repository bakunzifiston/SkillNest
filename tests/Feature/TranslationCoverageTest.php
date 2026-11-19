<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Guards the Kinyarwanda translation file: every text the code asks to translate must have a
 * translation, and translations must keep the placeholders ( :name, :count, {1}|[2,*] ) intact.
 */
class TranslationCoverageTest extends TestCase
{
    /** @return array<string, string> */
    private function rw(): array
    {
        $data = json_decode(file_get_contents(lang_path('rw.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);

        return $data;
    }

    /** @return array<string, list<string>> key => files that use it */
    private function usedKeys(): array
    {
        $files = [];
        foreach ([resource_path('views'), app_path()] as $dir) {
            foreach (File::allFiles($dir) as $file) {
                $path = $file->getPathname();
                if (! str_ends_with($path, '.php')) {
                    continue;
                }
                if (str_contains($path, DIRECTORY_SEPARATOR.'Console'.DIRECTORY_SEPARATOR)) {
                    continue; // developer tooling stays in English
                }
                $files[] = $path;
            }
        }

        $pattern = '/(?:\b__|\btrans_choice|\btrans|@lang|Lang::get)\(\s*(?:\'((?:\\\\.|[^\'\\\\])*)\'|"((?:\\\\.|[^"\\\\])*)")/s';
        $keys = [];

        foreach ($files as $path) {
            if (! preg_match_all($pattern, file_get_contents($path), $matches, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($matches as $m) {
                $key = ($m[1] ?? '') !== '' ? str_replace(["\\'", '\\\\'], ["'", '\\'], $m[1])
                                            : str_replace(['\\"', '\\\\'], ['"', '\\'], $m[2] ?? '');
                if ($key === '' || preg_match('/^[a-z0-9_]+(\.[a-z0-9_\-\*]+)+$/', $key)) {
                    continue; // empty, or a file key such as "validation.required"
                }
                $keys[$key][] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            }
        }

        return $keys;
    }

    public function test_translation_file_is_valid_json_with_text_values(): void
    {
        foreach ($this->rw() as $key => $value) {
            $this->assertIsString($key);
            $this->assertIsString($value, "Translation for \"{$key}\" must be a string");
            $this->assertNotSame('', trim($value), "Translation for \"{$key}\" is empty");
        }
    }

    public function test_every_text_wrapped_for_translation_has_a_kinyarwanda_translation(): void
    {
        $rw = $this->rw();
        $missing = [];

        foreach ($this->usedKeys() as $key => $files) {
            if (! array_key_exists($key, $rw)) {
                $missing[] = '"'.mb_substr($key, 0, 90).'"  ('.$files[0].')';
            }
        }

        $this->assertSame([], $missing, count($missing).' texts have no Kinyarwanda translation in lang/rw.json:'."\n".implode("\n", array_slice($missing, 0, 40)));
    }

    public function test_translations_keep_the_placeholders_of_the_english_text(): void
    {
        $problems = [];

        foreach ($this->rw() as $key => $value) {
            preg_match_all('/:[A-Za-z_]+/', $key, $k);
            preg_match_all('/:[A-Za-z_]+/', $value, $v);
            $a = array_unique($k[0]);
            $b = array_unique($v[0]);
            sort($a);
            sort($b);
            if ($a !== $b) {
                $problems[] = "placeholders differ: \"{$key}\" => \"{$value}\"";
            }

            $choice = fn (string $s) => [str_contains($s, '{1}'), str_contains($s, '[2,*]')];
            if ($choice($key) !== $choice($value)) {
                $problems[] = "plural syntax differs: \"{$key}\" => \"{$value}\"";
            }
        }

        $this->assertSame([], $problems, implode("\n", array_slice($problems, 0, 30)));
    }

    public function test_admin_menu_and_permission_labels_from_config_are_translated(): void
    {
        $rw = $this->rw();
        $labels = [];

        foreach (config('admin-modules.actions') as $label) {
            $labels[] = $label;
        }
        foreach (array_keys(config('admin-modules.groups')) as $group) {
            if ($group !== '') {
                $labels[] = $group;
            }
        }
        foreach (config('admin-modules.modules') as $module) {
            $labels[] = $module['label'];
            foreach ($module['nav'] ?? [] as $item) {
                $labels[] = $item['label'];
            }
        }

        $missing = array_values(array_filter(array_unique($labels), fn ($l) => ! array_key_exists($l, $rw)));

        $this->assertSame([], $missing, 'Admin config labels without a Kinyarwanda translation: '.implode(', ', $missing));
    }

    public function test_kinyarwanda_resolves_for_plain_and_plural_texts(): void
    {
        app()->setLocale('rw');

        $this->assertSame($this->rw()['Log in'], __('Log in'));
        $this->assertSame('Home', __('Home', [], 'en'));                  // English stays available
        $this->assertSame('Some text nobody translated', __('Some text nobody translated')); // falls back to English
    }

    public function test_kinyarwanda_plurals_use_the_plural_form_for_zero_and_many(): void
    {
        // Laravel has no plural rule for Kinyarwanda and would pick the singular form for 0.
        // Every plural text therefore carries an explicit {0} form.
        app()->setLocale('rw');
        $problems = [];

        foreach ($this->rw() as $key => $value) {
            if (! (str_contains($key, '{1}') && str_contains($key, '[2,*]'))) {
                continue;
            }
            $plural = trim(substr($value, strpos($value, '[2,*]') + 5));
            $one = trans_choice($key, 1, ['count' => 1]);
            $zero = trans_choice($key, 0, ['count' => 0]);
            $many = trans_choice($key, 5, ['count' => 5]);

            if ($zero !== str_replace(':count', '0', $plural) || $many !== str_replace(':count', '5', $plural) || $one === $many) {
                $problems[] = "{$key} => zero: {$zero} | one: {$one} | many: {$many}";
            }
        }

        $this->assertSame([], $problems, implode("\n", array_slice($problems, 0, 10)));
    }
}
