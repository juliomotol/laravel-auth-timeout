<?php

namespace JulioMotol\AuthTimeout\Tests;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use JulioMotol\AuthTimeout\Events\AuthTimeoutEvent;
use JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware;
use JulioMotol\AuthTimeout\Tests\User;
use Mockery;

class AuthTimeoutMiddlewareTest extends TestCase
{
    /**
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $event;

    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

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

        $this->assertEquals(time(), $this->session->get(config('auth-timeout.session')));
    }

    /** @test */
    public function should_reset_session_when_not_timedout()
    {
        $init_time = time();

        $this->hasAuth();
        $this->runMiddleware();

        sleep(2);

        $this->runMiddleware();

        $this->assertNotEquals($init_time, $this->session->get(config('auth-timeout.session')));
    }

    /** @test */
    public function should_timeout_when_idled()
    {
        config(['auth-timeout.timeout' => 0.05]);

        $this->expectException(AuthenticationException::class);

        $this->hasAuth();
        $this->runMiddleware();

        sleep(5);

        $this->runMiddleware();

        $this->assertNull($this->auth->user());
        $this->expectsEvents(AuthTimeoutEvent::class);
        $this->assertNull($this->session->get(config('auth-timeout.session')));
    }

    private function hasAuth()
    {
        $user = new User(['name' => 'Unit Test User']);

        $this->actingAs($user);
    }

    private function runMiddleware()
    {
        $request = Mockery::mock(Request::class);

        $nextParam = null;

        $next = function ($param) use (&$nextParam) {
            $nextParam = $param;
        };

        (new AuthTimeoutMiddleware($this->auth, $this->event, $this->session))->handle($request, $next);

        $this->assertSame($request, $nextParam);
    }
}
