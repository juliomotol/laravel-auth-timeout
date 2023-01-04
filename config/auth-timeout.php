<?php

return [

    /**
     * The session name used to identify if the user has reached the timeout time.
     */
    'session' => 'last_activity_time',

    /**
     * The minutes of idle time before the user is logged out.
     */
    'timeout' => 15,

    /**
     * The event that will be dispatched when a user has timed out.
     */
    'event' => JulioMotol\AuthTimeout\Events\AuthTimedOut::class,

];
