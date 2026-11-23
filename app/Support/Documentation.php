<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

/**
 * The platform's written documentation. The source of truth is Markdown in /docs:
 *
 *   docs/user-guide/NN-name.md   chapters of the User Guide (learners and administrators)
 *   docs/system/NN-name.md       chapters of the System Documentation
 *   docs/<book>/images/*.png     screenshots used by that book
 *
 * Chapters are ordered by their file name; a chapter's title is its first "# " line. Nothing here is stored in
 * the database, so updating the documentation means editing the files (see docs/README.md).
 */
class Documentation
{
    /** @return array<string, array{title: string, description: string}> */
    public static function books(): array
    {
        return [
            'user-guide' => [
                'title' => __('User Guide'),
                'description' => __('Step-by-step instructions for learners and administrators.'),
            ],
            'system' => [
                'title' => __('System Documentation'),
                'description' => __('Architecture, modules, database, workflows, integrations and configuration.'),
            ],
        ];
    }

    public static function exists(string $book): bool
    {
        return array_key_exists($book, self::books());
    }

    public static function path(string $book, string $file = ''): string
    {
        return base_path('docs/'.$book.($file !== '' ? '/'.$file : ''));
    }

    /**
     * Chapters of a book, in order: slug, title, raw Markdown, rendered HTML and the headings for the table of contents.
     *
     * @return Collection<int, array{slug: string, number: int, title: string, markdown: string, html: string, headings: list<array{level: int, id: string, text: string}>, file: string}>
     */
    public static function chapters(string $book): Collection
    {
        if (! self::exists($book) || ! File::isDirectory(self::path($book))) {
            return collect();
        }

        return collect(File::files(self::path($book)))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->sortBy(fn ($file) => $file->getFilename(), SORT_NATURAL)
            ->values()
            ->map(function ($file, int $i) {
                $markdown = trim(File::get($file->getPathname()));
                $slug = Str::slug(preg_replace('/^\d+-/', '', $file->getFilenameWithoutExtension()));
                $title = preg_match('/^#\s+(.+)$/m', $markdown, $m) ? trim($m[1]) : Str::headline($slug);
                $html = self::render($markdown, $slug);

                return [
                    'slug' => $slug,
                    'number' => $i + 1,
                    'title' => $title,
                    'markdown' => $markdown,
                    'html' => $html,
                    'headings' => self::headings($html),
                    'file' => $file->getFilename(),
                ];
            });
    }

    /** Render one chapter. Raw HTML in the Markdown is not passed through, and heading ids are prefixed per chapter so they stay unique. */
    public static function render(string $markdown, string $prefix = ''): string
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'doc-anchor',
                'id_prefix' => $prefix !== '' ? $prefix : 'doc',
                'fragment_prefix' => $prefix !== '' ? $prefix : 'doc',
                'insert' => 'after',
                'symbol' => '#',
                'aria_hidden' => true,
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $html = (new MarkdownConverter($environment))->convert($markdown)->getContent();

        // Notes, tips and warnings: a blockquote that starts with "Note:", "Tip:" or "Warning:" gets its own colour.
        $html = preg_replace_callback('/<blockquote>\s*<p>(?:<strong>)?(Note|Tip|Warning|Important)\b/i', fn ($m) => '<blockquote class="doc-'.strtolower($m[1]).'"><p>'.(str_contains($m[0], '<strong>') ? '<strong>' : '').$m[1], $html);

        // A screenshot on its own line becomes a figure with its description as the caption.
        return preg_replace('/<p>\s*<img ([^>]*?)alt="([^"]*)"([^>]*?)\/?>\s*<\/p>/i', '<figure class="doc-figure"><img $1alt="$2"$3><figcaption>$2</figcaption></figure>', $html);
    }

    /** @return list<array{level: int, id: string, text: string}> H2 and H3 headings with their anchor ids. */
    public static function headings(string $html): array
    {
        preg_match_all('/<h([23])>(.*?)<a id="([^"]+)"[^>]*class="doc-anchor"/s', $html, $m, PREG_SET_ORDER);

        return array_map(fn ($h) => ['level' => (int) $h[1], 'id' => $h[3], 'text' => trim(html_entity_decode(strip_tags($h[2])))], $m);
    }

    /** The date of the most recent change to a book's files (shown on the cover of downloads). */
    public static function updatedAt(string $book): ?\Illuminate\Support\Carbon
    {
        $files = File::isDirectory(self::path($book)) ? File::allFiles(self::path($book)) : [];
        $latest = collect($files)->max(fn ($f) => $f->getMTime());

        return $latest ? \Illuminate\Support\Carbon::createFromTimestamp($latest) : null;
    }

    /** Absolute path of a screenshot, or null. Only plain file names inside the book's images folder are served. */
    public static function image(string $book, string $file): ?string
    {
        if (! self::exists($book) || ! preg_match('/^[A-Za-z0-9._-]+\.(png|jpe?g|gif|webp|svg)$/i', $file)) {
            return null;
        }

        $path = self::path($book, 'images/'.$file);

        return File::isFile($path) ? $path : null;
    }

    /** One Markdown file with a title block, contents list and every chapter, for download. */
    public static function combinedMarkdown(string $book): string
    {
        $info = self::books()[$book];
        $chapters = self::chapters($book);
        $out = '# '.config('app.name').' — '.$info['title']."\n\n";
        $out .= '*Generated on '.now()->format('F j, Y').'*'."\n\n## Contents\n\n";

        foreach ($chapters as $c) {
            $out .= $c['number'].'. '.$c['title']."\n";
        }

        foreach ($chapters as $c) {
            // Chapter titles become H2 in the combined file; their own sections go one level down.
            $body = preg_replace_callback('/^(#{1,3}) /m', fn ($m) => str_repeat('#', min(strlen($m[1]) + 1, 4)).' ', $c['markdown']);
            $out .= "\n\n---\n\n".$body;
        }

        return $out."\n";
    }

    /** A self-contained, print-ready HTML page (images embedded). In a browser it can be saved as PDF with Print. */
    public static function standaloneHtml(string $book, bool $autoPrint = false): string
    {
        $info = self::books()[$book];
        $chapters = self::chapters($book)->map(function ($c) use ($book) {
            $c['html'] = preg_replace_callback('/<img([^>]*?)src="images\/([^"]+)"/i', function ($m) use ($book) {
                $path = self::image($book, $m[2]);
                if (! $path) {
                    return $m[0];
                }
                $mime = File::mimeType($path) ?: 'image/png';

                return '<img'.$m[1].'src="data:'.$mime.';base64,'.base64_encode(File::get($path)).'"';
            }, $c['html']);

            return $c;
        });

        return view('admin.documentation.standalone', [
            'book' => $book,
            'info' => $info,
            'chapters' => $chapters,
            'updatedAt' => self::updatedAt($book),
            'autoPrint' => $autoPrint,
        ])->render();
    }
}
