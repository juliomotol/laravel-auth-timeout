<?php

namespace JulioMotol\AuthTimeout\Tests;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use JulioMotol\AuthTimeout\Facades\AuthTimeout;
use JulioMotol\AuthTimeout\Middlewares\CheckAuthTimeout;

use function Pest\Laravel\travel;

afterEach(function () {
    Session::flush();

    CheckAuthTimeout::$redirectToCallback = null;
});

it('should proceed when user is guest', function () {
    expect(AuthTimeout::lastActiveAt())->toBeNull();

    runMiddleware();

    expect(AuthTimeout::lastActiveAt())->toBeNull();
});

it('should timeout when idled', function () {
    login();

    travel(config('auth-timeout.timeout') + 1)->minutes();

    runMiddleware();

    expect(Auth::user())->toBeNull();
    expect(AuthTimeout::lastActiveAt())->toBeNull();
})->throws(AuthenticationException::class);

it('can modify redirection', function () {
    CheckAuthTimeout::setRedirectTo(fn () => 'test');

    login();

    travel(config('auth-timeout.timeout') + 1)->minutes();

    try {
        runMiddleware();
    } catch (AuthenticationException $exception) {
        expect($exception->redirectTo(new Request()))->toEqual('test');

        throw $exception;
    }
})->throws(AuthenticationException::class);

it('should reset session when not timed out', function () {
    login();

    $startTime = AuthTimeout::lastActiveAt();

    travel(config('auth-timeout.timeout') - 1)->minutes();

    runMiddleware();

    expect(AuthTimeout::lastActiveAt())->not()->toEqual($startTime);
});

function login()
{
    Session::start();

    Auth::login(new User());

    expect(AuthTimeout::lastActiveAt())->toBeInstanceOf(Carbon::class);
}

function runMiddleware()
{
    app(CheckAuthTimeout::class)->handle(new Request(), function (Request $request) {
        expect(true)->toBeTrue();
    });
}
