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
    /**
     * The Authentication Manager.
     *
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;

    /**
     * The Event Dispatcher.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $event;

    /**
     * The Session Manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * The session key.
     *
     * @var string
     */
    protected $session_name;

    /**
     * Create an AuthTimeoutMiddleware.
     *
     * @param  \Illuminate\Auth\AuthManager  $auth
     * @param  \Illuminate\Events\Dispatcher  $event
     * @param  \Illuminate\Session\SessionManager  $session
     */
    public function __construct(AuthManager $auth, Dispatcher $event, SessionManager $session)
    {
        $this->auth = $auth;
        $this->event = $event;
        $this->session = $session;

        $this->session_name = config('auth-timeout.session');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // When no session had been set yet, we'll set one now.
        if (! $this->session->get($this->session_name)) {
            $this->reset();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function check($guard = null)
    {
        // When there are no user's logged in, we'll return false.
        if ($this->auth->guard($guard)->guest()) {
            return false;
        }

        $lastActiveAt = Carbon::parse($this->session->get($this->session_name));
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
        $this->session->forget($this->session_name);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->session->put($this->session_name, (string)Carbon::now());
    }
}
