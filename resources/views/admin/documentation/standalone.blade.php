<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ config('app.name') }} — {{ $info['title'] }}</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f8fafc;color:#1e293b}
.page{max-width:860px;margin:0 auto;padding:32px 24px 64px}
.cover{background:#0B1B33;color:#fff;border-radius:16px;padding:56px 40px;margin-bottom:32px;position:relative;overflow:hidden}
.cover:after{content:"";position:absolute;right:-60px;top:-60px;width:220px;height:220px;border-radius:50%;background:#F16029;opacity:.9}
.cover .kicker{font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#9FB6DD;margin:0 0 12px}
.cover h1{font-size:38px;line-height:1.15;margin:0 0 10px;position:relative}
.cover p{margin:0;color:#CBD5E1;position:relative}
.toc{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px 28px;margin-bottom:32px}
.toc h2{margin:0 0 10px;font-size:18px;color:#0B1B33}
.toc ol{margin:0;padding-left:22px;line-height:1.9;column-count:2;column-gap:36px}
.toc li{break-inside:avoid}
.toc a{color:#19499B;text-decoration:none}
.toc ul{margin:0 0 6px;padding-left:20px;list-style:circle;font-size:13.5px;line-height:1.6}
.chapter{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:28px 32px;margin-bottom:24px}
@include('admin.documentation._prose')
@media print{
 .noprint{display:none!important}
 body{background:#fff}
 .page{max-width:none;padding:0}
 .cover{border-radius:0;-webkit-print-color-adjust:exact;print-color-adjust:exact;page-break-after:always;margin-bottom:0}
 .toc{border:0;page-break-after:always}
 .chapter{border:0;border-radius:0;padding:0;page-break-before:always}
 .doc-body a.doc-anchor{display:none}
 .doc-body pre,.doc-body table,.doc-body img{page-break-inside:avoid}
 .doc-body pre{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
</head>
<body>
<div class="page">
 <div class="noprint" style="display:flex;gap:12px;align-items:center;justify-content:space-between;background:#EAF0FA;border:1px solid #D9E2EF;border-radius:12px;padding:10px 16px;margin-bottom:16px;font-size:14px">
  <span>To save as PDF: choose <strong>Print</strong>, then set the destination to <strong>Save as PDF</strong>.</span>
  <button type="button" onclick="window.print()" style="background:#F16029;color:#fff;border:0;border-radius:8px;padding:8px 16px;font-weight:600;cursor:pointer">Print / Save as PDF</button>
 </div>
 <header class="cover">
  <p class="kicker">{{ config('app.name') }}</p>
  <h1>{{ $info['title'] }}</h1>
  <p>{{ $info['description'] }}@if($updatedAt) · {{ $updatedAt->format('F j, Y') }}@endif</p>
 </header>
 <nav class="toc">
  <h2>Contents</h2>
  <ol>
   @foreach($chapters as $c)
    <li><a href="#{{ $c['slug'] }}">{{ $c['title'] }}</a>
     @php $subs = array_values(array_filter($c['headings'], fn ($h) => $h['level'] === 2)); @endphp
     @if($subs)<ul>@foreach($subs as $h)<li><a href="#{{ $h['id'] }}">{{ $h['text'] }}</a></li>@endforeach</ul>@endif
    </li>
   @endforeach
  </ol>
 </nav>
 @foreach($chapters as $c)
  <article id="{{ $c['slug'] }}" class="chapter doc-body"><p class="doc-eyebrow">Chapter {{ $c['number'] }}</p>{!! $c['html'] !!}</article>
 @endforeach
</div>
@if($autoPrint ?? false)<script>window.addEventListener('load',function(){setTimeout(function(){window.print()},400)})</script>@endif
</body>
</html>
