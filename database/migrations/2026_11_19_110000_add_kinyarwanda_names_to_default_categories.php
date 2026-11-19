<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The four starter categories get a Kinyarwanda name; anything an admin already entered is left alone. */
    private array $names = [
        'Business' => 'Ubucuruzi',
        'Design' => 'Igishushanyo',
        'Development' => 'Gukora porogaramu',
        'Marketing' => 'Kwamamaza',
    ];

    public function up(): void
    {
        foreach (DB::table('categories')->whereIn('name', array_keys($this->names))->get() as $category) {
            $translations = json_decode($category->translations ?? 'null', true) ?: [];

            if (! empty($translations['rw']['name'])) {
                continue;
            }

            $translations['rw']['name'] = $this->names[$category->name];
            DB::table('categories')->where('id', $category->id)->update([
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('categories')->whereIn('name', array_keys($this->names))->get() as $category) {
            $translations = json_decode($category->translations ?? 'null', true) ?: [];

            if (($translations['rw']['name'] ?? null) === $this->names[$category->name]) {
                unset($translations['rw']['name']);
                $translations = array_filter(['rw' => array_filter($translations['rw'] ?? [])]) ?: null;
                DB::table('categories')->where('id', $category->id)->update([
                    'translations' => $translations ? json_encode($translations, JSON_UNESCAPED_UNICODE) : null,
                ]);
            }
        }
    }
};
