<?php

return [

    /*
    | Languages the platform is available in. English is the default and the fallback:
    | any text that has no translation is shown in English. To add another language later,
    | add it here, add lang/<code>.json (+ lang/<code>/*.php), and it appears in the switcher.
    */
    'default' => 'en',

    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English', 'short' => 'EN'],
        'rw' => ['name' => 'Kinyarwanda', 'native' => 'Ikinyarwanda', 'short' => 'RW'],
    ],

    // Guests keep their choice in this cookie (minutes). Signed-in users also get it saved on their account.
    'cookie' => 'locale',
    'cookie_minutes' => 60 * 24 * 365,
];
