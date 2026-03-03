@extends('layouts.admin')

@section('title', 'Add Live Session')
@section('header', 'Add Live Session')

@section('content')
    <div class="max-w-xl">
        <p class="text-sm text-gray-500 mb-4">Create the meeting in Zoom, Google Meet, or another tool, then paste the join link here.</p>
        <form action="{{ route('admin.live-sessions.store') }}" method="post" class="space-y-5">
            @csrf
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                <select name="course_id" id="course_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Select course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id', $selectedCourseId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
                @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Week 1 Q&A" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Date & time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('scheduled_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="5" max="480" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="meeting_url" class="block text-sm font-medium text-gray-700">Meeting URL</label>
                <input type="url" name="meeting_url" id="meeting_url" value="{{ old('meeting_url') }}" required placeholder="https://zoom.us/j/..." class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('meeting_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="meeting_password" class="block text-sm font-medium text-gray-700">Meeting password (optional)</label>
                <input type="text" name="meeting_password" id="meeting_password" value="{{ old('meeting_password') }}" placeholder="e.g. 123456" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('meeting_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="invitees-wrap">
                <p class="text-sm font-medium text-gray-700 mb-2">Invite students (they will receive an email notification)</p>
                <div class="flex flex-wrap gap-2 mb-2" id="invited-tags"></div>
                <div class="relative" id="invitee-wrap">
                    <input type="text" id="invitee-search" placeholder="Search or click to see list…" autocomplete="off" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" disabled data-url="{{ url('admin/courses') }}">
                    <div id="invitee-dropdown" class="absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto hidden"></div>
                </div>
                <p class="mt-1.5 text-sm text-gray-500">
                    <button type="button" id="invitee-show-list" class="text-amber-600 hover:text-amber-700 font-medium hidden">Show dropdown list</button>
                    <span id="invitee-hint" class="text-gray-500">Select a course first.</span>
                </p>
                <input type="hidden" name="invited_user_ids" id="invited_user_ids_input" value="">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Create live session</button>
                <a href="{{ route('admin.live-sessions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var courseId = null;
            var selected = [];
            var searchTimeout = null;
            var baseUrl = '{{ url("admin/courses") }}';

            function updateHiddenInput() {
                document.getElementById('invited_user_ids_input').value = selected.join(',');
            }

            function renderTags() {
                var el = document.getElementById('invited-tags');
                el.innerHTML = '';
                selected.forEach(function (id) {
                    var tag = document.createElement('span');
                    tag.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm bg-amber-100 text-amber-800';
                    tag.dataset.id = id;
                    tag.innerHTML = ((window._invitedNames && window._invitedNames[id]) || 'ID ' + id) + ' <button type="button" class="hover:text-amber-900">&times;</button>';
                    tag.querySelector('button').onclick = function () {
                        selected = selected.filter(function (i) { return i !== id; });
                        delete (window._invitedNames || {})[id];
                        renderTags();
                        updateHiddenInput();
                    };
                    el.appendChild(tag);
                });
                updateHiddenInput();
            }

            function showDropdown(users) {
                var el = document.getElementById('invitee-dropdown');
                el.innerHTML = '';
                if (!users.length) {
                    el.innerHTML = '<div class="p-3 text-sm text-gray-500">No students found. Try a different search or check that the course has enrollments.</div>';
                } else {
                    users.forEach(function (u) {
                        if (selected.indexOf(String(u.id)) !== -1) return;
                        var row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'w-full text-left px-3 py-2 text-sm hover:bg-amber-50 border-b border-gray-100 last:border-0';
                        row.textContent = (u.name || u.email) + ' (' + u.email + ')';
                        row.onclick = function () {
                            selected.push(String(u.id));
                            window._invitedNames = window._invitedNames || {};
                            window._invitedNames[u.id] = (u.name || u.email);
                            renderTags();
                            document.getElementById('invitee-search').value = '';
                            document.getElementById('invitee-dropdown').classList.add('hidden');
                        };
                        el.appendChild(row);
                    });
                }
                el.classList.remove('hidden');
            }

            function loadDropdownList(searchTerm) {
                if (!courseId) return;
                var dropdown = document.getElementById('invitee-dropdown');
                dropdown.classList.remove('hidden');
                dropdown.innerHTML = '<div class="p-3 text-sm text-gray-500">Loading…</div>';
                var url = baseUrl + '/' + courseId + '/enrolled-users';
                if (searchTerm && searchTerm.length >= 2) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(showDropdown)
                    .catch(function () { showDropdown([]); });
            }

            document.getElementById('course_id').addEventListener('change', function () {
                courseId = this.value;
                selected = [];
                window._invitedNames = window._invitedNames || {};
                document.getElementById('invitee-search').disabled = !courseId;
                document.getElementById('invitee-search').value = '';
                document.getElementById('invitee-dropdown').classList.add('hidden');
                document.getElementById('invitee-dropdown').innerHTML = '';
                var showBtn = document.getElementById('invitee-show-list');
                var hint = document.getElementById('invitee-hint');
                if (courseId) {
                    showBtn.classList.remove('hidden');
                    hint.textContent = 'Click the field above or "Show dropdown list" to see enrolled students. Type to search.';
                } else {
                    showBtn.classList.add('hidden');
                    hint.textContent = 'Select a course first.';
                }
                renderTags();
            });

            document.getElementById('invitee-search').addEventListener('input', function () {
                var q = this.value.trim();
                clearTimeout(searchTimeout);
                var dropdown = document.getElementById('invitee-dropdown');
                if (!courseId) return;
                if (q.length >= 2) {
                    searchTimeout = setTimeout(function () { loadDropdownList(q); }, 250);
                } else if (q.length === 0) {
                    dropdown.classList.add('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            });

            document.getElementById('invitee-search').addEventListener('focus', function () {
                if (!courseId) return;
                if (this.value.trim().length >= 2) {
                    loadDropdownList(this.value.trim());
                } else {
                    loadDropdownList('');
                }
            });

            document.getElementById('invitee-show-list').addEventListener('click', function (e) {
                e.preventDefault();
                if (!courseId) return;
                document.getElementById('invitee-search').focus();
                loadDropdownList('');
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('#invitee-dropdown') || e.target.id === 'invitee-search' || e.target.closest('#invitee-show-list')) return;
                document.getElementById('invitee-dropdown').classList.add('hidden');
            });

            var initialCourse = document.getElementById('course_id').value;
            if (initialCourse) {
                courseId = initialCourse;
                document.getElementById('invitee-search').disabled = false;
                document.getElementById('invitee-show-list').classList.remove('hidden');
                document.getElementById('invitee-hint').textContent = 'Click the field above or "Show dropdown list" to see enrolled students. Type to search.';
            }
        })();
    </script>
@endsection
