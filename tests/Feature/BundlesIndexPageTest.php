<?php

namespace Tests\Feature;

use App\Models\Bundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BundlesIndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundles_index_shows_cleaned_catalog_ui(): void
    {
        Bundle::create([
            'title' => 'Agent Starter Path',
            'slug' => 'agent-starter-path',
            'description' => 'A curated set of courses for new agents.',
            'status' => Bundle::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('bundles.index'));

        $response->assertOk();
        $response->assertSee('Course Bundles', false);
        $response->assertSee('Agent Starter Path', false);
        $response->assertSee('View bundle', false);
        $response->assertDontSee('📦', false);
    }

    public function test_draft_bundles_are_not_listed(): void
    {
        Bundle::create([
            'title' => 'Hidden Draft',
            'slug' => 'hidden-draft',
            'description' => 'Not ready yet.',
            'status' => Bundle::STATUS_DRAFT,
        ]);

        $this->get(route('bundles.index'))
            ->assertOk()
            ->assertDontSee('Hidden Draft', false);
    }
}
