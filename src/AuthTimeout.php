<?php

namespace JulioMotol\AuthTimeout;

use Carbon\Carbon;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout as AuthTimeoutContract;
use JulioMotol\AuthTimeout\Events\AuthTimeoutEvent;

class AuthTimeout implements AuthTimeoutContract
{
    protected string $session_key;

    public function __construct(
        protected AuthManager $auth,
        protected Dispatcher $event,
        protected SessionManager $session
    ) {
        $this->session_key = config('auth-timeout.session');
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (! $this->session->get($this->session_key)) {
            $this->reset();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function check(string $guard = null): bool
    {
        // When there are no user's logged in, we'll return false.
        if ($this->auth->guard($guard)->guest()) {
            return false;
        }

        $lastActiveAt = Carbon::parse($this->session->get($this->session_key));
        $timeoutAt = $lastActiveAt->addMinutes(config('auth-timeout.timeout'));

        // Now lets check if they are still within the timeout threshold.
        if ($timeoutAt->greaterThan(Carbon::now())) {
            return true;
        }

        // At this point we know that they have timed out so we'll log them
        // out, dispatch an event, invalidate the session we've set, and return
        // false.
        $user = $this->auth->guard($guard)->user();

        $this->auth->guard($guard)->logout();
        $this->event->dispatch(new AuthTimeoutEvent($user, $guard));
        $this->session->forget($this->session_key);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->session->put($this->session_key, (string)Carbon::now());
    }
}
