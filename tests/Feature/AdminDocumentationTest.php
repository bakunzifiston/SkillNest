<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Admin > Documentation: reading and downloading the User Guide and the System Documentation. */
class AdminDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_books_have_chapters_with_titles_and_unique_slugs(): void
    {
        foreach (array_keys(Documentation::books()) as $book) {
            $chapters = Documentation::chapters($book);

            $this->assertGreaterThan(0, $chapters->count(), "{$book} has no chapters");
            $this->assertSame($chapters->count(), $chapters->pluck('slug')->unique()->count(), "{$book} has duplicate chapter slugs");
            foreach ($chapters as $c) {
                $this->assertStringStartsWith('# ', $c['markdown'], "{$c['file']} must start with a '# Title' line");
                $this->assertNotSame('', $c['title']);
            }
        }
    }

    public function test_every_image_the_chapters_use_exists(): void
    {
        $missing = [];

        foreach (array_keys(Documentation::books()) as $book) {
            foreach (Documentation::chapters($book) as $c) {
                preg_match_all('/!\[[^\]]*\]\(images\/([^)]+)\)/', $c['markdown'], $m);
                foreach ($m[1] as $file) {
                    if (Documentation::image($book, $file) === null) {
                        $missing[] = "{$book}/{$c['file']}: {$file}";
                    }
                }
            }
        }

        $this->assertSame([], $missing, 'Images referenced but missing in docs/*/images: '.implode(', ', $missing));
    }

    public function test_admin_sees_the_documentation_page_with_both_books_and_download_links(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.documentation.index'))
            ->assertOk()
            ->assertSee('User Guide')->assertSee('System Documentation')
            ->assertSee(route('admin.documentation.download', ['user-guide', 'html']), false)
            ->assertSee(route('admin.documentation.download', ['system', 'md']), false);
    }

    public function test_documentation_is_in_the_admin_navigation(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.documentation.index'), false);
    }

    public function test_a_book_can_be_read_online_with_a_table_of_contents(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Documentation::chapters('user-guide')->first();

        $this->actingAs($admin)->get(route('admin.documentation.show', 'user-guide'))
            ->assertOk()->assertSee('Table of contents')->assertSee('id="'.$first['slug'].'"', false)->assertSee($first['title']);
        $this->get(route('admin.documentation.show', 'nope'))->assertNotFound();
    }

    public function test_the_printable_html_download_is_a_complete_standalone_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.documentation.download', ['system', 'html']))->assertOk();

        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));
        $html = $response->getContent();
        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('class="cover"', $html);
        $this->assertStringNotContainsString('src="images/', $html); // images are embedded or absent, never relative links
        foreach (Documentation::chapters('system') as $c) {
            $this->assertStringContainsString('id="'.$c['slug'].'"', $html);
        }
    }

    public function test_the_pdf_link_opens_a_print_ready_page_that_starts_the_print_dialog(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.documentation.download', ['user-guide', 'pdf']))->assertOk();

        $this->assertNull($response->headers->get('Content-Disposition')); // shown in the browser, not saved as a file
        $this->assertStringContainsString('window.print()', $response->getContent());
        $this->assertStringContainsString('class="cover"', $response->getContent());
        // The plain HTML download must not open the print dialog by itself.
        $this->get(route('admin.documentation.download', ['user-guide', 'html']))->assertDontSee("window.addEventListener('load'", false);
        $this->get(route('admin.documentation.index'))->assertSee('Download PDF');
    }

    public function test_the_markdown_download_contains_every_chapter(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.documentation.download', ['user-guide', 'md']))->assertOk();

        $this->assertStringContainsString('.md"', $response->headers->get('Content-Disposition'));
        foreach (Documentation::chapters('user-guide') as $c) {
            $this->assertStringContainsString($c['title'], $response->getContent());
        }
    }

    public function test_unknown_books_and_formats_are_not_found(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/documentation/system/download/exe')->assertNotFound();
        $this->get('/admin/documentation/secrets/download/md')->assertNotFound();
    }

    public function test_images_are_served_only_from_the_books_image_folders(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/documentation/system/image/..%2F..%2F.env')->assertNotFound();
        $this->get('/admin/documentation/system/image/not-there.png')->assertNotFound();
        $this->assertNull(Documentation::image('system', '../README.md'));
        $this->assertNull(Documentation::image('system', 'notes.txt'));
    }

    public function test_raw_html_in_documentation_is_not_rendered(): void
    {
        $html = Documentation::render("# T\n\n<script>alert(1)</script>\n\n[x](javascript:alert(1))\n");

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_visitors_and_learners_cannot_open_the_documentation(): void
    {
        $this->get(route('admin.documentation.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.documentation.index'))->assertForbidden();
    }

    public function test_staff_need_the_documentation_permission(): void
    {
        $role = Role::query()->create(['name' => 'Editor', 'slug' => 'editor', 'description' => 'x']);
        $role->syncPermissions(['dashboard' => ['view']]);
        $staff = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($staff)->get(route('admin.documentation.index'))->assertForbidden();
        $this->get(route('admin.documentation.download', ['system', 'md']))->assertForbidden();

        $role->syncPermissions(['dashboard' => ['view'], 'documentation' => ['view']]);

        $this->actingAs($staff->fresh())->get(route('admin.documentation.index'))->assertOk();
    }
}
