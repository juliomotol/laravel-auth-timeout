<?php

namespace JulioMotol\AuthTimeout\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use JulioMotol\AuthTimeout\Events\AuthTimeoutEvent;

class AuthTimeoutMiddleWare
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
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $guard
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $session_name = config('auth-timeout.session');

        // When there are no user's logged in, just let them pass through
        if ($this->auth->guard($guard)->guest()) {
            return $next($request);
        }

        // At this point we know that every user that reaches here is
        // authenticated. If they are newly logged in, and no session had been
        // set yet, lets set that here for now.
        if (! $this->session->get($session_name)) {
            $this->session->put($session_name, time());
        }

        // Now lets check if they have been idle for the timeout duration. If 
        // so we'll log them out, dispatch an event, invalidate the session
        // we've set, and throw and AuthenticationException.
        if ((time() - (int)$this->session->get($session_name)) > (config('auth-timeout.timeout') * 60)) {
            $user = $this->auth->guard($guard)->user();

            $this->auth->guard($guard)->logout();

            $this->event->dispatch(new AuthTimeoutEvent($user));

            $this->session->forget($session_name);

            throw new AuthenticationException('Timed out.', [$guard], $this->redirectTo($request));
        }

        // Refresh our session with the current time.
        $this->session->put($session_name, time());

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they timed out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return config('auth-timeout.redirect');
    }
}
