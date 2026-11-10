{{-- Section / lesson placement. Expects $courseTree (courses with chapters.lessons), $selectedCourseId, $selectedChapterId, $selectedLessonId, $courseLocked (bool). --}}
@php
    $tree = $courseTree->map(fn ($c) => [
        'id' => $c->id,
        'chapters' => $c->chapters->map(fn ($ch) => [
            'id' => $ch->id,
            'title' => $ch->title,
            'lessons' => $ch->lessons->map(fn ($l) => ['id' => $l->id, 'title' => $l->title])->values(),
        ])->values(),
    ])->values();
@endphp
<div id="quiz-placement" data-tree='@json($tree)' data-course-locked="{{ $courseLocked ? '1' : '0' }}">
    <div>
        <label for="chapter_id" class="block text-sm font-medium text-gray-700">Section (optional)</label>
        <select name="chapter_id" id="chapter_id" data-selected="{{ old('chapter_id', $selectedChapterId) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent"></select>
        @error('chapter_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="mt-5">
        <label for="lesson_id" class="block text-sm font-medium text-gray-700">Show after lesson (optional)</label>
        <select name="lesson_id" id="lesson_id" data-selected="{{ old('lesson_id', $selectedLessonId) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent"></select>
        <p class="mt-1 text-xs text-gray-500">Leave on "End of section" to list the quiz after all lessons in the section.</p>
        @error('lesson_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<script>
    (function () {
        var box = document.getElementById('quiz-placement');
        var tree = JSON.parse(box.dataset.tree);
        var chapterSel = document.getElementById('chapter_id');
        var lessonSel = document.getElementById('lesson_id');
        var courseSel = document.getElementById('course_id');
        var courseId = function () { return courseSel ? courseSel.value : '{{ $selectedCourseId }}'; };

        function fill(sel, placeholder, items, selected) {
            sel.innerHTML = '';
            var o = document.createElement('option'); o.value = ''; o.textContent = placeholder; sel.appendChild(o);
            items.forEach(function (i) {
                var opt = document.createElement('option');
                opt.value = i.id; opt.textContent = i.title;
                if (String(i.id) === String(selected)) opt.selected = true;
                sel.appendChild(opt);
            });
        }
        function chapters() {
            var c = tree.find(function (c) { return String(c.id) === String(courseId()); });
            return c ? c.chapters : [];
        }
        function renderChapters(selected) {
            fill(chapterSel, 'No section (general course quizzes)', chapters(), selected);
            renderLessons("");
        }
        function renderLessons(selected) {
            var ch = chapters().find(function (c) { return String(c.id) === String(chapterSel.value); });
            fill(lessonSel, 'End of section', ch ? ch.lessons : [], selected);
            lessonSel.disabled = !ch;
        }
        renderChapters(chapterSel.dataset.selected);
        renderLessons(lessonSel.dataset.selected);
        if (courseSel) courseSel.addEventListener('change', function () { renderChapters(''); });
        chapterSel.addEventListener('change', function () { renderLessons(''); });
    })();
</script>
