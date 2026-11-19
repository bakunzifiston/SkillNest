<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * English (default) and Kinyarwanda: switching, remembering the choice, and translated content.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $extra = []): Course
    {
        $category = Category::first() ?? Category::create(['name' => 'Design', 'slug' => 'design']);

        return Course::create(array_merge([
            'category_id' => $category->id, 'title' => 'English Title', 'slug' => 'c-'.uniqid(),
            'description' => 'English description', 'price' => 0, 'level' => 'beginner',
            'status' => Course::STATUS_PUBLISHED,
        ], $extra));
    }

    // ------------------------------------------------------------------ default + switching

    public function test_english_is_the_default_language(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertSame('en', app()->getLocale());
        $response->assertHeader('Content-Language', 'en');
        $this->assertStringContainsString('<html lang="en"', $response->getContent());
    }

    public function test_guest_can_switch_to_kinyarwanda_and_back(): void
    {
        $this->from(route('home'))->post(route('locale.update'), ['locale' => 'rw'])
            ->assertRedirect(route('home'))
            ->assertCookie(config('localization.cookie'), 'rw')
            ->assertSessionHas(config('localization.session_key'), 'rw');

        $html = $this->get(route('home'))->assertOk()->assertHeader('Content-Language', 'rw')->getContent();
        $this->assertStringContainsString('<html lang="rw"', $html);

        $this->post(route('locale.update'), ['locale' => 'en']);
        $this->assertStringContainsString('<html lang="en"', $this->get(route('home'))->getContent());
    }

    public function test_unknown_language_is_rejected_and_nothing_changes(): void
    {
        $this->post(route('locale.update'), ['locale' => 'fr'])->assertSessionHasErrors('locale');
        $this->post(route('locale.update'), [])->assertSessionHasErrors('locale');

        $this->assertStringContainsString('<html lang="en"', $this->get(route('home'))->getContent());
    }

    public function test_switching_never_redirects_to_another_site(): void
    {
        $this->from('https://evil.example/phish')->post(route('locale.update'), ['locale' => 'rw'])
            ->assertRedirect(route('home'));
    }

    public function test_the_switcher_is_available_on_every_kind_of_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            'site' => $this->get(route('home')),
            'login' => $this->get(route('login')),
            'register' => $this->get(route('register')),
        ] as $name => $response) {
            $response->assertOk();
            $this->assertMatchesRegularExpression('/name="locale"\s+value="rw"/', $response->getContent(), "switcher missing on {$name}");
            $this->assertMatchesRegularExpression('/name="locale"\s+value="en"/', $response->getContent(), "switcher missing on {$name}");
        }

        $this->assertMatchesRegularExpression(
            '/name="locale"\s+value="rw"/',
            $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent(),
            'switcher missing on admin'
        );
    }

    // ------------------------------------------------------------------ remembering the choice

    public function test_signed_in_choice_is_saved_on_the_account(): void
    {
        $user = User::factory()->create();
        $this->assertNull($user->locale);

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'rw']);

        $this->assertSame('rw', $user->fresh()->locale);
    }

    public function test_saved_language_follows_the_user_into_a_brand_new_session(): void
    {
        $user = User::factory()->create(['locale' => 'rw']);

        // No session, no cookie: a fresh browser that has just logged in.
        $this->actingAs($user)->get(route('home'))->assertHeader('Content-Language', 'rw');
        $this->assertSame('rw', app()->getLocale());
    }

    public function test_saved_preference_beats_a_leftover_guest_cookie(): void
    {
        // Control: the same cookie IS honoured for a visitor with no saved preference...
        $this->withCookie(config('localization.cookie'), 'rw')->get(route('home'))->assertHeader('Content-Language', 'rw');

        // ...but a signed-in user's own saved choice wins over it.
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->withCookie(config('localization.cookie'), 'rw')
            ->get(route('home'))->assertHeader('Content-Language', 'en');
    }

    public function test_guest_choice_is_adopted_by_a_user_who_never_chose(): void
    {
        $user = User::factory()->create(['locale' => null]);

        $this->post(route('locale.update'), ['locale' => 'rw']);            // as a guest
        $this->actingAs($user)->get(route('home'))->assertHeader('Content-Language', 'rw');

        $this->assertSame('rw', $user->fresh()->locale);
    }

    public function test_the_choice_survives_logout_through_the_cookie(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'rw'])
            ->assertCookie(config('localization.cookie'), 'rw');

        $this->post(route('logout'));

        $this->withCookie(config('localization.cookie'), 'rw')->get(route('home'))
            ->assertHeader('Content-Language', 'rw');
    }

    public function test_new_accounts_start_in_the_language_they_registered_in(): void
    {
        $this->post(route('locale.update'), ['locale' => 'rw']);
        $this->post(route('register'), [
            'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'country' => 'Rwanda', 'province' => 'Kigali City', 'district' => 'Gasabo', 'sector' => 'Remera',
        ]);

        $this->assertSame('rw', User::where('email', 'jane@example.com')->value('locale'));
    }

    public function test_emails_to_a_user_use_their_language(): void
    {
        $this->assertSame('rw', User::factory()->make(['locale' => 'rw'])->preferredLocale());
        $this->assertSame('en', User::factory()->make(['locale' => null])->preferredLocale());
        $this->assertSame('en', User::factory()->make(['locale' => 'xx'])->preferredLocale());
    }

    // ------------------------------------------------------------------ framework messages

    public function test_validation_and_auth_messages_are_available_in_kinyarwanda(): void
    {
        app()->setLocale('rw');

        $this->assertSame('Ugomba kuzuza imeri.', __('validation.required', ['attribute' => __('validation.attributes.email')]));
        $this->assertStringContainsString('ntabwo ahuye', __('auth.failed'));
        $this->assertStringContainsString('guhindura ijambobanga', __('passwords.sent'));

        app()->setLocale('en');
        $this->assertSame('These credentials do not match our records.', __('auth.failed'));
    }

    public function test_a_failed_login_shows_the_message_in_the_chosen_language(): void
    {
        $this->post(route('locale.update'), ['locale' => 'rw']);

        $this->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString('ntabwo ahuye', session('errors')->first('email'));
    }

    // ------------------------------------------------------------------ translated content

    public function test_content_falls_back_to_english_when_not_translated(): void
    {
        $course = $this->course();

        app()->setLocale('rw');
        $this->assertSame('English Title', $course->tr('title'));
        $this->assertSame('English description', $course->tr('description'));
        $this->assertFalse($course->isTranslatedTo('rw'));
    }

    public function test_translated_fields_show_in_kinyarwanda_and_the_rest_stay_english(): void
    {
        $course = $this->course();
        $course->saveTranslations(['rw' => ['title' => 'Umutwe mu Kinyarwanda']]);
        $course = $course->fresh();

        app()->setLocale('rw');
        $this->assertSame('Umutwe mu Kinyarwanda', $course->tr('title'));
        $this->assertSame('English description', $course->tr('description')); // not translated: English
        $this->assertTrue($course->isTranslatedTo('rw'));

        app()->setLocale('en');
        $this->assertSame('English Title', $course->tr('title'));
        $this->assertSame('English Title', $course->title);                    // the original is never overwritten
    }

    public function test_saving_translations_ignores_blanks_unknown_languages_and_unknown_fields(): void
    {
        $course = $this->course();

        $course->saveTranslations([
            'rw' => ['title' => '  Isomo  ', 'description' => '   ', 'price' => '999', 'slug' => 'hacked'],
            'fr' => ['title' => 'Cours'],
            'en' => ['title' => 'Overwrite attempt'],
        ]);
        $course = $course->fresh();

        $this->assertSame(['rw' => ['title' => 'Isomo']], $course->translations);
        $this->assertSame('English Title', $course->title);
        $this->assertSame('0', (string) (int) $course->price);

        $course->saveTranslations(['rw' => ['title' => '']]);
        $this->assertNull($course->fresh()->translations);
    }

    public function test_the_public_course_page_shows_translated_text_and_falls_back_per_field(): void
    {
        $course = $this->course(['title' => 'Digital Marketing', 'description' => 'Learn marketing']);
        $course->saveTranslations(['rw' => ['title' => 'Kwamamaza kuri murandasi']]);

        $en = $this->get(route('courses.show', $course))->assertOk()->getContent();
        $this->assertStringContainsString('Digital Marketing', $en);
        $this->assertStringNotContainsString('Kwamamaza kuri murandasi', $en);

        $this->post(route('locale.update'), ['locale' => 'rw']);
        $rw = $this->get(route('courses.show', $course))->assertOk()->getContent();
        $this->assertStringContainsString('Kwamamaza kuri murandasi', $rw);
        $this->assertStringContainsString('Learn marketing', $rw); // description has no translation: English
    }

    public function test_untranslated_courses_never_break_the_kinyarwanda_pages(): void
    {
        $this->course(['title' => 'Only English Here']);
        $this->post(route('locale.update'), ['locale' => 'rw']);

        $this->get(route('courses.index'))->assertOk()->assertSee('Only English Here');
        $this->get(route('home'))->assertOk()->assertSee('Only English Here');
    }

    // ------------------------------------------------------------------ emails

    private function sentMessages(): array
    {
        return app('mail.manager')->mailer('array')->getSymfonyTransport()->messages()->all();
    }

    public function test_the_password_reset_email_is_written_in_the_users_language(): void
    {
        config(['mail.default' => 'array']);

        User::factory()->create(['locale' => 'rw'])->sendPasswordResetNotification('token-rw');
        User::factory()->create(['locale' => null])->sendPasswordResetNotification('token-en');

        [$rw, $en] = $this->sentMessages();

        $this->assertStringContainsString('Hindura ijambobanga ryawe', $rw->getOriginalMessage()->getSubject());
        $this->assertStringContainsString('<html lang="rw"', $rw->getOriginalMessage()->getHtmlBody());
        $this->assertStringContainsString('token-rw', $rw->getOriginalMessage()->getHtmlBody());

        $this->assertStringContainsString('Reset Your Password', $en->getOriginalMessage()->getSubject());
        $this->assertStringContainsString('<html lang="en"', $en->getOriginalMessage()->getHtmlBody());
    }

    public function test_locale_does_not_leak_between_emails_or_into_the_rest_of_the_request(): void
    {
        config(['mail.default' => 'array']);
        $this->assertSame('en', app()->getLocale());

        User::factory()->create(['locale' => 'rw'])->sendPasswordResetNotification('t');

        $this->assertSame('en', app()->getLocale());   // sending a Kinyarwanda email must not switch the app
    }

    // ------------------------------------------------------------------ first visit (initial language)

    public function test_a_first_time_visitor_gets_the_initial_language(): void
    {
        config(['localization.initial' => 'rw']);

        $response = $this->get(route('home'))->assertOk()->assertHeader('Content-Language', 'rw');

        $this->assertStringContainsString('<html lang="rw"', $response->getContent());
        // The automatic initial language is NOT stored as if the visitor had chosen it.
        $response->assertCookieMissing(config('localization.cookie'));
        $this->assertNull(session(config('localization.session_key')));
    }

    public function test_an_explicit_english_choice_beats_the_initial_language(): void
    {
        config(['localization.initial' => 'rw']);

        $this->post(route('locale.update'), ['locale' => 'en'])->assertCookie(config('localization.cookie'), 'en');

        $this->get(route('home'))->assertHeader('Content-Language', 'en');
        $this->withCookie(config('localization.cookie'), 'en')->flushSession()->get(route('home'))->assertHeader('Content-Language', 'en');
    }

    public function test_users_without_a_saved_choice_follow_the_initial_language_and_saved_choices_win(): void
    {
        config(['localization.initial' => 'rw']);

        $none = User::factory()->create(['locale' => null]);
        $this->actingAs($none)->get(route('home'))->assertHeader('Content-Language', 'rw');
        $this->assertNull($none->fresh()->locale);                       // nothing is saved on their account automatically

        $english = User::factory()->create(['locale' => 'en']);
        $this->actingAs($english)->get(route('home'))->assertHeader('Content-Language', 'en');
    }

    public function test_emails_follow_the_initial_language_when_no_choice_was_saved(): void
    {
        config(['localization.initial' => 'rw']);

        $this->assertSame('rw', User::factory()->make(['locale' => null])->preferredLocale());
        $this->assertSame('en', User::factory()->make(['locale' => 'en'])->preferredLocale());
        config(['localization.initial' => 'xx']);                         // a typo in .env must not break anything
        $this->assertSame('en', User::factory()->make(['locale' => null])->preferredLocale());
    }

    public function test_the_content_base_language_and_fallback_stay_english_when_the_initial_language_is_kinyarwanda(): void
    {
        config(['localization.initial' => 'rw']);

        $this->assertSame('en', config('localization.default'));
        $this->assertSame('en', config('app.fallback_locale'));

        // Translations are still edited as "Kinyarwanda translations" of English originals.
        $course = $this->course();
        $course->saveTranslations(['rw' => ['title' => 'Isomo'], 'en' => ['title' => 'ignored']]);
        $this->assertSame(['rw' => ['title' => 'Isomo']], $course->fresh()->translations);
        $this->get(route('home'));
        $this->assertSame('Isomo', $course->fresh()->tr('title'));       // current language is rw on a first visit
        $this->assertSame('English Title', $course->fresh()->title);     // the original is untouched
    }

    public function test_an_invalid_initial_language_falls_back_to_english(): void
    {
        config(['localization.initial' => 'zz']);

        $this->get(route('home'))->assertHeader('Content-Language', 'en');
    }

    // ------------------------------------------------------------------ audience warning wording

    private function restrictedCourse(string $level, ?int $organizationId = null): Course
    {
        return $this->course(['title' => 'Restricted '.$level, 'slug' => 'r-'.$level, 'access_level' => $level, 'partner_organization_id' => $organizationId]);
    }

    public function test_the_partner_only_warning_reads_naturally_in_kinyarwanda(): void
    {
        $sfh = \App\Models\PartnerOrganization::where('slug', 'sfh')->firstOrFail();
        $course = $this->restrictedCourse('partner', $sfh->id);

        app()->setLocale('rw');
        $this->assertSame(
            'Iri somo rijyenewe abafatanyabikorwa ba SFH gusa. Injira ukoresheje konti yujuje ibisabwa kugira ngo wiyandikishe.',
            $course->audienceWarning(null)
        );

        $agent = User::factory()->create();
        $this->assertSame(
            'Iri somo rijyenewe abafatanyabikorwa ba SFH gusa. Konti yawe (Agent) ntishobora kuryiyandikishamo.',
            $course->audienceWarning($agent)
        );
        $this->assertStringNotContainsString('abanyeshuri ba Abanyeshuri', $course->audienceWarning(null));
    }

    public function test_the_other_audience_warnings_also_read_naturally_in_kinyarwanda(): void
    {
        app()->setLocale('rw');

        $this->assertStringStartsWith('Iri somo rijyenewe aba-agent gusa.', $this->restrictedCourse('agents')->audienceWarning(null));
        $this->assertStringStartsWith('Iri somo rijyenewe abafatanyabikorwa gusa.', $this->restrictedCourse('partners')->audienceWarning(null));
    }

    public function test_the_english_warning_is_unchanged(): void
    {
        $sfh = \App\Models\PartnerOrganization::where('slug', 'sfh')->firstOrFail();

        $this->assertSame(
            'This course is for SFH partner learners only. Log in with an eligible account to enroll.',
            $this->restrictedCourse('partner', $sfh->id)->audienceWarning(null)
        );
        $this->assertStringStartsWith('This course is for Agents only.', $this->restrictedCourse('agents')->audienceWarning(null));
    }
}
