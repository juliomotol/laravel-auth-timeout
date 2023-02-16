<?php

namespace JulioMotol\AuthTimeout\Middlewares;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JulioMotol\AuthTimeout\Facades\AuthTimeout;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthTimeout
{
    public static ?Closure $redirectToCallback = null;

    public function handle(Request $request, Closure $next, string $guard = null): ?Response
    {
        if (Auth::guard($guard)->guest()) {
            return $next($request);
        }

        if (! AuthTimeout::check($guard)) {
            throw new AuthenticationException('Timed out.', [$guard], $this->redirectTo($request, $guard));
        }

        AuthTimeout::hit();

        return $next($request);
    }

    protected function redirectTo(Request $request, ?string $guard): ?string
    {
        if (! self::$redirectToCallback) {
            return null;
        }

        return (self::$redirectToCallback)($request, $guard);
    }

    public static function setRedirectTo(Closure $callback): void
    {
        self::$redirectToCallback = $callback;
    }
}
