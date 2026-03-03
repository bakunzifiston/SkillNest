<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    /**
     * Serve course banner images from storage (works even when storage:link is missing).
     */
    public function courseImage(Request $request, string $path): StreamedResponse
    {
        // Allow paths that start with "courses/", "bundles/", "site/", or "partners/" and prevent directory traversal
        $path = str_replace('\\', '/', $path);
        $allowed = str_starts_with($path, 'courses/') || str_starts_with($path, 'bundles/') || str_starts_with($path, 'site/') || str_starts_with($path, 'partners/');
        if (! $allowed || str_contains($path, '..')) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        return response()->stream(function () use ($path) {
            $stream = Storage::disk('public')->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
