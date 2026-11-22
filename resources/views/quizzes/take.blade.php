@extends('layouts.site')

@section('title', __('Quiz: :title', ['title' => $quizAttempt->quiz->tr('title')]))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <p class="text-sm text-slate-500">{{ __('Quiz: :title', ['title' => $quizAttempt->quiz->tr('title')]) }} ({{ $quizAttempt->quiz->course->tr('title') }})</p>
        <h1 class="font-display font-bold text-2xl text-slate-900 mt-2">{{ __('Answer the questions') }}</h1>
        @if($remainingSeconds !== null)
            <div id="quiz-timer" data-remaining="{{ $remainingSeconds }}" class="sticky top-2 z-10 mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 shadow-sm" role="timer" aria-live="off">
                <span class="text-sm text-slate-500">{{ __('Time left') }}</span>
                <span id="quiz-timer-value" class="font-display font-bold tabular-nums text-slate-900">--:--</span>
            </div>
        @endif
        <p id="quiz-saved-note" class="mt-3 text-xs text-slate-500" style="transition: opacity .4s ease;">{{ __('Your answers are saved automatically.') }}</p>
        <form id="quiz-form" action="{{ route('quizzes.submit', $quizAttempt) }}" method="post" class="mt-8 space-y-8">
            @csrf
            @foreach($quizAttempt->quiz->questions as $index => $q)
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <p class="font-semibold text-slate-900">{{ $index + 1 }}. {{ $q->tr('question_text') }}</p>
                    <div class="mt-4 space-y-2">
                        @foreach($q->options as $opt)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="question_{{ $q->id }}" value="{{ $opt->id }}" class="text-primary focus:ring-accent" required @checked((int) ($draft[$q->id] ?? 0) === (int) $opt->id)>
                                <span class="text-slate-700">{{ $opt->tr('option_text') }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="flex gap-4">
                <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                    {{ __('Submit quiz') }}
                </button>
                <a href="{{ route('courses.show', $quizAttempt->quiz->course) }}" class="inline-flex items-center px-4 py-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            // Autosave: the answers are kept on the server as the student goes, so nothing is lost if the tab closes,
            // and so the server can submit them automatically if the time limit runs out.
            var form = document.getElementById('quiz-form');
            var note = document.getElementById('quiz-saved-note');
            var url = @json(route('quizzes.autosave', $quizAttempt));
            var tokenEl = document.querySelector('meta[name="csrf-token"]');
            var last = JSON.stringify(@json((object) ($draft ?: [])));
            var timer = null, busy = false;

            function answers() {
                var out = {};
                new FormData(form).forEach(function (value, key) {
                    var m = key.match(/^question_(\d+)$/);
                    if (m) out[m[1]] = value;
                });
                return out;
            }
            function save() {
                var current = answers(), text = JSON.stringify(current);
                if (text === last || busy) return;
                busy = true;
                fetch(url, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': tokenEl ? tokenEl.content : '' },
                    body: JSON.stringify({ answers: current })
                }).then(function (response) {
                    if (response.status === 409) {                       // the time ran out: the saved answers were submitted
                        return response.json().then(function (data) { window.location = data.redirect; });
                    }
                    if (response.ok) { last = text; note.style.opacity = '1'; }
                }).catch(function () { /* offline: the next attempt will retry */ }).then(function () { busy = false; });
            }

            form.addEventListener('change', function () { clearTimeout(timer); timer = setTimeout(save, 500); });
            setInterval(save, 15000);
            document.addEventListener('visibilitychange', function () { if (document.hidden) save(); });
        })();
    </script>

    @if($remainingSeconds !== null)
        <script>
            (function () {
                var box = document.getElementById('quiz-timer');
                var out = document.getElementById('quiz-timer-value');
                var form = document.getElementById('quiz-form');
                var left = parseInt(box.dataset.remaining, 10);
                var end = performance.now() + left * 1000;          // the server's clock decides how long is left
                var sent = false;

                function pad(n) { return (n < 10 ? '0' : '') + n; }
                function tick() {
                    left = Math.max(0, Math.ceil((end - performance.now()) / 1000));
                    out.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
                    if (left <= 60) { box.style.borderColor = '#dc2626'; out.style.color = '#dc2626'; }
                    if (left === 0 && !sent) { sent = true; form.submit(); }   // time is up: send what has been answered
                }
                tick();
                setInterval(tick, 500);
            })();
        </script>
    @endif
@endsection
