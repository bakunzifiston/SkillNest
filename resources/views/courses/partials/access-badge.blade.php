{{-- Small "who is this for" tag on course cards. Shown only for restricted courses. --}}
@if(($course->access_level ?? 'all') !== 'all')
    @php $locked = ! $course->isAvailableTo(auth()->user()); @endphp
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $locked ? 'bg-accent-light text-accent-darker' : 'bg-slate-100 text-slate-600' }}" title="{{ $locked ? __('Not available for your account') : __('Available for your account') }}">
        @if($locked)
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"/></svg>
        @endif
        {{ __(':audience only', ['audience' => $course->audienceName()]) }}
    </span>
@endif
