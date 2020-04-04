<?php

namespace JulioMotol\AuthTimeout\Events;

use Illuminate\Queue\SerializesModels;

class AuthTimeoutEvent
{
    use SerializesModels;

    /**
     * The user that timed out.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
