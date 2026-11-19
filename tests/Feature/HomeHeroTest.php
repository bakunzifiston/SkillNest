<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The home page banner: floating pill menu, brand-blue hero, circular photo, curved bottom. */
class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_has_the_new_banner_and_the_floating_menu(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('class="home-hero"', $html);
        $this->assertMatchesRegularExpression('/<body class="[^"]*\bhome-page\b/', $html);   // switches the menu to the floating pill
        $this->assertStringContainsString('home-hero-photo', $html);
        $this->assertStringContainsString('home-hero-curve', $html);
        $this->assertStringContainsString('images/banner/agents-field.jpg', $html);           // the agents photo with the KoraLink logo on their vests
        $this->assertStringContainsString('images/brand/logo-mark.png', $html);              // the brand logo is still in the menu
    }

    public function test_the_banner_uses_only_the_brand_colours(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();
        $css = substr($html, strpos($html, '/* ---- Home hero'), 14000);
        $css = substr($css, 0, strpos($css, '</style>'));

        foreach (['#F16029', '#19499B', '#153F86', '#102C5E', '#D95420'] as $brand) {
            $this->assertStringContainsString($brand, $css, "brand colour {$brand} should be used in the hero");
        }
        // No off-brand colours: every hex colour in the hero css is from the brand palette (or white/black/neutral).
        preg_match_all('/#[0-9A-Fa-f]{6}\b/', $css, $m);
        $allowed = ['#F16029', '#19499B', '#153F86', '#102C5E', '#D95420', '#0B1B33', '#FFFFFF', '#475569', '#C9D6EE', '#000000', '#F8FAFC', '#E8EEF7'];
        foreach (array_unique(array_map('strtoupper', $m[0])) as $hex) {
            $this->assertContains($hex, $allowed, "{$hex} is not a brand colour");
        }
    }

    public function test_the_home_hero_class_is_only_on_the_home_page(): void
    {
        foreach ([route('courses.index'), route('about'), route('contact'), route('login')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertDoesNotMatchRegularExpression('/<body class="[^"]*\bhome-page\b/', $html, "{$url} is not the home page");
            $this->assertStringNotContainsString('class="home-hero"', $html);
        }
    }

    public function test_the_banner_works_in_both_languages_and_keeps_all_its_text(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Build Digital Skills.')->assertSee('Grow Your Impact.')
            ->assertSee('Explore Courses')->assertSee('Learn About KoraLink');

        $this->post(route('locale.update'), ['locale' => 'rw']);
        $rw = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Iyubakemo ubumenyi mu ikoranabuhanga.', $rw);
        $this->assertStringContainsString('Zamura umusaruro mubyo ukora.', $rw);
        $this->assertStringContainsString('Reba amasomo', $rw);
        $this->assertStringContainsString("Ishuri ry&#039;Aba-agent rya KoraLink", $rw);
    }

    public function test_the_hero_photo_exists(): void
    {
        $this->assertFileExists(public_path('images/banner/agents-field.jpg'));
    }

    public function test_the_call_to_action_card_sits_right_under_the_categories(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $categories = strpos($html, 'id="categories"');
        $card = strpos($html, 'class="home-cta"');
        $courses = strpos($html, '<!-- featured -->') ?: strpos($html, 'Start Learning Today');
        $partners = strpos($html, 'Our Partners');

        $this->assertNotFalse($categories);
        $this->assertNotFalse($card);
        $this->assertTrue($categories < $card, 'the card comes after the categories');
        $this->assertTrue($card < $courses, 'the card comes before the featured courses');
        $this->assertTrue($card < $partners, 'the card is no longer at the end of the page');
        $this->assertSame(1, substr_count($html, 'class="home-cta"'), 'the card appears once');
        $this->assertStringNotContainsString('Ready to grow your digital skills?</h2>', $html);
    }

    public function test_the_call_to_action_card_has_the_same_width_as_the_other_sections(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        // It sits in the standard page container (like the category cards) and has no narrower width of its own.
        $this->assertMatchesRegularExpression('/<section class="home-cta">\s*<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">\s*<div class="home-cta-card"/', $html);
        preg_match('/\.home-cta-card \{[^}]*\}/', $html, $rule);
        $this->assertNotEmpty($rule);
        $this->assertStringNotContainsString('max-width', $rule[0]);
    }

    public function test_the_page_has_the_call_to_action_card(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('class="home-cta"', $html);
        $this->assertStringContainsString('home-cta-card" data-reveal', $html);         // fades in like the other cards
        $this->assertStringContainsString('home-btn home-btn--white', $html);          // the white pill button
        $this->assertStringContainsString('home-cta-bar--a', $html);                   // the orange bars
        $this->assertStringContainsString('home-cta-badge', $html);                    // the award badge
        $this->assertStringContainsString('Ready to grow', $html);
        $this->assertStringContainsString('your digital skills?', $html);
        $this->assertStringContainsString('aria-label="Ready to grow your digital skills?"', $html);   // screen readers read the whole sentence
        $this->assertStringNotContainsString('py-16 lg:py-20 bg-navy text-white', $html);   // the old plain navy band is gone


        // The card has a single button: Contact us (no Explore Courses / About buttons any more).
        preg_match('/<div class="home-cta-actions">.*?<\/div>/s', $html, $actions);
        $this->assertNotEmpty($actions);
        $this->assertSame(1, substr_count($actions[0], '<a '));
        $this->assertStringContainsString('href="'.route('contact').'"', $actions[0]);
        $this->assertStringContainsString('Contact us', $actions[0]);
        $this->assertStringNotContainsString('Explore Courses', $actions[0]);
        $this->assertStringNotContainsString('About KoraLink', $actions[0]);
    }

    public function test_the_call_to_action_reads_naturally_in_kinyarwanda(): void
    {
        $this->post(route('locale.update'), ['locale' => 'rw']);
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('Witeguye kongera', $html);
        $this->assertStringContainsString('ubumenyi bwawe mu ikoranabuhanga?', $html);
        $this->assertStringContainsString('aria-label="Witeguye kongera ubumenyi bwawe mu ikoranabuhanga?"', $html);

        preg_match('/<div class="home-cta-actions">.*?<\/div>/s', $html, $actions);
        $this->assertStringContainsString('Twandikire', $actions[0]);            // "Contact us"
        $this->assertStringNotContainsString('Reba amasomo', $actions[0]);       // the old buttons are gone
        $this->assertStringNotContainsString('Abo turi bo kuri KoraLink', $actions[0]);
    }

    public function test_the_card_description_uses_the_chosen_kinyarwanda_wording(): void
    {
        $this->post(route('locale.update'), ['locale' => 'rw']);

        $this->get(route('home'))->assertOk()->assertSee(
            'Reba amasomo yagufasha kwiga, kunoza akazi kawe no kugira uruhare runini mwiterambere ryumuryango.'
        );
    }
}
