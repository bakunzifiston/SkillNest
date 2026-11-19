{{-- Decorative illustration for the sign-in / register side panel. Brand colours: navy, blue, orange. --}}
<svg viewBox="0 0 480 360" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ __('Illustration: a learner follows a course on a phone, supported by a community of health champions') }}" style="width: 100%; height: auto; max-height: min(300px, 30vh); display: block; margin: 0 auto;">
    {{-- soft glow behind the phone --}}
    <circle cx="240" cy="160" r="150" fill="#19499B" opacity="0.28"/>
    <circle cx="240" cy="160" r="104" fill="#19499B" opacity="0.30"/>

    {{-- dashed links from the floating badges to the phone --}}
    <g fill="none" stroke="#C9D6EE" stroke-opacity="0.45" stroke-width="2" stroke-dasharray="5 6" stroke-linecap="round">
        <path d="M124 96 C146 96 152 112 172 118"/>
        <path d="M352 92 C332 92 326 108 308 114"/>
        <path d="M120 222 C140 218 150 206 172 200"/>
        <path d="M356 236 C336 236 326 224 308 216"/>
    </g>

    {{-- phone --}}
    <rect x="166" y="26" width="148" height="262" rx="26" fill="#132848" stroke="#C9D6EE" stroke-opacity="0.55" stroke-width="2"/>
    <rect x="176" y="40" width="128" height="234" rx="17" fill="#FFFFFF"/>
    <rect x="224" y="31" width="32" height="5" rx="2.5" fill="#C9D6EE" opacity="0.5"/>
    {{-- app header --}}
    <rect x="188" y="54" width="58" height="8" rx="4" fill="#19499B"/>
    <circle cx="288" cy="58" r="7" fill="#F16029"/>
    {{-- video card --}}
    <rect x="188" y="74" width="104" height="66" rx="9" fill="#19499B"/>
    <circle cx="240" cy="107" r="15" fill="#FFFFFF"/>
    <path d="M235 99.5 L235 114.5 L248 107 Z" fill="#F16029"/>
    <rect x="196" y="128" width="46" height="4" rx="2" fill="#FFFFFF" opacity="0.45"/>
    {{-- lesson title --}}
    <rect x="188" y="152" width="84" height="7" rx="3.5" fill="#0B1B33"/>
    <rect x="188" y="165" width="58" height="5" rx="2.5" fill="#9FB0CF"/>
    {{-- progress --}}
    <rect x="188" y="182" width="104" height="8" rx="4" fill="#DDE5F3"/>
    <rect x="188" y="182" width="72" height="8" rx="4" fill="#F16029"/>
    {{-- checklist --}}
    <g>
        <circle cx="196" cy="208" r="7" fill="#1D9E75"/>
        <path d="M192.5 208 l2.5 2.6 l4.8 -5" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="210" y="205" width="78" height="6" rx="3" fill="#C9D6EE"/>

        <circle cx="196" cy="230" r="7" fill="#1D9E75"/>
        <path d="M192.5 230 l2.5 2.6 l4.8 -5" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="210" y="227" width="64" height="6" rx="3" fill="#C9D6EE"/>

        <circle cx="196" cy="252" r="7" fill="none" stroke="#9FB0CF" stroke-width="2"/>
        <rect x="210" y="249" width="72" height="6" rx="3" fill="#E8EEF7"/>
    </g>

    {{-- health cross badge (top left) --}}
    <circle cx="96" cy="92" r="36" fill="#FFFFFF"/>
    <circle cx="96" cy="92" r="36" fill="none" stroke="#C9D6EE" stroke-width="3"/>
    <rect x="89" y="74" width="14" height="36" rx="3.5" fill="#F16029"/>
    <rect x="78" y="85" width="36" height="14" rx="3.5" fill="#F16029"/>

    {{-- graduation cap (top right) --}}
    <path d="M370 100 v20 c0 9 44 9 44 0 v-20 l-22 11 z" fill="#B8461A"/>
    <polygon points="392,68 438,88 392,108 346,88" fill="#F16029"/>
    <path d="M438 88 v26" stroke="#FBD4C4" stroke-width="3" stroke-linecap="round"/>
    <circle cx="438" cy="118" r="4.5" fill="#FBD4C4"/>

    {{-- progress ring (left) --}}
    <circle cx="92" cy="226" r="34" fill="#132848"/>
    <circle cx="92" cy="226" r="26" fill="none" stroke="#2A4A7C" stroke-width="9"/>
    <circle cx="92" cy="226" r="26" fill="none" stroke="#F16029" stroke-width="9" stroke-linecap="round" stroke-dasharray="122 164" transform="rotate(-90 92 226)"/>
    <path d="M83 226 l6.5 7 l12 -13" fill="none" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>

    {{-- chart card (right) --}}
    <rect x="352" y="196" width="100" height="86" rx="14" fill="#FFFFFF"/>
    <rect x="366" y="210" width="34" height="6" rx="3" fill="#0B1B33"/>
    <rect x="368" y="258" width="12" height="14" rx="3" fill="#C9D6EE"/>
    <rect x="386" y="248" width="12" height="24" rx="3" fill="#C9D6EE"/>
    <rect x="404" y="238" width="12" height="34" rx="3" fill="#19499B"/>
    <rect x="422" y="224" width="12" height="48" rx="3" fill="#F16029"/>

    {{-- community of champions (bottom) --}}
    <g fill="none" stroke="#C9D6EE" stroke-opacity="0.35" stroke-width="2" stroke-dasharray="4 6" stroke-linecap="round">
        <path d="M158 324 H322"/>
    </g>
    <g>
        <circle cx="150" cy="312" r="11" fill="#C9D6EE"/>
        <path d="M130 348 c0 -13 8 -22 20 -22 s20 9 20 22 z" fill="#C9D6EE"/>

        <circle cx="240" cy="308" r="13" fill="#F16029"/>
        <path d="M216 352 c0 -15 10 -26 24 -26 s24 11 24 26 z" fill="#F16029"/>
        <rect x="236" y="333" width="8" height="2.6" rx="1.3" fill="#FFFFFF" opacity="0.9"/>
        <rect x="238.7" y="330.3" width="2.6" height="8" rx="1.3" fill="#FFFFFF" opacity="0.9"/>

        <circle cx="330" cy="312" r="11" fill="#9FB0CF"/>
        <path d="M310 348 c0 -13 8 -22 20 -22 s20 9 20 22 z" fill="#9FB0CF"/>
    </g>

    {{-- sparkles --}}
    <g fill="#F16029">
        <path d="M56 160 l2.4 6.6 l6.6 2.4 l-6.6 2.4 l-2.4 6.6 l-2.4 -6.6 l-6.6 -2.4 l6.6 -2.4 z" opacity="0.9"/>
        <path d="M418 160 l2 5.4 l5.4 2 l-5.4 2 l-2 5.4 l-2 -5.4 l-5.4 -2 l5.4 -2 z" opacity="0.8"/>
    </g>
    <g fill="#FFFFFF">
        <circle cx="130" cy="150" r="3" opacity="0.5"/>
        <circle cx="352" cy="162" r="3" opacity="0.5"/>
        <circle cx="206" cy="14" r="2.5" opacity="0.45"/>
        <circle cx="414" cy="298" r="2.5" opacity="0.4"/>
        <circle cx="60" cy="300" r="2.5" opacity="0.4"/>
    </g>
</svg>
