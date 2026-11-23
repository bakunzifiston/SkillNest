@extends('layouts.admin')

@section('title', __('Documentation'))
@section('header', __('Documentation'))

@section('content')
    <style>
        @include('admin.documentation._ui')
        .doc-card{background:#fff;border:1px solid #E2E8F0;border-radius:18px;padding:26px;display:flex;flex-direction:column}
        .doc-card-head{display:flex;align-items:center;gap:14px}
        .doc-card-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex:none}
        .doc-chapters{list-style:none;margin:0;padding:0;border:1px solid #EEF2F7;border-radius:12px;overflow:hidden}
        .doc-chapters a.doc-more{display:block;padding:9px 14px;font-size:13px;font-weight:600;color:#19499B;text-decoration:none;background:#F8FAFC;border-top:1px solid #EEF2F7}
        .doc-chapters a.doc-more:hover{background:#EEF3FB}
        .doc-chapters li+li{border-top:1px solid #EEF2F7}
        .doc-chapters a{display:flex;gap:12px;align-items:baseline;padding:9px 14px;font-size:14px;color:#334155;text-decoration:none}
        .doc-chapters a:hover{background:#F8FAFC;color:#19499B}
        .doc-chapters .n{flex:none;width:22px;text-align:right;font-size:12px;color:#94A3B8;font-variant-numeric:tabular-nums}
    </style>

    <p class="text-sm text-slate-600 mb-6" style="max-width:46rem">{{ __('Read the guides online, or download them to share or print. The documents are kept in the project\'s docs folder and are updated together with the system.') }}</p>

    <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));align-items:stretch">
        @foreach($books as $book)
            @php $isGuide = $book['key'] === 'user-guide'; @endphp
            <section class="doc-card">
                <div class="doc-card-head">
                    <span class="doc-card-icon" style="background:{{ $isGuide ? '#FEF0EA' : '#EAF0FA' }};color:{{ $isGuide ? '#F16029' : '#19499B' }}">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">@if($isGuide)<path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 016.5 3H20v15H6.5A2.5 2.5 0 004 20.5v-15zM4 20.5A2.5 2.5 0 006.5 18H20v3H6.5A2.5 2.5 0 014 20.5z"/>@else<path stroke-linecap="round" stroke-linejoin="round" d="M8 9l-3 3 3 3m8-6l3 3-3 3M13.5 6l-3 12"/>@endif</svg>
                    </span>
                    <div style="min-width:0">
                        <h2 class="font-display font-semibold text-navy" style="font-size:19px;line-height:1.25">{{ $book['title'] }}</h2>
                        <p class="text-xs text-slate-500" style="margin-top:2px">
                            {{ trans_choice('{1} :count chapter|[2,*] :count chapters', $book['chapters']->count()) }}
                            @if($book['updatedAt']) · {{ __('Updated :date', ['date' => $book['updatedAt']->translatedFormat('M j, Y')]) }} @endif
                        </p>
                    </div>
                </div>
                <p class="text-sm text-slate-600" style="margin-top:14px;min-height:3.2em">{{ $book['description'] }}</p>

                @if($book['chapters']->isEmpty())
                    <p class="text-sm text-slate-500" style="margin-top:16px">{{ __('No chapters have been added yet.') }}</p>
                @else
                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:20px">
                        <a href="{{ route('admin.documentation.show', $book['key']) }}" class="doc-btn doc-btn-primary">{{ __('Read online') }}</a>
                        <a href="{{ route('admin.documentation.download', [$book['key'], 'pdf']) }}" target="_blank" rel="noopener" class="doc-btn doc-btn-accent">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 20h14"/></svg>{{ __('Download PDF') }}
                        </a>
                    </div>
                    <p class="text-xs text-slate-500" style="margin-top:12px">
                        {{ __('Other formats') }}:
                        <a href="{{ route('admin.documentation.download', [$book['key'], 'html']) }}" class="text-primary hover:underline">HTML</a>
                        <span aria-hidden="true">·</span>
                        <a href="{{ route('admin.documentation.download', [$book['key'], 'md']) }}" class="text-primary hover:underline">Markdown</a>
                    </p>

                    <p class="text-xs font-semibold uppercase text-slate-500" style="margin-top:20px;letter-spacing:.1em">{{ __('Contents') }}</p>
                    <ol class="doc-chapters" style="margin-top:8px">
                        @foreach($book['chapters']->take(6) as $chapter)
                            <li><a href="{{ route('admin.documentation.show', $book['key']) }}#{{ $chapter['slug'] }}"><span class="n">{{ $chapter['number'] }}</span><span>{{ $chapter['title'] }}</span></a></li>
                        @endforeach
                        @if($book['chapters']->count() > 6)
                            <li><a class="doc-more" href="{{ route('admin.documentation.show', $book['key']) }}">{{ __('Show all :count chapters', ['count' => $book['chapters']->count()]) }} &rarr;</a></li>
                        @endif
                    </ol>
                @endif
            </section>
        @endforeach
    </div>

    <p class="text-xs text-slate-500" style="margin-top:20px;max-width:46rem">{{ __('Download PDF opens a print-ready page: in the print window choose Save as PDF as the destination.') }}</p>
@endsection
