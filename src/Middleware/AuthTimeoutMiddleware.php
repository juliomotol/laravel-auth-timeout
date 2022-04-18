<?php

namespace JulioMotol\AuthTimeout\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use JulioMotol\AuthTimeout\Contracts\AuthTimeout;
use Symfony\Component\HttpFoundation\Response;

class AuthTimeoutMiddleware
{
    public static Closure $redirectToCallback;

    /**
     * Create an AuthTimeoutMiddleware.
     */
    public function __construct(
        public AuthManager $auth,
        public AuthTimeout $authTimeout
    ) {
    }

    public function handle(Request $request, Closure $next, string $guard = null): ?Response
    {
        // When there are no user's logged in, just let them pass through
        if ($this->auth->guard($guard)->guest()) {
            return $next($request);
        }

        // First we'll initialize a session when none has been set yet.
        $this->authTimeout->init();

        // Then we'll check if the user have timed out.
        if (! $this->authTimeout->check($guard)) {
            throw new AuthenticationException('Timed out.', [$guard], $this->redirectTo($request, $guard));
        }

        // If the user is not yet timed out, we'll reset the timeout session
        // and proceed with the pipeline.
        $this->authTimeout->reset();

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they timed out.
     */
    protected function redirectTo(Request $request, ?string $guard): ?string
    {
        if (! isset(self::$redirectToCallback)) {
            return null;
        }

        return (self::$redirectToCallback)($request, $guard);
    }

    /**
     * Set the redirection callback.
     */
    public static function setRedirectTo(Closure $callback): void
    {
        self::$redirectToCallback = $callback;
    }
}
