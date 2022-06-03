<?php

namespace JulioMotol\AuthTimeout\Contracts;

use Carbon\Carbon;

interface AuthTimeout
{
    /**
     * Initialize the timeout session when no has been set yet.
     */
    public function init(): void;

    /**
     * Check if a user has timed out. Returns `true` if not yet timed out and `false` otherwise.
     */
    public function check(string $guard = null): bool;

    /**
     * Reset the user's timeout session.
     */
    public function reset(): void;

    /**
     * Get the last active session time.
     */
    public function lastActiveAt(): ?Carbon;
}
