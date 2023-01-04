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
    | Timeout Event
    |--------------------------------------------------------------------------
    |
    | Here you may specify the event that will be dispatched when a user has.
    | timed out.
    |
    */

    'event' => JulioMotol\AuthTimeout\Events\AuthTimedOut::class,

];
