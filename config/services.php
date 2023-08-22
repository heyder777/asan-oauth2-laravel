<?php

return [

    /**
     Add new config asan 
     */

    'asan' => [
        'base_uri' => env('ASAN_LOGIN_URL'),
        'client_id' => env('ASAN_CLIENT'),
        'client_secret' => env('ASAN_CLIENT_SECRET'),
        'redirect' => env('ASAN_REDIRECT_URL'),
    ],

];
