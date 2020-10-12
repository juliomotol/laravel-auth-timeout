<?php

namespace JulioMotol\AuthTimeout\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthManager;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout;

class AuthTimeoutMiddleware
{
    /**
     * The Authentication Manager.
     *
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;

    /**
     * The AuthTimeout instance.
     *
     * @var \JulioMotol\AuthTimeout\Contracts\AuthTimeout
     */
    protected $authTimeout;

    /**
     * Create an AuthTimeoutMiddleware.
     *
     * @param  \Illuminate\Auth\AuthManager  $auth
     * @param  \JulioMotol\AuthTimeout\Contracts\AuthTimeout $authTimeout
     */
    public function __construct(AuthManager $auth, AuthTimeout $authTimeout)
    {
        $this->auth = $auth;
        $this->authTimeout = $authTimeout;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $guard
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // When there are no user's logged in, just let them pass through
        if ($this->auth->guard($guard)->guest()) {
            return $next($request);
        }

        // First we'll initialize a session when none has been set yet.
        $this->authTimeout->init();

        // Then we'll check if the user have timed out. If so, we'll throw an
        // AuthenticationException.
        if (!$this->authTimeout->check($guard)) {
            throw new AuthenticationException('Timed out.', [$guard], $this->redirectTo($request, $guard));
        }

        // If the user is not yet timed out, we'll reset the timeout session
        // and proceed with the pipeline.
        $this->authTimeout->reset();

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they timed out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed    $guard
     *
     * @return string|null
     */
    protected function redirectTo($request, $guard)
    {
        return config('auth-timeout.redirect');
    }
}
