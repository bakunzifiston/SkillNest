<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Services\CourseProgressCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnerAccessTest extends TestCase
{
    use RefreshDatabase;

    private PartnerOrganization $sfh;

    private PartnerOrganization $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sfh = PartnerOrganization::where('slug', 'sfh')->firstOrFail(); // seeded by migration
        $this->other = PartnerOrganization::create(['name' => 'Other Org', 'slug' => 'other-org', 'registration_code' => 'OTHER222']);
    }

    private function course(string $level = 'all', ?PartnerOrganization $org = null, array $extra = []): Course
    {
        $category = Category::first() ?? Category::create(['name' => 'Design', 'slug' => 'design']);

        return Course::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Course '.uniqid(),
            'slug' => 'course-'.uniqid(),
            'description' => 'desc',
            'price' => 0,
            'level' => 'beginner',
            'status' => Course::STATUS_PUBLISHED,
            'access_level' => $level,
            'partner_organization_id' => $org?->id,
        ], $extra));
    }

    private function lesson(Course $course): Lesson
    {
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Ch', 'sort_order' => 1]);

        return Lesson::create(['chapter_id' => $chapter->id, 'title' => 'Lesson', 'type' => 'text', 'content' => 'x', 'sort_order' => 1]);
    }

    private function agent(): User
    {
        return User::factory()->create();
    }

    private function partner(?PartnerOrganization $org = null): User
    {
        return User::factory()->create(['learner_type' => 'partner', 'partner_organization_id' => ($org ?? $this->sfh)->id]);
    }

    // ---------------------------------------------------------------- defaults

    public function test_existing_style_users_and_courses_default_to_agent_and_all(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'category_id' => (Category::create(['name' => 'C', 'slug' => 'c']))->id,
            'title' => 'Legacy', 'slug' => 'legacy', 'description' => 'x', 'price' => 0, 'level' => 'beginner',
        ]);

        $this->assertSame('agent', $user->fresh()->learner_type);
        $this->assertNull($user->fresh()->partner_organization_id);
        $this->assertSame('all', $course->fresh()->access_level);
    }

    public function test_sfh_is_seeded_as_first_partner_organization(): void
    {
        $this->assertTrue($this->sfh->is_active);
        $this->assertSame('SFH', $this->sfh->name);
    }

    // ------------------------------------------------------------ audience matrix

    public function test_audience_matrix(): void
    {
        $guest = null;
        $agent = $this->agent();
        $sfh = $this->partner($this->sfh);
        $other = $this->partner($this->other);
        $admin = User::factory()->create(['is_admin' => true]);

        $cases = [
            //                       guest  agent  sfh    other  admin
            'all' => [true, true, true, true, true],
            'agents' => [false, true, false, false, true],
            'partners' => [false, false, true, true, true],
        ];

        foreach ($cases as $level => $expected) {
            $course = $this->course($level);
            foreach ([$guest, $agent, $sfh, $other, $admin] as $i => $viewer) {
                $this->assertSame($expected[$i], $course->isAvailableTo($viewer), "level {$level}, viewer #{$i}");
            }
        }

        $specific = $this->course('partner', $this->sfh);
        $this->assertFalse($specific->isAvailableTo($guest));
        $this->assertFalse($specific->isAvailableTo($agent));
        $this->assertTrue($specific->isAvailableTo($sfh));
        $this->assertFalse($specific->isAvailableTo($other));
        $this->assertTrue($specific->isAvailableTo($admin));
    }

    public function test_specific_partner_course_without_organization_is_closed_to_partners(): void
    {
        $broken = $this->course('partner', null);

        $this->assertFalse($broken->isAvailableTo($this->partner()));
    }

    // --------------------------------------------------------------- registration

    private function registrationData(array $extra = []): array
    {
        return array_merge([
            'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane'.uniqid().'@example.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'country' => 'Rwanda', 'province' => 'Kigali City', 'district' => 'Gasabo', 'sector' => 'Remera',
        ], $extra);
    }

    public function test_registration_defaults_to_agent(): void
    {
        $data = $this->registrationData();
        $this->post(route('register'), $data)->assertRedirect();

        $user = User::where('email', $data['email'])->first();
        $this->assertSame('agent', $user->learner_type);
        $this->assertNull($user->partner_organization_id);
    }

    public function test_registration_as_partner_stores_organization(): void
    {
        $data = $this->registrationData(['learner_type' => 'partner', 'partner_organization_id' => $this->sfh->id, 'partner_code' => $this->sfh->registration_code]);
        $this->post(route('register'), $data)->assertRedirect();

        $user = User::where('email', $data['email'])->first();
        $this->assertSame('partner', $user->learner_type);
        $this->assertSame($this->sfh->id, $user->partner_organization_id);
    }

    public function test_partner_registration_requires_valid_active_organization(): void
    {
        $this->post(route('register'), $this->registrationData(['learner_type' => 'partner']))
            ->assertSessionHasErrors('partner_organization_id');

        $this->post(route('register'), $this->registrationData(['learner_type' => 'partner', 'partner_organization_id' => 9999]))
            ->assertSessionHasErrors('partner_organization_id');

        $this->other->update(['is_active' => false]);
        $this->post(route('register'), $this->registrationData(['learner_type' => 'partner', 'partner_organization_id' => $this->other->id, 'partner_code' => 'OTHER222']))
            ->assertSessionHasErrors('partner_organization_id');

        $this->assertSame(0, User::where('learner_type', 'partner')->count());
    }

    public function test_agent_registration_ignores_a_submitted_organization(): void
    {
        $data = $this->registrationData(['learner_type' => 'agent', 'partner_organization_id' => $this->sfh->id]);
        $this->post(route('register'), $data)->assertRedirect();

        $this->assertNull(User::where('email', $data['email'])->first()->partner_organization_id);
    }

    public function test_register_page_offers_partner_option_with_active_organizations_only(): void
    {
        $this->other->update(['is_active' => false]);

        $html = $this->get(route('register'))->assertOk()->getContent();
        $this->assertStringContainsString('Partner organization', $html);
        $this->assertStringContainsString('SFH', $html);
        $this->assertStringNotContainsString('Other Org', $html);
    }

    // ------------------------------------------------------ direct URL + enrollment

    public function test_restricted_course_page_stays_visible_with_a_warning_and_no_enroll_button(): void
    {
        $course = $this->course('partner', $this->sfh, ['title' => 'SFH-ONLY-COURSE']);
        $enrollUrl = route('courses.enroll', $course);

        // Guest: visible, warned, with log in / register links, no enroll form.
        $guest = $this->get(route('courses.show', $course))->assertOk()
            ->assertSee('SFH-ONLY-COURSE')->assertSee('Enrollment not available')->assertSee('SFH partner learners only');
        $this->assertStringNotContainsString($enrollUrl, $guest->getContent());
        $guest->assertSee(route('login'), false)->assertSee(route('register'), false);

        // Agent and a partner from another organization: visible, warned, cannot enroll.
        foreach ([$this->agent(), $this->partner($this->other)] as $viewer) {
            $html = $this->actingAs($viewer)->get(route('courses.show', $course))->assertOk()
                ->assertSee('Enrollment not available')->assertSee("can't enroll in it")->getContent();
            $this->assertStringNotContainsString($enrollUrl, $html);
        }

        // Eligible partner: no warning, enroll button present.
        $html = $this->actingAs($this->partner($this->sfh))->get(route('courses.show', $course))->assertOk()
            ->assertDontSee('Enrollment not available')->getContent();
        $this->assertStringContainsString($enrollUrl, $html);
    }

    public function test_audience_warning_messages(): void
    {
        $course = $this->course('partners');

        $this->assertNull($course->audienceWarning($this->partner()));
        $this->assertStringContainsString('Partners only', $course->audienceWarning(null));
        $this->assertStringContainsString('Agent', $course->audienceWarning($this->agent()));
        $this->assertNull($this->course('all')->audienceWarning(null));
    }

    public function test_ineligible_learner_cannot_enroll_even_with_a_crafted_request(): void
    {
        $course = $this->course('agents');
        $partner = $this->partner();

        $this->actingAs($partner)->post(route('courses.enroll', $course))->assertForbidden();

        $this->assertSame(0, Enrollment::count());
        $this->assertSame(0, $course->fresh()->students_count);
    }

    public function test_eligible_learner_can_enroll(): void
    {
        $course = $this->course('partner', $this->sfh);
        $this->lesson($course);
        $partner = $this->partner($this->sfh);

        $this->actingAs($partner)->post(route('courses.enroll', $course))->assertRedirect();

        $this->assertTrue($partner->fresh()->hasEnrolled($course));
    }

    public function test_free_course_lesson_url_does_not_auto_enroll_ineligible_learner(): void
    {
        $course = $this->course('partners');
        $lesson = $this->lesson($course);
        $agent = $this->agent();

        $this->actingAs($agent)->get(route('courses.lessons.show', [$course, $lesson]))->assertRedirect(route('courses.show', $course));

        $this->assertSame(0, Enrollment::count());
    }

    public function test_eligible_learner_can_open_lesson_of_restricted_course(): void
    {
        $course = $this->course('partner', $this->sfh);
        $lesson = $this->lesson($course);

        $this->actingAs($this->partner($this->sfh))
            ->get(route('courses.lessons.show', [$course, $lesson]))
            ->assertOk();
    }

    public function test_open_courses_still_work_for_everyone(): void
    {
        $course = $this->course('all');
        $lesson = $this->lesson($course);

        $this->get(route('courses.show', $course))->assertOk();
        $this->actingAs($this->agent())->get(route('courses.lessons.show', [$course, $lesson]))->assertOk();
        $this->actingAs($this->partner())->get(route('courses.lessons.show', [$course, $lesson]))->assertOk();
    }

    public function test_existing_enrollments_keep_access_after_course_is_restricted(): void
    {
        $course = $this->course('all');
        $lesson = $this->lesson($course);
        $agent = $this->agent();
        Enrollment::create(['user_id' => $agent->id, 'course_id' => $course->id]);

        $course->update(['access_level' => 'partners']);

        $this->actingAs($agent)->get(route('courses.show', $course))->assertOk();
        $this->actingAs($agent)->get(route('courses.lessons.show', [$course, $lesson]))->assertOk();
        $this->actingAs($agent)->get(route('courses.index'))->assertSee($course->title);
    }

    // -------------------------------------------------------------------- listings

    public function test_restricted_courses_stay_listed_for_everyone_with_an_audience_badge(): void
    {
        $this->course('all', null, ['title' => 'OPEN-COURSE']);
        $this->course('agents', null, ['title' => 'AGENTS-COURSE']);
        $this->course('partners', null, ['title' => 'PARTNERS-COURSE']);
        $this->course('partner', $this->sfh, ['title' => 'SFH-COURSE']);

        foreach ([null, $this->agent(), $this->partner($this->sfh), $this->partner($this->other)] as $viewer) {
            $html = ($viewer ? $this->actingAs($viewer) : $this)->get(route('courses.index'))->assertOk()->getContent();
            foreach (['OPEN-COURSE', 'AGENTS-COURSE', 'PARTNERS-COURSE', 'SFH-COURSE'] as $title) {
                $this->assertStringContainsString($title, $html);
            }
            $this->assertStringContainsString('Agents only', $html);
            $this->assertStringContainsString('Partners only', $html);
            $this->assertStringContainsString('SFH only', $html);
        }
    }

    public function test_home_page_still_lists_restricted_courses_with_a_badge(): void
    {
        $this->course('partners', null, ['title' => 'PARTNER-COURSE-ON-HOME']);

        $this->get(route('home'))->assertOk()->assertSee('PARTNER-COURSE-ON-HOME')->assertSee('Partners only');
    }

    // --------------------------------------------------------------------- bundles

    public function test_bundle_enrollment_skips_courses_outside_the_learners_audience(): void
    {
        $open = $this->course('all');
        $restricted = $this->course('partner', $this->sfh);
        $bundle = Bundle::create(['title' => 'Bundle', 'slug' => 'bundle', 'description' => 'x', 'status' => Bundle::STATUS_PUBLISHED]);
        $bundle->courses()->attach([$open->id => ['order' => 1], $restricted->id => ['order' => 2]]);

        $agent = $this->agent();
        $this->actingAs($agent)->post(route('bundles.enroll', $bundle))->assertRedirect();

        $this->assertTrue($agent->fresh()->hasEnrolled($open));
        $this->assertFalse($agent->fresh()->hasEnrolled($restricted));
    }

    public function test_bundle_enrollment_rejected_when_no_course_is_available(): void
    {
        $restricted = $this->course('partner', $this->sfh);
        $bundle = Bundle::create(['title' => 'B2', 'slug' => 'b2', 'description' => 'x', 'status' => Bundle::STATUS_PUBLISHED]);
        $bundle->courses()->attach([$restricted->id => ['order' => 1]]);

        $agent = $this->agent();
        $this->actingAs($agent)->post(route('bundles.enroll', $bundle))->assertSessionHas('error');

        $this->assertSame(0, Enrollment::count());
        $this->assertSame(0, $agent->bundleEnrollments()->count());
    }

    // ----------------------------------------------------------------------- admin

    private function admin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $this->actingAs($admin)->app->make('auth')->user();
    }

    private function coursePayload(array $extra = []): array
    {
        return array_merge([
            'category_id' => (Category::first() ?? Category::create(['name' => 'D', 'slug' => 'd']))->id,
            'title' => 'Admin Course', 'status' => 'draft',
        ], $extra);
    }

    public function test_admin_can_create_course_for_a_specific_partner(): void
    {
        $this->admin();
        $this->get(route('admin.courses.create'))->assertOk()->assertSee('Who can access this course');

        $this->post(route('admin.courses.store'), $this->coursePayload(['access_level' => 'partner', 'partner_organization_id' => $this->sfh->id]))->assertRedirect();

        $course = Course::where('title', 'Admin Course')->first();
        $this->assertSame('partner', $course->access_level);
        $this->assertSame($this->sfh->id, $course->partner_organization_id);
    }

    public function test_specific_partner_requires_an_organization(): void
    {
        $this->admin();

        $this->post(route('admin.courses.store'), $this->coursePayload(['access_level' => 'partner']))
            ->assertSessionHasErrors('partner_organization_id');
        $this->assertSame(0, Course::count());
    }

    public function test_course_created_without_access_level_is_open_to_all(): void
    {
        $this->admin();
        $this->post(route('admin.courses.store'), $this->coursePayload());

        $this->assertSame('all', Course::first()->access_level);
    }

    public function test_changing_audience_clears_stale_organization_and_warns_about_enrolled_learners(): void
    {
        $this->admin();
        $course = $this->course('partner', $this->sfh);
        $agent = $this->agent();
        Enrollment::create(['user_id' => $agent->id, 'course_id' => $course->id]);

        $payload = [
            'category_id' => $course->category_id, 'title' => $course->title, 'status' => 'published',
            'access_level' => 'agents', 'partner_organization_id' => $this->sfh->id,
        ];
        $this->get(route('admin.courses.edit', $course))->assertOk();
        $this->put(route('admin.courses.update', $course), $payload)->assertRedirect();

        $course->refresh();
        $this->assertSame('agents', $course->access_level);
        $this->assertNull($course->partner_organization_id);

        // Now restrict to partners: the enrolled agent is outside the audience but kept.
        $this->put(route('admin.courses.update', $course), array_merge($payload, ['access_level' => 'partners']))
            ->assertSessionHas('success', fn ($m) => str_contains($m, '1 already-enrolled learner'));
        $this->assertTrue($agent->fresh()->hasEnrolled($course));
    }

    public function test_update_without_access_fields_keeps_existing_audience(): void
    {
        $this->admin();
        $course = $this->course('partner', $this->sfh);

        $this->put(route('admin.courses.update', $course), [
            'category_id' => $course->category_id, 'title' => 'Renamed', 'status' => 'published',
        ])->assertRedirect();

        $course->refresh();
        $this->assertSame('partner', $course->access_level);
        $this->assertSame($this->sfh->id, $course->partner_organization_id);
    }

    public function test_admin_can_manage_partner_organizations(): void
    {
        $this->admin();
        $this->get(route('admin.partner-organizations.index'))->assertOk()->assertSee('SFH');

        $this->post(route('admin.partner-organizations.store'), ['name' => 'New Partner'])->assertRedirect();
        $org = PartnerOrganization::where('name', 'New Partner')->first();
        $this->assertSame('new-partner', $org->slug);
        $this->assertTrue($org->is_active);

        $this->post(route('admin.partner-organizations.store'), ['name' => 'New Partner'])->assertSessionHasErrors('name');

        $this->put(route('admin.partner-organizations.update', $org), ['name' => 'Renamed Partner', 'is_active' => '0'])->assertRedirect();
        $this->assertFalse($org->fresh()->is_active);
    }

    public function test_organization_in_use_cannot_be_deleted(): void
    {
        $this->admin();
        $this->partner($this->sfh);

        $this->delete(route('admin.partner-organizations.destroy', $this->sfh))->assertSessionHas('error');
        $this->assertNotNull(PartnerOrganization::find($this->sfh->id));

        $unused = PartnerOrganization::create(['name' => 'Unused', 'slug' => 'unused']);
        $this->delete(route('admin.partner-organizations.destroy', $unused))->assertSessionHas('success');
        $this->assertNull(PartnerOrganization::find($unused->id));
    }

    public function test_non_admin_cannot_reach_partner_organization_admin(): void
    {
        $this->actingAs($this->agent())->get(route('admin.partner-organizations.index'))->assertForbidden();
    }

    public function test_admin_can_set_a_learners_category(): void
    {
        $this->admin();
        $learner = $this->agent();

        $this->get(route('admin.users.edit', $learner))->assertOk()->assertSee('Learner category');
        $this->put(route('admin.users.update', $learner), [
            'first_name' => $learner->first_name, 'last_name' => $learner->last_name, 'email' => $learner->email,
            'country' => 'Rwanda', 'province' => 'Kigali City', 'district' => 'Gasabo', 'sector' => 'Remera',
            'is_active' => '1', 'learner_type' => 'partner', 'partner_organization_id' => $this->sfh->id,
        ])->assertRedirect();

        $learner->refresh();
        $this->assertSame('partner', $learner->learner_type);
        $this->assertSame($this->sfh->id, $learner->partner_organization_id);
        $this->assertSame('Partner (SFH)', $learner->learnerCategoryLabel());
    }

    // ------------------------------------------------------------------ CSV import

    private function importRow(string $email): array
    {
        return [
            'line' => 2, 'email' => $email, 'name' => 'Imp Orted', 'first_name' => 'Imp', 'last_name' => 'Orted',
            'percent_completed' => 0, 'percent_viewed' => 0, 'content_progress' => [],
            'activated_at' => null, 'started_at' => null, 'completed_at' => null, 'last_sign_in' => null,
        ];
    }

    public function test_import_creates_new_users_inside_the_courses_audience(): void
    {
        $sfhCourse = $this->course('partner', $this->sfh);
        $openCourse = $this->course('all');
        $importer = new CourseProgressCsvImporter;

        $importer->import([$this->importRow('new-sfh@example.com')], $sfhCourse);
        $importer->import([$this->importRow('new-open@example.com')], $openCourse);

        $sfhUser = User::where('email', 'new-sfh@example.com')->first();
        $this->assertSame('partner', $sfhUser->learner_type);
        $this->assertSame($this->sfh->id, $sfhUser->partner_organization_id);
        $this->assertTrue($sfhUser->hasEnrolled($sfhCourse));
        $this->assertSame('agent', User::where('email', 'new-open@example.com')->first()->learner_type);
    }

    public function test_import_skips_existing_users_outside_the_courses_audience(): void
    {
        $course = $this->course('partner', $this->sfh);
        $agent = $this->agent();

        $summary = (new CourseProgressCsvImporter)->import([$this->importRow($agent->email)], $course);

        $this->assertSame(0, Enrollment::count());
        $this->assertSame(0, $summary['enrollments_created']);
        $this->assertSame(1, $summary['skipped_rows']);
        $this->assertNotEmpty($summary['errors']);
    }

    // ------------------------------------------------------- partner access codes

    private function partnerRegistration(PartnerOrganization $org, ?string $code): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('register'), $this->registrationData([
            'learner_type' => 'partner',
            'partner_organization_id' => $org->id,
            'partner_code' => $code,
        ]));
    }

    public function test_partner_registration_fails_without_or_with_wrong_code(): void
    {
        $this->partnerRegistration($this->sfh, null)->assertSessionHasErrors('partner_code');
        $this->partnerRegistration($this->sfh, '')->assertSessionHasErrors('partner_code');
        $this->partnerRegistration($this->sfh, 'WRONGCODE')->assertSessionHasErrors('partner_code');

        $this->assertSame(0, User::where('learner_type', 'partner')->count());
    }

    public function test_one_organizations_code_does_not_work_for_another(): void
    {
        $this->partnerRegistration($this->sfh, $this->other->registration_code)->assertSessionHasErrors('partner_code');

        $this->assertSame(0, User::where('learner_type', 'partner')->count());
    }

    public function test_code_is_case_insensitive_and_trimmed(): void
    {
        $this->partnerRegistration($this->sfh, '  '.strtolower($this->sfh->registration_code).' ')->assertSessionDoesntHaveErrors();

        $this->assertSame(1, User::where('learner_type', 'partner')->count());
    }

    public function test_organization_without_a_code_accepts_no_partner_registrations(): void
    {
        $this->sfh->update(['registration_code' => null]);

        $this->partnerRegistration($this->sfh, '')->assertSessionHasErrors('partner_code');
        $this->partnerRegistration($this->sfh, 'null')->assertSessionHasErrors('partner_code');

        $this->assertSame(0, User::where('learner_type', 'partner')->count());
    }

    public function test_agent_registration_does_not_need_a_code(): void
    {
        $this->post(route('register'), $this->registrationData(['learner_type' => 'agent']))->assertSessionDoesntHaveErrors();

        $this->assertSame(1, User::where('learner_type', 'agent')->where('is_admin', false)->count());
    }

    public function test_registration_attempts_are_rate_limited_so_codes_cannot_be_guessed(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->partnerRegistration($this->sfh, 'GUESS'.$i)->assertSessionHasErrors('partner_code');
        }

        $this->partnerRegistration($this->sfh, $this->sfh->registration_code)->assertStatus(429);
        $this->assertSame(0, User::where('learner_type', 'partner')->count());
    }

    public function test_new_organizations_get_a_code_and_admin_can_regenerate_it(): void
    {
        $this->admin();
        $this->post(route('admin.partner-organizations.store'), ['name' => 'Brand New']);
        $org = PartnerOrganization::where('name', 'Brand New')->first();
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{8}$/', $org->registration_code);

        $old = $org->registration_code;
        $this->post(route('admin.partner-organizations.regenerate-code', $org))->assertRedirect();
        $this->assertNotSame($old, $org->fresh()->registration_code);

        // Old code stops working, new one works.
        auth()->logout();
        $this->partnerRegistration($org, $old)->assertSessionHasErrors('partner_code');
        $this->partnerRegistration($org, $org->fresh()->registration_code)->assertSessionDoesntHaveErrors();
    }

    public function test_admin_index_shows_codes_and_register_page_asks_for_one(): void
    {
        $this->get(route('register'))->assertSee('Organization access code');

        $this->admin();
        $this->get(route('admin.partner-organizations.index'))->assertOk()->assertSee($this->sfh->registration_code);
    }

    // ------------------------------------------------------------ Agent KYC fields

    private function kycData(array $extra = []): array
    {
        return array_merge([
            'phone' => '+250 788 123 456',
            'national_id' => '1 1990 8 0012345 6 78',
            'date_of_birth' => '1990-05-17',
            'gender' => 'female',
        ], $extra);
    }

    public function test_agent_can_register_with_all_kyc_fields(): void
    {
        $data = $this->registrationData(['learner_type' => 'agent'] + $this->kycData());
        $this->post(route('register'), $data)->assertSessionDoesntHaveErrors();

        $user = User::where('email', $data['email'])->first();
        $this->assertSame('+250 788 123 456', $user->phone);
        $this->assertSame('1199080012345678', $user->national_id); // spaces stripped
        $this->assertSame('1990-05-17', $user->date_of_birth->format('Y-m-d'));
        $this->assertSame('female', $user->gender);
    }

    public function test_kyc_fields_are_optional_for_agents(): void
    {
        $data = $this->registrationData(['learner_type' => 'agent', 'phone' => '', 'national_id' => '', 'date_of_birth' => '', 'gender' => '']);
        $this->post(route('register'), $data)->assertSessionDoesntHaveErrors();

        $user = User::where('email', $data['email'])->first();
        $this->assertNotNull($user);
        $this->assertNull($user->phone);
        $this->assertNull($user->national_id);
        $this->assertNull($user->date_of_birth);
        $this->assertNull($user->gender);
    }

    public function test_agent_can_fill_only_some_kyc_fields(): void
    {
        $data = $this->registrationData(['phone' => '0788123456']);
        $this->post(route('register'), $data)->assertSessionDoesntHaveErrors();

        $user = User::where('email', $data['email'])->first();
        $this->assertSame('0788123456', $user->phone);
        $this->assertNull($user->national_id);
    }

    public function test_invalid_kyc_values_are_rejected_when_provided(): void
    {
        $this->post(route('register'), $this->registrationData($this->kycData(['phone' => 'not-a-phone'])))->assertSessionHasErrors('phone');
        $this->post(route('register'), $this->registrationData($this->kycData(['national_id' => '12!'])))->assertSessionHasErrors('national_id');
        $this->post(route('register'), $this->registrationData($this->kycData(['date_of_birth' => now()->addDay()->format('Y-m-d')])))->assertSessionHasErrors('date_of_birth');
        $this->post(route('register'), $this->registrationData($this->kycData(['gender' => 'robot'])))->assertSessionHasErrors('gender');

        $this->assertSame(0, User::where('is_admin', false)->whereNotNull('email')->where('email', 'like', '%@example.%')->count());
    }

    public function test_national_id_is_encrypted_in_the_database(): void
    {
        $data = $this->registrationData($this->kycData(['national_id' => '1199080012345678']));
        $this->post(route('register'), $data);

        $raw = \DB::table('users')->where('email', $data['email'])->value('national_id');
        $this->assertNotNull($raw);
        $this->assertStringNotContainsString('1199080012345678', $raw);
        $this->assertSame('1199080012345678', User::where('email', $data['email'])->first()->national_id);
        $this->assertArrayNotHasKey('national_id', User::where('email', $data['email'])->first()->toArray());
    }

    public function test_partner_registration_ignores_kyc_fields(): void
    {
        $data = $this->registrationData(['learner_type' => 'partner', 'partner_organization_id' => $this->sfh->id, 'partner_code' => $this->sfh->registration_code] + $this->kycData());
        $this->post(route('register'), $data)->assertSessionDoesntHaveErrors();

        $user = User::where('email', $data['email'])->first();
        $this->assertSame('partner', $user->learner_type);
        $this->assertNull($user->phone);
        $this->assertNull($user->national_id);
    }

    public function test_register_page_shows_optional_kyc_section(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Additional details')->assertSee('National ID number')->assertSee('Phone number');
    }

    private function profilePayload(User $user, array $extra = []): array
    {
        return array_merge([
            'first_name' => $user->first_name, 'last_name' => $user->last_name, 'email' => $user->email,
            'country' => 'Rwanda', 'province' => 'Kigali City', 'district' => 'Gasabo', 'sector' => 'Remera',
        ], $extra);
    }

    public function test_agent_can_add_kyc_later_from_profile(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->get(route('profile.edit'))->assertOk()->assertSee('National ID number');
        $this->patch(route('profile.update'), $this->profilePayload($agent, $this->kycData()))->assertSessionHasNoErrors();

        $this->assertSame('+250 788 123 456', $agent->fresh()->phone);
        $this->assertSame('1199080012345678', $agent->fresh()->national_id);
    }

    public function test_partner_profile_has_no_kyc_and_cannot_set_it(): void
    {
        $partner = $this->partner();

        $this->actingAs($partner)->get(route('profile.edit'))->assertOk()->assertDontSee('National ID number');
        $this->patch(route('profile.update'), $this->profilePayload($partner, $this->kycData()))->assertSessionHasNoErrors();

        $this->assertNull($partner->fresh()->phone);
        $this->assertNull($partner->fresh()->national_id);
    }

    public function test_admin_sees_agent_kyc_on_user_page(): void
    {
        $agent = $this->agent();
        $agent->forceFill($this->kycData(['national_id' => '1199080012345678']))->save();
        $this->admin();

        $this->get(route('admin.users.show', $agent))->assertOk()
            ->assertSee('KYC details')->assertSee('1199080012345678')->assertSee('+250 788 123 456')->assertDontSee('Occupation');
    }

    // ------------------------------------------- Register link on restricted courses

    public function test_register_link_on_sfh_course_opens_partner_signup_for_sfh(): void
    {
        $course = $this->course('partner', $this->sfh);
        $link = route('register', ['course' => $course->slug, 'type' => 'partner', 'organization' => $this->sfh->id]);

        $this->get(route('courses.show', $course))->assertOk()->assertSee($link);

        $html = $this->get($link)->assertOk()->getContent();
        $this->assertStringContainsString("type: 'partner'", $html);                       // Partner radio selected
        $this->assertMatchesRegularExpression('/<option value="'.$this->sfh->id.'"\s+selected/', $html); // SFH preselected
    }

    public function test_register_link_on_all_partners_course_preselects_partner_without_an_organization(): void
    {
        $course = $this->course('partners');
        $link = route('register', ['course' => $course->slug, 'type' => 'partner']);

        $this->get(route('courses.show', $course))->assertSee($link);

        $html = $this->get($link)->assertOk()->getContent();
        $this->assertStringContainsString("type: 'partner'", $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="\d+"\s+selected/', $html);
    }

    public function test_register_link_on_agents_only_course_opens_normal_agent_signup(): void
    {
        $course = $this->course('agents');
        $link = route('register', ['course' => $course->slug]);

        $this->get(route('courses.show', $course))->assertOk()->assertSee($link);
        $this->assertStringContainsString("type: 'agent'", $this->get($link)->getContent());
    }

    public function test_register_page_ignores_bad_type_or_organization_parameters(): void
    {
        $this->other->update(['is_active' => false]);

        $html = $this->get(route('register', ['type' => 'hacker', 'organization' => 9999]))->assertOk()->getContent();
        $this->assertStringContainsString("type: 'agent'", $html);

        // An inactive organization is not preselected, and the Partner tab still opens.
        $html = $this->get(route('register', ['type' => 'partner', 'organization' => $this->other->id]))->assertOk()->getContent();
        $this->assertStringContainsString("type: 'partner'", $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="'.$this->other->id.'"/', $html);
    }

    public function test_after_registering_from_a_course_link_the_learner_returns_to_that_course(): void
    {
        $course = $this->course('partner', $this->sfh);
        $this->get(route('register', ['course' => $course->slug, 'type' => 'partner', 'organization' => $this->sfh->id]));

        $this->post(route('register'), $this->registrationData([
            'learner_type' => 'partner', 'partner_organization_id' => $this->sfh->id, 'partner_code' => $this->sfh->registration_code,
        ]))->assertRedirect(route('courses.show', $course));
    }

    public function test_unknown_or_draft_course_parameter_is_ignored(): void
    {
        $draft = $this->course('all', null, ['status' => Course::STATUS_DRAFT]);

        $this->get(route('register', ['course' => 'no-such-course']))->assertOk();
        $this->get(route('register', ['course' => $draft->slug]))->assertOk();

        $this->post(route('register'), $this->registrationData())->assertRedirect(route('dashboard'));
    }

    // ------------------------------------------------------------ learner dashboard

    private function dashboardHtml(User $user): string
    {
        return $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
    }

    /** The "Courses for you" block of the dashboard HTML. */
    private function availableSection(string $html): string
    {
        $from = strpos($html, 'Courses for you');
        if ($from === false) {
            $from = strpos($html, 'Other courses');
        }
        if ($from === false) {
            return '';
        }
        $to = strpos($html, 'Continue learning', $from) ?: (strpos($html, 'Courses for other groups', $from) ?: strlen($html));

        return substr($html, $from, $to - $from);
    }

    /** The "Courses for other groups" block (courses meant for other groups). */
    private function otherGroupsSection(string $html): string
    {
        $from = strpos($html, 'Courses for other groups');

        return $from === false ? '' : substr($html, $from);
    }

    public function test_dashboard_shows_available_courses_first_category_ones_before_open_ones(): void
    {
        $agent = $this->agent();
        $this->course('all', null, ['title' => 'OPEN-ONE']);
        $this->course('agents', null, ['title' => 'AGENT-MADE']);
        $this->course('all', null, ['title' => 'OPEN-TWO']);
        $this->course('partners', null, ['title' => 'FOR-PARTNERS']);

        $html = $this->dashboardHtml($agent);
        $first = $this->availableSection($html);

        foreach (['AGENT-MADE', 'OPEN-ONE', 'OPEN-TWO'] as $title) {
            $this->assertStringContainsString($title, $first);
        }
        $this->assertStringNotContainsString('FOR-PARTNERS', $first);
        $this->assertTrue(strpos($first, 'AGENT-MADE') < strpos($first, 'OPEN-ONE'), 'category courses come before open ones');

        // Courses meant for other groups come last, in their own section.
        $this->assertTrue(strpos($html, 'Courses for you') < strpos($html, 'Courses for other groups'));
        $this->assertStringContainsString('FOR-PARTNERS', $this->otherGroupsSection($html));
        $this->assertStringNotContainsString('OPEN-ONE', $this->otherGroupsSection($html));
    }

    public function test_dashboard_sections_match_the_learners_category(): void
    {
        $this->course('agents', null, ['title' => 'T-AGENTS']);
        $this->course('partners', null, ['title' => 'T-PARTNERS']);
        $this->course('partner', $this->sfh, ['title' => 'T-SFH']);
        $this->course('partner', $this->other, ['title' => 'T-OTHER']);
        $this->course('all', null, ['title' => 'T-OPEN']);

        $agentHtml = $this->dashboardHtml($this->agent());
        $this->assertSame(['T-AGENTS', 'T-OPEN'], $this->titlesIn($this->availableSection($agentHtml)));
        $this->assertSame(['T-OTHER', 'T-PARTNERS', 'T-SFH'], $this->titlesIn($this->otherGroupsSection($agentHtml)));

        // A partner's own organization gets its own section; the rest are "Other courses".
        $sfhHtml = $this->dashboardHtml($this->partner($this->sfh));
        $this->assertSame(['T-SFH'], $this->titlesIn($this->organizationSection($sfhHtml, 'SFH')));
        $this->assertSame(['T-OPEN', 'T-PARTNERS'], $this->titlesIn($this->availableSection($sfhHtml)));
        $this->assertSame(['T-AGENTS', 'T-OTHER'], $this->titlesIn($this->otherGroupsSection($sfhHtml)));
    }

    /** @return list<string> sorted T-… titles found in a chunk of HTML */
    private function titlesIn(string $html): array
    {
        preg_match_all('/T-[A-Z]+/', $html, $m);
        $found = array_values(array_unique($m[0]));
        sort($found);

        return $found;
    }

    public function test_assigned_courses_show_enrollment_state_and_progress_link(): void
    {
        $agent = $this->agent();
        $enrolled = $this->course('agents', null, ['title' => 'AGENT-ENROLLED']);
        $this->lesson($enrolled);
        $this->course('agents', null, ['title' => 'AGENT-NOT-ENROLLED']);
        Enrollment::create(['user_id' => $agent->id, 'course_id' => $enrolled->id]);

        $html = $this->dashboardHtml($agent);

        $this->assertStringContainsString('Enrolled', $html);
        $this->assertStringContainsString('Not enrolled yet', $html);
        $this->assertStringContainsString('0 / 1 lessons', $html);
        // An assigned + enrolled course is not repeated under "Continue learning".
        $this->assertSame(1, substr_count($html, 'AGENT-ENROLLED'));
    }

    public function test_other_groups_section_hides_drafts_and_courses_already_enrolled(): void
    {
        $agent = $this->agent();
        $this->course('partners', null, ['title' => 'G-PARTNERS']);
        $this->course('partners', null, ['title' => 'G-DRAFT', 'status' => Course::STATUS_DRAFT]);
        $grandfathered = $this->course('partners', null, ['title' => 'G-ENROLLED']);
        Enrollment::create(['user_id' => $agent->id, 'course_id' => $grandfathered->id]);

        $html = $this->dashboardHtml($agent);
        $other = $this->otherGroupsSection($html);

        $this->assertStringContainsString('G-PARTNERS', $other);
        $this->assertStringNotContainsString('G-DRAFT', $html);
        $this->assertStringNotContainsString('G-ENROLLED', $other);
        // An enrollment that is no longer in the learner's audience still shows under "Continue learning".
        $this->assertStringContainsString('G-ENROLLED', substr($html, strpos($html, 'Continue learning'), strpos($html, 'Courses for other groups') - strpos($html, 'Continue learning')));
    }

    public function test_courses_in_other_groups_section_are_marked_as_not_available(): void
    {
        $this->course('partners', null, ['title' => 'LOCKED-COURSE']);

        $html = $this->dashboardHtml($this->agent());

        $this->assertStringContainsString('Partners only', $this->otherGroupsSection($html));
        $this->assertStringContainsString('View details', $this->otherGroupsSection($html));
        $this->assertStringNotContainsString('Not enrolled yet', $this->otherGroupsSection($html));
    }

    public function test_dashboard_without_any_available_courses_keeps_the_original_layout(): void
    {
        $html = $this->dashboardHtml($this->agent());

        $this->assertStringNotContainsString('Courses for you', $html);
        $this->assertStringNotContainsString('Courses for other groups', $html);
        $this->assertStringContainsString('No courses yet', $html);
    }

    public function test_draft_courses_are_not_listed_on_the_dashboard(): void
    {
        $this->course('agents', null, ['title' => 'DRAFT-ASSIGNED', 'status' => Course::STATUS_DRAFT]);

        $this->assertStringNotContainsString('DRAFT-ASSIGNED', $this->dashboardHtml($this->agent()));
    }

    public function test_staff_are_still_sent_to_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_guests_cannot_open_the_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // ----------------------------------------- partner dashboard: organization section

    /** The "{Org} courses" block (partners only). */
    private function organizationSection(string $html, string $orgName): string
    {
        $from = strpos($html, $orgName.' courses');
        if ($from === false) {
            return '';
        }
        $ends = array_filter([
            strpos($html, 'Other courses', $from),
            strpos($html, 'Continue learning', $from),
            strpos($html, 'Courses for other groups', $from),
        ], fn ($pos) => $pos !== false);

        return substr($html, $from, ($ends ? min($ends) : strlen($html)) - $from);
    }

    public function test_partner_dashboard_has_an_organization_section_then_other_courses(): void
    {
        $this->course('partner', $this->sfh, ['title' => 'T-SFHONE']);
        $this->course('partner', $this->sfh, ['title' => 'T-SFHTWO']);
        $this->course('partner', $this->other, ['title' => 'T-OTHERORG']);
        $this->course('partners', null, ['title' => 'T-ALLPARTNERS']);
        $this->course('agents', null, ['title' => 'T-AGENTSONLY']);
        $this->course('all', null, ['title' => 'T-OPEN']);

        $html = $this->dashboardHtml($this->partner($this->sfh));

        $org = $this->organizationSection($html, 'SFH');
        $this->assertSame(['T-SFHONE', 'T-SFHTWO'], $this->titlesIn($org));

        $others = $this->availableSection($html);
        $this->assertSame(['T-ALLPARTNERS', 'T-OPEN'], $this->titlesIn($others));

        $this->assertSame(['T-AGENTSONLY', 'T-OTHERORG'], $this->titlesIn($this->otherGroupsSection($html)));

        // Order on the page: organization courses, other courses, other groups.
        $this->assertTrue(strpos($html, 'SFH courses') < strpos($html, 'Other courses'));
        $this->assertTrue(strpos($html, 'Other courses') < strpos($html, 'Courses for other groups'));
    }

    public function test_organization_section_only_lists_the_partners_own_organization(): void
    {
        $this->course('partner', $this->sfh, ['title' => 'T-SFHONLY']);
        $this->course('partner', $this->other, ['title' => 'T-OTHERONLY']);

        $otherPartnerHtml = $this->dashboardHtml($this->partner($this->other));

        $this->assertStringContainsString('Other Org courses', $otherPartnerHtml);
        $this->assertSame(['T-OTHERONLY'], $this->titlesIn($this->organizationSection($otherPartnerHtml, 'Other Org')));
        $this->assertStringNotContainsString('T-SFHONLY', $this->organizationSection($otherPartnerHtml, 'Other Org'));
    }

    public function test_agents_and_partners_without_organization_courses_get_no_organization_section(): void
    {
        $this->course('all', null, ['title' => 'T-OPEN']);

        $agentHtml = $this->dashboardHtml($this->agent());
        $this->assertStringNotContainsString('SFH courses', $agentHtml);
        $this->assertStringContainsString('Courses for you', $agentHtml);

        $partnerHtml = $this->dashboardHtml($this->partner($this->sfh));
        $this->assertStringNotContainsString('SFH courses', $partnerHtml);
        $this->assertStringContainsString('Courses for you', $partnerHtml);
    }

    public function test_organization_course_card_shows_enrollment_state(): void
    {
        $partner = $this->partner($this->sfh);
        $enrolled = $this->course('partner', $this->sfh, ['title' => 'T-ENROLLED']);
        $this->course('partner', $this->sfh, ['title' => 'T-PENDING']);
        Enrollment::create(['user_id' => $partner->id, 'course_id' => $enrolled->id]);

        $org = $this->organizationSection($this->dashboardHtml($partner), 'SFH');

        $this->assertStringContainsString('Enrolled', $org);
        $this->assertStringContainsString('Not enrolled yet', $org);
        // Not repeated under Continue learning.
        $this->assertSame(1, substr_count($this->dashboardHtml($partner), 'T-ENROLLED'));
    }

    public function test_occupation_field_is_gone_from_forms_and_is_not_saved(): void
    {
        $this->get(route('register'))->assertOk()->assertDontSee('Occupation');
        $this->actingAs($this->agent())->get(route('profile.edit'))->assertOk()->assertDontSee('Occupation');

        $data = $this->registrationData(['occupation' => 'Farmer']);
        $this->post(route('register'), $data)->assertSessionDoesntHaveErrors();

        $this->assertFalse(\Schema::hasColumn('users', 'occupation'));
    }
}
