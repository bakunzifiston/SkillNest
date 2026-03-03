<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-4">{{ __("You're logged in!") }}</p>
                    @unless(auth()->user()->is_admin ?? false)
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('courses.my-courses') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">My courses</a>
                            <a href="{{ route('bundles.my-bundles') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">My bundles</a>
                        </div>
                    @endunless
                </div>
            </div>

            @unless(auth()->user()->is_admin ?? false)
                @php
                    $bundleEnrollments = auth()->user()->bundleEnrollments()->with('bundle')->latest('enrolled_at')->take(5)->get();
                @endphp
                @if($bundleEnrollments->isNotEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Bundle progress</h3>
                            <p class="text-sm text-gray-500 mb-4">Progress is the share of courses in the bundle you’ve completed (all lessons in a course = 1 course done).</p>
                            <ul class="space-y-3">
                                @foreach($bundleEnrollments as $be)
                                    @php $be->refreshProgress(); @endphp
                                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                        <a href="{{ route('bundles.show', $be->bundle) }}" class="font-medium text-gray-900 hover:text-amber-600">{{ $be->bundle->title }}</a>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm text-gray-600">{{ $be->completed_courses }} / {{ $be->total_courses }} courses</span>
                                            <span class="text-sm font-semibold text-amber-600">{{ (int) $be->bundle_completion_percentage }}%</span>
                                            <div class="w-20 h-2 rounded-full bg-gray-200 overflow-hidden">
                                                <div class="h-full rounded-full bg-amber-500" style="width: {{ (int) $be->bundle_completion_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('bundles.my-bundles') }}" class="inline-block mt-4 text-sm text-amber-600 font-medium hover:underline">View all my bundles →</a>
                        </div>
                    </div>
                @endif
            @endunless
        </div>
    </div>
</x-app-layout>
