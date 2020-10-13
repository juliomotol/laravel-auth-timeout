<?php

namespace JulioMotol\AuthTimeout\Contracts;

interface AuthTimeout
{
    /**
     * Initialize the timeout session when no has been set yet.
     *
     * @return void;
     */
    public function init();

    /**
     * Check if a user has timed out. Returns `true` if not yet timed out and `false` otherwise.
     *
     * @param mixed $guard
     *
     * @return bool
     */
    public function check($guard = null);

    /**
     * Reset the user's timeout session.
     *
     * @return void
     */
    public function reset();
}
