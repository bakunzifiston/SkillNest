{{-- How often a student may take the quiz. Expects $quiz (existing quiz) or null on the create form. --}}
@php
    $maxAttempts = old('max_attempts', $quiz?->max_attempts);
    $allowRetake = (bool) old('allow_retake_after_pass', $quiz?->allow_retake_after_pass ?? true);
@endphp
<div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-4">
    <div>
        <label for="max_attempts" class="block text-sm font-medium text-gray-700">{{ __('Maximum attempts (optional)') }}</label>
        <input type="number" name="max_attempts" id="max_attempts" value="{{ $maxAttempts }}" min="1" max="100" placeholder="{{ __('Unlimited') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
        <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty to let students retake the quiz as often as they like. An unfinished attempt does not use up an attempt.') }}</p>
        @error('max_attempts')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <input type="hidden" name="allow_retake_after_pass" value="0">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="allow_retake_after_pass" value="1" @checked($allowRetake) class="rounded border-gray-300 text-primary focus:ring-accent">
            <span class="text-sm text-gray-700">{{ __('Students can retake the quiz after passing it') }}</span>
        </label>
        @error('allow_retake_after_pass')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
