<?php

namespace JulioMotol\AuthTimeout\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class AuthTimedOut
{
    use SerializesModels;

    public function __construct(
        public Authenticatable $user,
        public ?string $guard
    ) {}
}
