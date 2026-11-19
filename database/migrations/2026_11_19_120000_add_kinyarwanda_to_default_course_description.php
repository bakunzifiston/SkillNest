<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The placeholder description many starter courses share, and its Kinyarwanda version. */
    private string $english = 'Learn essential skills with hands-on projects and expert instruction. Perfect for beginners and intermediate learners.';

    private string $kinyarwanda = 'Iga ubumenyi bw\'ingenzi ukoresheje imishinga ifatika n\'inyigisho z\'abahanga. Bikwiriye abatangira n\'abafite ubumenyi buringaniye.';

    public function up(): void
    {
        foreach (DB::table('courses')->where('description', $this->english)->get() as $course) {
            $translations = json_decode($course->translations ?? 'null', true) ?: [];

            // Never overwrite a Kinyarwanda description an admin has already written.
            if (! empty($translations['rw']['description'])) {
                continue;
            }

            $translations['rw']['description'] = $this->kinyarwanda;
            DB::table('courses')->where('id', $course->id)->update([
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('courses')->get() as $course) {
            $translations = json_decode($course->translations ?? 'null', true) ?: [];

            if (($translations['rw']['description'] ?? null) === $this->kinyarwanda) {
                unset($translations['rw']['description']);
                $translations['rw'] = array_filter($translations['rw'] ?? []);
                $translations = array_filter($translations);
                DB::table('courses')->where('id', $course->id)->update([
                    'translations' => $translations ? json_encode($translations, JSON_UNESCAPED_UNICODE) : null,
                ]);
            }
        }
    }
};
