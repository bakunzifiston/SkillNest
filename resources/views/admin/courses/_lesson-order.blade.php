@php $sequential = old('sequential_lessons', $course?->sequential_lessons ?? true); @endphp
<div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
    <input type="hidden" name="sequential_lessons" value="0">
    <label class="flex items-start gap-3 cursor-pointer">
        <input type="checkbox" name="sequential_lessons" value="1" @checked($sequential) class="mt-1 h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
        <span>
            <span class="block text-sm font-semibold text-navy">{{ __('Students must complete lessons in order') }}</span>
            <span class="block text-xs text-slate-500 mt-0.5">{{ __('Each lesson unlocks when the previous one is completed. Turn off to let students open lessons in any order.') }}</span>
        </span>
    </label>
</div>
