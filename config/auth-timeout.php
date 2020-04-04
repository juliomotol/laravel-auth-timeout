<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session Name
    |--------------------------------------------------------------------------
    |
    | Here you may change the session name used to identify if the user has
    | has reached the timeout time.
    |
    */

    'session' => 'last_activity_time',

    /*
    |--------------------------------------------------------------------------
    | Session Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the auth session
    | to be allowed to remain idle before the user is logged out.
    |
    */

    'timeout' => 15,

    /*
    |--------------------------------------------------------------------------
    | Timeout Redirection
    |--------------------------------------------------------------------------
    |
    | Here you may specify the path to where the user will be redirected when
    | they have timed out.
    |
    */

    'redirect' => null,

];
