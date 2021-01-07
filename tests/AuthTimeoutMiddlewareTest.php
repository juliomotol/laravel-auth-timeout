<?php

namespace JulioMotol\AuthTimeout\Tests;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Http\Request;
use JulioMotol\AuthTimeout\Events\AuthTimeoutEvent;
use JulioMotol\AuthTimeout\Middleware\AuthTimeoutMiddleware;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class AuthTimeoutMiddlewareTest extends TestCase
{
    use InteractsWithTime;

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

        $this->assertEquals((string)Carbon::now(), $this->session->get(config('auth-timeout.session')));
    }

    /** @test */
    public function should_reset_session_when_not_timedout()
    {
        $init_time = time();

        $this->hasAuth();
        $this->runMiddleware();

        $this->travel(config('auth-timeout.timeout') - 1)->minutes();

        $this->runMiddleware();

        $this->assertNotEquals($init_time, $this->session->get(config('auth-timeout.session')));
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
        $this->assertNull($this->session->get(config('auth-timeout.session')));
    }

    private function hasAuth()
    {
        $user = new User(['name' => 'Unit Test User']);

        $this->actingAs($user);
    }

    private function runMiddleware()
    {
        $middleware = app(AuthTimeoutMiddleware::class);
        $symfonyRequest = new SymfonyRequest([
            'foo' => 'bar',
            'baz' => '',
        ]);
        $symfonyRequest->server->set('REQUEST_METHOD', 'GET');
        $request = Request::createFromBase($symfonyRequest);

        $middleware->handle($request, function (Request $newRequest) use ($request) {
            $this->assertSame($request, $newRequest);
        });
    }
}
