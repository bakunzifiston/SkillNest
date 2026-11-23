<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Documentation;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Admin > Documentation: read the User Guide and the System Documentation online, or download them. */
class DocumentationController extends Controller
{
    public function index(): View
    {
        $books = collect(Documentation::books())->map(function ($info, $key) {
            $chapters = Documentation::chapters($key);

            return $info + [
                'key' => $key,
                'chapters' => $chapters->map(fn ($c) => ['slug' => $c['slug'], 'title' => $c['title'], 'number' => $c['number']]),
                'updatedAt' => Documentation::updatedAt($key),
            ];
        });

        return view('admin.documentation.index', compact('books'));
    }

    public function show(string $book): View
    {
        abort_unless(Documentation::exists($book), 404);

        return view('admin.documentation.show', [
            'book' => $book,
            'info' => Documentation::books()[$book],
            'chapters' => Documentation::chapters($book),
            'updatedAt' => Documentation::updatedAt($book),
        ]);
    }

    public function download(string $book, string $format): Response
    {
        abort_unless(Documentation::exists($book) && in_array($format, ['html', 'md', 'pdf'], true), 404);
        abort_if(Documentation::chapters($book)->isEmpty(), 404);

        $name = Str::slug(config('app.name').'-'.$book).'-'.now()->format('Y-m-d');

        if ($format === 'md') {
            return response(Documentation::combinedMarkdown($book), 200, [
                'Content-Type' => 'text/markdown; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$name.'.md"',
            ]);
        }

        if ($format === 'pdf') {
            // No PDF library is installed: open the print-ready page and let the browser's Print > Save as PDF do the work.
            return response(Documentation::standaloneHtml($book, autoPrint: true), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return response(Documentation::standaloneHtml($book), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$name.'.html"',
        ]);
    }

    public function image(string $book, string $file): BinaryFileResponse
    {
        $path = Documentation::image($book, $file);
        abort_unless($path, 404);

        return response()->file($path, ['Cache-Control' => 'private, max-age=3600']);
    }
}
