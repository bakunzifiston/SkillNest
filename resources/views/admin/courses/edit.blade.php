@extends('layouts.admin')

@section('title', 'Edit Course')
@section('header', $course->title)

@section('content')
    @php $activeTab = request('tab', session('tab', 'overview')); @endphp

    {{-- Clear tabs (Thinkific-style) - very visible --}}
    <div class="bg-white rounded-xl border border-gray-200 p-2 mb-8 inline-flex gap-1 shadow-sm">
        <a href="{{ route('admin.courses.edit', $course) }}?tab=overview" class="px-6 py-3 rounded-lg font-medium text-sm {{ $activeTab === 'overview' ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Overview
        </a>
        <a href="{{ route('admin.courses.edit', $course) }}?tab=curriculum" class="px-6 py-3 rounded-lg font-medium text-sm {{ $activeTab === 'curriculum' ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Curriculum
        </a>
    </div>
    <p class="text-gray-500 text-sm mb-6">Use <strong>Overview</strong> to edit details and pricing; use <strong>Curriculum</strong> to add sections and lessons.</p>

    {{-- Tab: Overview --}}
    <div id="tab-overview" class="{{ $activeTab === 'overview' ? '' : 'hidden' }}">
        <div class="max-w-2xl">
            <form action="{{ route('admin.courses.update', $course) }}" method="post" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $course->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-700">Instructor</label>
                    <select name="instructor_id" id="instructor_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Select instructor (optional)</option>
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->id }}" {{ old('instructor_id', $course->instructor_id) == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Course title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description', $course->description) }}</textarea>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Pricing</span>
                    @php $isFree = old('is_free', $course->price == 0 ? '1' : '0'); @endphp
                    <div class="flex gap-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_free" value="1" {{ $isFree == '1' ? 'checked' : '' }} class="rounded-full border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="ml-2">Free</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_free" value="0" {{ $isFree == '0' ? 'checked' : '' }} class="rounded-full border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="ml-2">Paid</span>
                        </label>
                    </div>
                </div>
                <div id="price-field" class="{{ ($course->price ?? 0) == 0 ? 'hidden' : '' }}">
                    <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $course->price) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm max-w-xs">
                </div>
                <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                    <label for="banner" class="block text-sm font-medium text-gray-700">Course banner</label>
                    @if($course->banner_url)
                        <div class="mt-2 mb-3">
                            <p class="text-xs text-gray-500 mb-1">Current banner:</p>
                            <img src="{{ $course->banner_url }}" alt="Course banner" class="rounded-lg border border-gray-200 max-h-32 object-cover">
                            <p class="mt-1 text-xs text-gray-500">Upload a new file below to replace.</p>
                        </div>
                    @endif
                    <input type="file" name="banner" id="banner" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
                    <p class="mt-1 text-xs text-gray-500">JPEG, PNG, GIF or WebP. Max 2MB.</p>
                    @error('banner')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duration</label>
                        <input type="text" name="duration" id="duration" value="{{ old('duration', $course->duration) }}" placeholder="e.g. 8 hours" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                        <select name="level" id="level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="beginner" {{ old('level', $course->level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ old('level', $course->level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ old('level', $course->level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Save course</button>
                    <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Back to courses</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tab: Curriculum (Thinkific-style sections + lessons) --}}
    <div id="tab-curriculum" class="{{ $activeTab === 'curriculum' ? '' : 'hidden' }}">
        <div class="max-w-4xl">
            <p class="text-gray-600 mb-6">Build your course with sections and lessons. Add sections to group lessons, then add lessons (text, video, PDF, or YouTube).</p>

            <div class="space-y-6">
                @foreach($course->chapters as $chapter)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    {{-- Section header --}}
                    <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 text-lg font-bold">{{ $loop->iteration }}.</span>
                            <h3 class="font-semibold text-gray-900">{{ $chapter->title }}</h3>
                            <span class="text-sm text-gray-500">({{ $chapter->lessons->count() }} lesson{{ $chapter->lessons->count() !== 1 ? 's' : '' }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.chapters.edit', $chapter) }}" class="text-sm text-gray-600 hover:text-amber-600">Edit</a>
                            <form action="{{ route('admin.chapters.destroy', $chapter) }}" method="post" class="inline" onsubmit="return confirm('Delete this section and all its lessons?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                            <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="ml-2 text-sm px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600">Add lesson</a>
                        </div>
                    </div>
                    {{-- Lessons list --}}
                    <ul class="divide-y divide-gray-100">
                        @forelse($chapter->lessons as $lesson)
                        <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                @if($lesson->type === 'video')
                                    <span class="flex items-center justify-center w-8 h-8 rounded bg-red-100 text-red-600" title="Video"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg></span>
                                @elseif($lesson->type === 'youtube')
                                    <span class="flex items-center justify-center w-8 h-8 rounded bg-red-100 text-red-600" title="YouTube"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></span>
                                @elseif($lesson->type === 'pdf')
                                    <span class="flex items-center justify-center w-8 h-8 rounded bg-orange-100 text-orange-600" title="PDF"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg></span>
                                @else
                                    <span class="flex items-center justify-center w-8 h-8 rounded bg-gray-200 text-gray-600" title="Text"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a1 1 0 011-1h10a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h10a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zm0 6a1 1 0 011-1h6a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2z" clip-rule="evenodd"/></svg></span>
                                @endif
                                <span class="font-medium text-gray-900">{{ $lesson->title }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-sm text-amber-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="post" class="inline" onsubmit="return confirm('Delete this lesson?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </li>
                        @empty
                        <li class="px-5 py-6 text-center text-gray-500 text-sm">
                            No lessons in this section. <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="text-amber-600 hover:underline">Add lesson</a>
                        </li>
                        @endforelse
                    </ul>
                </div>
                @endforeach
            </div>

            <div class="mt-8">
                <a href="{{ route('admin.courses.chapters.create', $course) }}" class="inline-flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-amber-400 hover:text-amber-600 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add section
                </a>
            </div>

            @if($course->chapters->isEmpty())
            <div class="mt-6 p-6 bg-gray-50 rounded-xl border border-gray-200 text-center text-gray-600">
                <p class="mb-3">You don’t have any sections yet.</p>
                <a href="{{ route('admin.courses.chapters.create', $course) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add your first section</a>
            </div>
            @endif
        </div>
    </div>

    <script>
        (function() {
            var tab = '{{ $activeTab }}';
            if (tab === 'curriculum') document.getElementById('tab-curriculum').classList.remove('hidden');
            if (tab === 'overview') document.getElementById('tab-overview').classList.remove('hidden');
        })();
        document.querySelectorAll('input[name="is_free"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('price-field').classList.toggle('hidden', this.value === '1');
            });
        });
    </script>
@endsection
