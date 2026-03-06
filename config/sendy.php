<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Sendy
    |--------------------------------------------------------------------------
    |
    | When set to false, all API methods will short-circuit and return null
    | (or an empty array for getLists) without making any HTTP requests.
    | Set to false in local/staging environments to avoid touching real lists.
    |
    */

    'enabled' => env('SENDY_ENABLED', true),

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
