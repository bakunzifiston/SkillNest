@extends('layouts.admin')

@section('title', 'Edit Live Session')
@section('header', 'Edit Live Session: ' . $liveSession->title)

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.live-sessions.update', $liveSession) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <p class="text-sm text-gray-500">Course: {{ $liveSession->course->title }}</p>
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $liveSession->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description', $liveSession->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Date & time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $liveSession->scheduled_at->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('scheduled_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $liveSession->duration_minutes) }}" min="5" max="480" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="meeting_url" class="block text-sm font-medium text-gray-700">Meeting URL</label>
                <input type="url" name="meeting_url" id="meeting_url" value="{{ old('meeting_url', $liveSession->meeting_url) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('meeting_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="meeting_password" class="block text-sm font-medium text-gray-700">Meeting password (optional)</label>
                <input type="text" name="meeting_password" id="meeting_password" value="{{ old('meeting_password', $liveSession->meeting_password) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('meeting_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Invite students (invited students receive an email; only they can see the join link)</p>
                <p class="text-sm text-gray-500 mb-2">Open the dropdown to browse the list or type to search. Remove with ×.</p>
                <div class="flex flex-wrap gap-2 mb-2" id="invited-tags"></div>
                <div class="relative">
                    <input type="text" id="invitee-search" placeholder="Search or click to see list…" autocomplete="off" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <div id="invitee-dropdown" class="absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto hidden"></div>
                </div>
                <p class="mt-1.5 text-sm text-gray-500"><button type="button" id="invitee-show-list" class="text-amber-600 hover:text-amber-700 font-medium">Show dropdown list</button> — or type to search.</p>
                @php
    $invitedIdsValue = old('invited_user_ids', $liveSession->invitedAttendees->pluck('id')->toArray());
    if (is_array($invitedIdsValue)) {
        $invitedIdsValue = implode(',', $invitedIdsValue);
    }
@endphp
                <input type="hidden" name="invited_user_ids" id="invited_user_ids_input" value="{{ $invitedIdsValue }}">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Save</button>
                <a href="{{ route('admin.live-sessions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var courseId = {{ $liveSession->course_id }};
            var baseUrl = '{{ url("admin/courses") }}';
            var inputEl = document.getElementById('invited_user_ids_input');
            var selected = inputEl.value ? inputEl.value.split(',').filter(Boolean) : [];
            window._invitedNames = {!! json_encode($liveSession->invitedAttendees->mapWithKeys(fn ($u) => [$u->id => $u->name ?: $u->email])->toArray()) !!};

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

            document.getElementById('invitee-search').addEventListener('input', function () {
                var q = this.value.trim();
                clearTimeout(window._inviteSearchTimeout);
                var dropdown = document.getElementById('invitee-dropdown');
                if (q.length >= 2) {
                    window._inviteSearchTimeout = setTimeout(function () { loadDropdownList(q); }, 250);
                } else if (q.length === 0) {
                    dropdown.classList.add('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            });

            document.getElementById('invitee-search').addEventListener('focus', function () {
                if (this.value.trim().length >= 2) {
                    loadDropdownList(this.value.trim());
                } else {
                    loadDropdownList('');
                }
            });

            document.getElementById('invitee-show-list').addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('invitee-search').focus();
                loadDropdownList('');
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('#invitee-dropdown') || e.target.id === 'invitee-search' || e.target.closest('#invitee-show-list')) return;
                document.getElementById('invitee-dropdown').classList.add('hidden');
            });

            renderTags();
        })();
    </script>
@endsection
