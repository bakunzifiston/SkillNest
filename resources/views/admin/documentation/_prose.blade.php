{{-- Typography for rendered documentation. Plain CSS so it does not depend on the compiled Tailwind build. --}}
.doc-body{color:#1e293b;font-size:15px;line-height:1.7;overflow-wrap:break-word}
.doc-body h1{font-size:28px;line-height:1.25;color:#0B1B33;font-weight:700;margin:0 0 16px;padding-bottom:12px;border-bottom:3px solid #F16029}
.doc-body h2{font-size:21px;color:#19499B;font-weight:700;margin:34px 0 10px;scroll-margin-top:16px}
.doc-body h3{font-size:17px;color:#0B1B33;font-weight:700;margin:24px 0 8px;scroll-margin-top:16px}
.doc-body p{margin:0 0 12px}
.doc-body ul{list-style:disc;margin:0 0 14px;padding-left:24px}
.doc-body ol{list-style:decimal;margin:0 0 14px;padding-left:24px}
.doc-body li{margin:4px 0}
.doc-body li>ul,.doc-body li>ol{margin:4px 0}
.doc-body a{color:#19499B;text-decoration:underline}
.doc-body a.doc-anchor{color:#cbd5e1;text-decoration:none;margin-left:8px;font-weight:400;opacity:0}
.doc-body h2:hover a.doc-anchor,.doc-body h3:hover a.doc-anchor{opacity:1}
.doc-body code{background:#F1F5F9;color:#0B1B33;padding:1px 6px;border-radius:5px;font-size:.9em;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.doc-body pre{background:#0B1B33;color:#E2E8F0;padding:14px 16px;border-radius:10px;overflow-x:auto;margin:0 0 16px;font-size:13px;line-height:1.55}
.doc-body pre code{background:none;color:inherit;padding:0;font-size:inherit}
.doc-body blockquote{margin:0 0 16px;padding:10px 16px;background:#EAF0FA;border-left:4px solid #19499B;border-radius:0 8px 8px 0;color:#16335F}
.doc-body blockquote.doc-tip{background:#E6F7F1;border-color:#1E9E75;color:#0F5A43}
.doc-body blockquote.doc-warning,.doc-body blockquote.doc-important{background:#FEF0EA;border-color:#F16029;color:#7A2E10}
.doc-eyebrow{font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#F16029;margin:0 0 6px}
.doc-body figure.doc-figure{margin:6px 0 20px}
.doc-body figure.doc-figure img{display:block;margin:0;width:100%}
.doc-body figcaption{margin-top:8px;font-size:12.5px;color:#64748B;text-align:center}
.doc-body tbody tr:nth-child(even) td{background:#F8FAFC}
.doc-body th{font-size:12.5px;text-transform:uppercase;letter-spacing:.04em}
.doc-body blockquote p:last-child{margin-bottom:0}
.doc-body table{border-collapse:separate;border-spacing:0;width:100%;margin:0 0 18px;font-size:14px;display:table;border:1px solid #D9E2EF;border-radius:10px;overflow:hidden}
.doc-body th{background:#EAF0FA;color:#0B1B33;text-align:left;font-weight:600}
.doc-body th,.doc-body td{border-bottom:1px solid #E6EDF6;padding:9px 12px;vertical-align:top}
.doc-body tr:last-child td{border-bottom:0}
.doc-body img{max-width:100%;height:auto;border:1px solid #E2E8F0;border-radius:10px;margin:8px 0 16px;box-shadow:0 2px 10px rgba(11,27,51,.08)}
.doc-body h1{margin-top:0}
.doc-body hr{border:0;border-top:1px solid #E2E8F0;margin:24px 0}
