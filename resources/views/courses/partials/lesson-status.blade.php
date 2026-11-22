@php
    // $status: completed | in_progress | locked | open (open shows nothing). $dark: for the navy sidebar.
    $status = $status ?? 'open';
    $dark = $dark ?? false;
    $palette = [
        'completed' => $dark ? ['#0F6B4E', '#D9F5EA'] : ['#E6F7F1', '#136B4F'],
        'in_progress' => $dark ? ['#F16029', '#FFFFFF'] : ['#FEF0EA', '#B8461A'],
        'locked' => $dark ? ['rgba(255,255,255,.14)', 'rgba(255,255,255,.75)'] : ['#F1F5F9', '#64748B'],
    ];
    $labels = ['completed' => __('Completed'), 'in_progress' => __('In progress'), 'locked' => __('Locked')];
@endphp
@if(isset($palette[$status]))
    <span data-lesson-status-badge="{{ $status }}" style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap;border-radius:999px;padding:2px 9px;font-size:11px;font-weight:600;line-height:1.5;background:{{ $palette[$status][0] }};color:{{ $palette[$status][1] }};">
        @if($status === 'completed')
            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        @elseif($status === 'locked')
            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 11V8a5 5 0 0110 0v3M6 11h12v9H6z"/></svg>
        @else
            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
        @endif
        {{ $labels[$status] }}
    </span>
@endif
