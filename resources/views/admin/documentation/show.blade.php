@extends('layouts.admin')

@section('title', $info['title'])
@section('header', $info['title'])

@section('content')
    <style>
        @include('admin.documentation._prose')
        @include('admin.documentation._ui')
        /* The page scrolls inside <main>'s parent, so main must not be a scroll container or "sticky" never sticks. */
        main{overflow:visible!important}
        .doc-layout{display:flex;gap:24px;align-items:flex-start}
        .doc-toc{flex:0 0 272px;position:sticky;top:16px;align-self:flex-start;max-height:calc(100vh - 32px);overflow-y:auto;background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:14px 10px 12px;scrollbar-width:thin}
        .doc-toc-title{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#64748B;margin:2px 10px 10px}
        .doc-toc details{margin:0}
        .doc-toc summary{list-style:none;cursor:pointer;display:flex;gap:10px;align-items:flex-start;padding:7px 10px;border-radius:10px}
        .doc-toc summary::-webkit-details-marker{display:none}
        .doc-toc summary:hover{background:#F1F5F9}
        .doc-toc .doc-num{flex:none;width:22px;height:22px;border-radius:7px;background:#EEF2F7;color:#475569;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:1px}
        .doc-toc .doc-ch{font-weight:600;color:#1E293B;font-size:13.5px;line-height:1.35;text-decoration:none}
        .doc-toc details[open]>summary .doc-num{background:#19499B;color:#fff}
        .doc-toc details[open]>summary .doc-ch{color:#19499B}
        .doc-toc .doc-subs{margin:2px 0 8px 21px;padding-left:10px;border-left:2px solid #E2E8F0}
        .doc-toc .doc-subs a{display:block;padding:4px 8px;margin-left:-2px;border-left:2px solid transparent;font-size:12.5px;line-height:1.4;color:#64748B;text-decoration:none;border-radius:0 6px 6px 0}
        .doc-toc .doc-subs a:hover{color:#19499B;background:#F8FAFC}
        .doc-toc .doc-subs a.active{color:#19499B;font-weight:600;border-left-color:#F16029;background:#FEF6F2}
        .doc-main{flex:1 1 0;min-width:0}
        @media (max-width:1024px){.doc-layout{flex-direction:column}.doc-toc{position:static;flex:none;width:100%;max-height:320px}}
        .doc-chapter{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:28px 32px;margin-bottom:20px}
        @media (max-width:640px){.doc-chapter{padding:20px 16px}}
    </style>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <a href="{{ route('admin.documentation.index') }}" class="text-sm font-semibold text-primary hover:underline">&larr; {{ __('All documentation') }}</a>
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
            <a href="{{ route('admin.documentation.download', [$book, 'pdf']) }}" target="_blank" rel="noopener" class="doc-btn doc-btn-sm doc-btn-accent">{{ __('Download PDF') }}</a>
            <a href="{{ route('admin.documentation.download', [$book, 'html']) }}" class="doc-btn doc-btn-sm">HTML</a>
            <a href="{{ route('admin.documentation.download', [$book, 'md']) }}" class="doc-btn doc-btn-sm">Markdown</a>
        </div>
    </div>

    @if($chapters->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-slate-500 text-sm">{{ __('No chapters have been added yet.') }}</div>
    @else
        <div class="doc-layout">
            <nav class="doc-toc" aria-label="{{ __('Table of contents') }}">
                <p class="doc-toc-title">{{ __('Contents') }}</p>
                @foreach($chapters as $chapter)
                    <details class="doc-toc-ch" data-ch="{{ $chapter['slug'] }}" @if($loop->first) open @endif>
                        <summary><span class="doc-num">{{ $chapter['number'] }}</span><a class="doc-ch" href="#{{ $chapter['slug'] }}">{{ $chapter['title'] }}</a></summary>
                        <div class="doc-subs">
                            @foreach($chapter['headings'] as $h)
                                @if($h['level'] === 2)
                                    <a href="#{{ $h['id'] }}" data-sec="{{ $h['id'] }}">{{ $h['text'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>

            <div class="doc-main">
                <p class="text-xs text-slate-500 mb-3">{{ $updatedAt ? __('Updated :date', ['date' => $updatedAt->translatedFormat('M j, Y')]) : '' }}</p>
                @foreach($chapters as $chapter)
                    <article id="{{ $chapter['slug'] }}" class="doc-chapter doc-body" style="scroll-margin-top:16px;">
                        <p class="doc-eyebrow">{{ __('Chapter :number', ['number' => $chapter['number']]) }}</p>
                        {!! preg_replace('/src="images\//', 'src="'.e(str_replace('__f__', '', route('admin.documentation.image', [$book, '__f__'], false))).'', $chapter['html']) !!}
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <script>
        // Keep the contents in step with the reading position: open the current chapter and mark the current section.
        (function () {
            var nav = document.querySelector('.doc-toc');
            var chapters = document.querySelectorAll('.doc-toc-ch');
            if (!nav || !chapters.length) { return; }
            var articles = Array.prototype.slice.call(document.querySelectorAll('article.doc-chapter'));
            var heads = Array.prototype.slice.call(document.querySelectorAll('.doc-body h2')).map(function (h) {
                var a = h.querySelector('a.doc-anchor[id]');
                return a ? { el: h, id: a.id } : null;
            }).filter(Boolean);
            var links = {};
            document.querySelectorAll('.doc-toc [data-sec]').forEach(function (a) { links[a.getAttribute('data-sec')] = a; });
            var line = 140, ticking = false, lastCh = null, lastSec = null;

            function update() {
                ticking = false;
                var ch = articles[0];
                articles.forEach(function (a) { if (a.getBoundingClientRect().top <= line) { ch = a; } });
                var sec = null;
                heads.forEach(function (h) { if (h.el.getBoundingClientRect().top <= line) { sec = h.id; } });

                if (ch && ch.id !== lastCh) {
                    lastCh = ch.id;
                    chapters.forEach(function (d) { d.open = d.getAttribute('data-ch') === ch.id; });
                }
                if (sec !== lastSec) {
                    lastSec = sec;
                    Object.keys(links).forEach(function (id) { links[id].classList.toggle('active', id === sec); });
                    var cur = sec && links[sec];
                    if (cur) {
                        var r = cur.getBoundingClientRect(), n = nav.getBoundingClientRect();
                        if (r.top < n.top + 60 || r.bottom > n.bottom - 30) { nav.scrollTop += r.top - n.top - n.height / 2; }
                    }
                }
            }
            function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll);
            update();
        })();
    </script>
@endsection
