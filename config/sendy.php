<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sendy API Key
    |--------------------------------------------------------------------------
    |
    | Your Sendy installation API key, used to authenticate API requests.
    |
    */

    'api_key' => env('SENDY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sendy Installation URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Sendy installation (e.g. https://sendy.yourdomain.com).
    |
    */

    'url' => env('SENDY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Mailing Lists
    |--------------------------------------------------------------------------
    |
    | Named list IDs for convenient reference throughout your application.
    |
    */

    'lists' => [
        'new_signups' => env('SENDY_NEW_USERS_LIST_ID'),
    ],

];
