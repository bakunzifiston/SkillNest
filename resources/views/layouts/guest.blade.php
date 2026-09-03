<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <x-brand-meta />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:pt-0 bg-gradient-to-br from-primary-light via-slate-50 to-accent-light">
            <div class="mb-2">
                <a href="/" class="block">
                    <x-brand-logo size="xl" class="justify-center" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-brand overflow-hidden rounded-2xl border border-primary-muted/60">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
