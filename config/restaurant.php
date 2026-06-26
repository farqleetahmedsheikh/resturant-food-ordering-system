<?php

return [
    'currency' => [
        'code' => env('RESTAURANT_CURRENCY', 'AUD'),
        'locale' => env('RESTAURANT_CURRENCY_LOCALE', 'en_AU'),
    ],

    'timezones' => [
        'Australia/Sydney' => 'Australia/Sydney',
        'Australia/Melbourne' => 'Australia/Melbourne',
        'Australia/Brisbane' => 'Australia/Brisbane',
        'Australia/Adelaide' => 'Australia/Adelaide',
        'Australia/Perth' => 'Australia/Perth',
        'Australia/Hobart' => 'Australia/Hobart',
        'Australia/Darwin' => 'Australia/Darwin',
        'Australia/Canberra' => 'Australia/Canberra',
        'Australia/Broken_Hill' => 'Australia/Broken_Hill',
        'Australia/Lord_Howe' => 'Australia/Lord_Howe',
    ],
];
