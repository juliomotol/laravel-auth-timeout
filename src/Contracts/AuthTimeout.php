<?php

namespace JulioMotol\AuthTimeout\Contracts;

use Carbon\Carbon;

interface AuthTimeout
{
    public function init(): void;

    public function check(?string $guard = null): bool;

    /** @deprecated Use `hit()` instead. */
    public function reset(): void;

    public function hit(): void;

    public function lastActiveAt(): ?Carbon;
}
