<?php

namespace JulioMotol\AuthTimeout;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\Store;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout as AuthTimeoutContract;

class AuthTimeout implements AuthTimeoutContract
{
    public function __construct(
        protected Factory $auth,
        protected Dispatcher $event,
        protected Store $session
    ) {
    }

    public function init(): void
    {
        if ($this->lastActiveAt()) {
            return;
        }

        $this->hit();
    }

    public function check(?string $guard = null): bool
    {
        $user = $this->auth->guard($guard)->user();

        if (! $user) {
            return false;
        }

        if ($this->lastActiveAt()?->addMinutes(config('auth-timeout.timeout'))->isFuture()) {
            return true;
        }

        $this->auth->guard($guard)->logout();
        $this->event->dispatch(new (config('auth-timeout.event'))($user, $guard));
        $this->session->forget($this->getSessionKey());

        return false;
    }

    public function reset(): void
    {
        $this->hit();
    }

    public function hit(): void
    {
        $this->session->put($this->getSessionKey(), (string) Carbon::now());
    }

    public function lastActiveAt(): ?Carbon
    {
        if ($lastActivity = $this->session->get($this->getSessionKey())) {
            // In v2, `$lastActivity` was stored as `int` using `time`. To preseve compatibility
            // with v3, lets first check if it is numeric then parse it back to `int` just in case
            // Laravel's session store messes with its type.
            return Carbon::parse(is_numeric($lastActivity) ? (int) $lastActivity : $lastActivity);
        }

        return null;
    }

    protected function getSessionKey(): string
    {
        return config('auth-timeout.session');
    }
}
