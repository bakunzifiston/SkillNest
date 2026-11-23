<?php

return [

    /*
    | Languages the platform is available in. English is the default and the fallback:
    | any text that has no translation is shown in English. To add another language later,
    | add it here, add lang/<code>.json (+ lang/<code>/*.php), and it appears in the switcher.
    */
    'default' => 'en',

    /*
    | The language a brand-new visitor sees on their very first visit (before they or their account
    | have chosen one). Change it with APP_INITIAL_LOCALE in .env. Keep 'default' as it is: it is the
    | language the original course content is written in, and the fallback when a translation is missing.
    */
    'initial' => env('APP_INITIAL_LOCALE', 'en'),

    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English', 'short' => 'EN'],
        'rw' => ['name' => 'Kinyarwanda', 'native' => 'Ikinyarwanda', 'short' => 'RW'],
    ],

    // Only a real choice is remembered (never the automatic initial language): in the session, in this cookie
    // (minutes) and, for signed-in users, on their account. New names, so values stored by older versions are ignored.
    'session_key' => 'locale_choice',
    'cookie' => 'koralink_lang',
    'cookie_minutes' => 60 * 24 * 365,
];
