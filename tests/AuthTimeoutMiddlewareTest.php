<?php

namespace JulioMotol\AuthTimeout\Tests;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use JulioMotol\AuthTimeout\Events\AuthTimeoutEvent;
use JulioMotol\AuthTimeout\Facades\AuthTimeout;
use JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class AuthTimeoutMiddlewareTest extends TestCase
{
    use InteractsWithTime;

    protected AuthManager $auth;

    protected Dispatcher $event;

    protected SessionManager $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->app['auth'];
        $this->event = $this->app['events'];
        $this->session = $this->app['session'];
    }

    protected function tearDown(): void
    {
        $this->session->flush();

        parent::tearDown();
    }

    /** @test */
    public function should_proceed_when_user_is_guest()
    {
        $this->runMiddleware();
    }

    /** @test */
    public function should_assign_session_for_new_login()
    {
        $this->hasAuth();
        $this->runMiddleware();

        $this->assertNotNull(AuthTimeout::lastActiveAt());
    }

    /** @test */
    public function should_reset_session_when_not_timedout()
    {
        $startTime = Carbon::now();

        $this->hasAuth();
        $this->runMiddleware();

        $this->travel(config('auth-timeout.timeout') - 1)->minutes();

        $this->runMiddleware();

        $this->assertNotEquals($startTime, AuthTimeout::lastActiveAt());
    }

    /** @test */
    public function should_timeout_when_idled()
    {
        $this->expectException(AuthenticationException::class);

        $this->hasAuth();
        $this->runMiddleware();

        $this->travel(config('auth-timeout.timeout') + 1)->minutes();

        $this->runMiddleware();

        $this->expectsEvents(AuthTimeoutEvent::class);
        $this->assertNull($this->auth->user());
        $this->assertNull(AuthTimeout::lastActiveAt());
    }

    /** @test */
    public function can_modify_redirection()
    {
        $this->expectException(AuthenticationException::class);

        $redirectionCallback = function () {
            return 'test';
        };

        AuthTimeoutMiddleware::setRedirectTo($redirectionCallback);

        $this->hasAuth();
        $this->runMiddleware();

        $this->travel(config('auth-timeout.timeout') + 1)->minutes();

        try {
            $this->runMiddleware();
        } catch (AuthenticationException $exception) {
            $this->assertSame($redirectionCallback(), $exception->redirectTo());

            throw $exception;
        }
    }

    /** @test */
    public function is_backwards_compatible_with_v2()
    {
        $this->session->put(config('auth-timeout.session'), time());

        $this->hasAuth();
        $this->runMiddleware();

        $this->travel(config('auth-timeout.timeout') - 1)->minutes();

        $this->runMiddleware();
    }

    private function hasAuth()
    {
        $user = new User(['name' => 'Unit Test User']);

        $this->actingAs($user);
    }

    private function runMiddleware()
    {
        $symfonyRequest = new SymfonyRequest([
            'foo' => 'bar',
            'baz' => '',
        ]);
        $symfonyRequest->server->set('REQUEST_METHOD', 'GET');

        $request = Request::createFromBase($symfonyRequest);

        app(AuthTimeoutMiddleware::class)->handle($request, function (Request $newRequest) use ($request) {
            $this->assertSame($request, $newRequest);
        });
    }
}
